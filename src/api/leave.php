<?php

use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/OneSignalHelper.php';
require_once __DIR__ . '/../helpers/EmployeeHelper.php';
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    // CREATE LEAVE REQUEST
    $app->post('/leave/request', function ($request, $response, $args) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        try {
            // 🔹 Validasi field wajib
            if (!isset($data['start_date'], $data['end_date'], $data['reason'], $data['employee_id'])) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Field start_date, end_date, reason, dan employee_id harus diisi!"
                ], 400);
            }

            // 🔹 Ambil nama lengkap pengaju dari tabel employee
            $stmtEmp = $db->prepare("SELECT full_name FROM ar_employee WHERE id = :id LIMIT 1");
            $stmtEmp->execute(['id' => $data['employee_id']]);
            $employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);
            $employeeName = $employee['full_name'] ?? $data['employee_id']; // fallback ke ID jika nama null

            // 🔹 Generate Custom ID untuk leave request
            $leaveId = generateCustomId();

            // 🔹 Insert ke tabel ar_leave_request
            $sql = "INSERT INTO ar_leave_request 
                    (id, employee_id, start_date, end_date, reason, status, created_by, created_at, updated_at)
                    VALUES (:id, :employee_id, :start_date, :end_date, :reason, 'pending', :created_by, NOW(), NOW())";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                "id"          => $leaveId,
                "employee_id" => $data['employee_id'],
                "start_date"  => $data['start_date'],
                "end_date"    => $data['end_date'],
                "reason"      => $data['reason'],
                "created_by"  => $data['employee_id'],
            ]);

            // 🔹 Target posisi yang akan menerima notifikasi
            $targetPositions = [
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
            ];

            // 🔹 Ambil player IDs berdasarkan posisi target
            $playerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

            // 🔹 Hapus player_id milik pembuat cuti dari daftar target
            $creatorPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data['employee_id']);
            $playerIds = array_diff($playerIds, $creatorPlayerIds);

            // 🔹 Kirim notifikasi dan simpan di ar_user_notifications
            if (!empty($playerIds)) {
                OneSignalHelper::sendNotification(
                    $playerIds,
                    "Pengajuan cuti baru dari {$employeeName}",
                    "Pengajuan Cuti",
                    [
                        "type" => "leaveAdd",
                        "leave_id" => $leaveId
                    ]
                );

                foreach ($playerIds as $playerId) {
                    $userId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                    if ($userId) {
                        $notifId = generateCustomId();
                        $insertNotif = $db->prepare("
                            INSERT INTO ar_user_notifications (id, user_id, type, read_status, created_at)
                            VALUES (:id, :user_id, :type, 0, NOW())
                        ");
                        $insertNotif->execute([
                            'id'      => $notifId,
                            'user_id' => $userId,
                            'type'    => 'leaveAdd'
                        ]);
                    }
                }
            }

            // 🔹 Response sukses
            return $response->withJson([
                "status" => true,
                "message" => "Pengajuan cuti berhasil dikirim!",
                "data" => [
                    "leave_id"      => $leaveId,
                    "employee_id"   => $data['employee_id'],
                    "employee_name" => $employeeName,
                    "start_date"    => $data['start_date'],
                    "end_date"      => $data['end_date'],
                    "reason"        => $data['reason'],
                    "status"        => "pending",
                    "created_by"    => $data['employee_id'],
                    "created_at"    => date("Y-m-d H:i:s"),
                    "updated_at"    => date("Y-m-d H:i:s"),
                ]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });





    // HISTORY LEAVE REQUEST
    $app->get('/leave/history', function ($request, $response, $args) {
        $params = $request->getQueryParams();
        $employee_id = $params['employee_id'] ?? null;

        if (!$employee_id) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'employee_id' is required"
            ], 400);
        }

        $db = $this->get('db_default');

        try {
            // 🔹 Ambil posisi karyawan (berdasarkan ID)
            $stmt = $db->prepare("
                SELECT e.position_id, p.position_name
                FROM ar_employee e
                JOIN ar_position p ON e.position_id = p.id
                WHERE e.id = :employee_id
                LIMIT 1
            ");
            $stmt->execute(["employee_id" => $employee_id]);
            $position = $stmt->fetch(PDO::FETCH_ASSOC);
            $positionId = strtoupper(trim($position['position_id'] ?? ''));

            // 🔹 Daftar posisi dengan hak akses penuh (lihat semua cuti)
            $superPrivilegedPositions = [
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
            ];

            // 🔹 Daftar posisi yang bisa melihat approved + milik sendiri
            $approvedViewPositions = [
                'PO00005', // Head Sales Dept
                'PO00003', // Advisory Product Dev.
                'PO00020', // Head Administration & Support Dept
                'PO00006', // Head Project and Customer Service Dept
                'PO00004', // Head Finance & Accounting Dept
                'PO00014', // Operational Manager
            ];

            $isSuperPrivileged = in_array($positionId, $superPrivilegedPositions);
            $isApprovedViewer  = in_array($positionId, $approvedViewPositions);

            // 🔹 Ambil data cuti berdasarkan level akses
            if ($isSuperPrivileged) {
                // Bisa lihat semua cuti (semua status)
                $stmt = $db->query("
                    SELECT l.*, e.full_name, e.position_id, p.position_name
                    FROM ar_leave_request l
                    JOIN ar_employee e ON e.id = l.employee_id
                    JOIN ar_position p ON e.position_id = p.id
                    ORDER BY l.created_at DESC
                ");
            } elseif ($isApprovedViewer) {
                // Bisa lihat semua cuti yang approved + cuti milik sendiri
                $stmt = $db->prepare("
                    SELECT l.*, e.full_name, e.position_id, p.position_name
                    FROM ar_leave_request l
                    JOIN ar_employee e ON e.id = l.employee_id
                    JOIN ar_position p ON e.position_id = p.id
                    WHERE l.status = 'approved' OR l.employee_id = :employee_id
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute(['employee_id' => $employee_id]);
            } else {
                // Hanya bisa lihat cuti miliknya sendiri
                $stmt = $db->prepare("
                    SELECT l.*, e.full_name, e.position_id, p.position_name
                    FROM ar_leave_request l
                    JOIN ar_employee e ON e.id = l.employee_id
                    JOIN ar_position p ON e.position_id = p.id
                    WHERE l.employee_id = :employee_id
                    ORDER BY l.created_at DESC
                ");
                $stmt->execute(['employee_id' => $employee_id]);
            }

            $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "position_id" => $positionId,
                "position_name" => $position['position_name'] ?? null,
                "is_super_privileged" => $isSuperPrivileged,
                "is_approved_viewer" => $isApprovedViewer,
                "data" => $leaves
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });

    // UPDATE STATUS LEAVE (approve/reject)
    $app->post('/leave/{id:[A-Za-z0-9\-]+}/status', function ($request, $response, $args) {
        $db = $this->get('db_default');
        $leave_id = $args['id'];
        $data = $request->getParsedBody();

        if (!isset($data['status'], $data['approved_by'])) {
            return $response->withJson([
                "status" => false,
                "message" => "Field status dan approved_by harus diisi!"
            ], 400);
        }

        $status = strtolower($data['status']);
        if (!in_array($status, ['approved', 'rejected'])) {
            return $response->withJson([
                "status" => false,
                "message" => "Status harus 'approved' atau 'rejected'"
            ], 400);
        }

        $rejectNote = $data['reject_note'] ?? null;

        try {
            // 🔹 Update status leave
            if ($status === 'approved') {
                $stmt = $db->prepare("
                    UPDATE ar_leave_request 
                    SET 
                        status = 'approved',
                        approved_by = :approved_by,
                        approved_at = NOW(),
                        rejected_by = NULL,
                        rejected_at = NULL,
                        reject_note = NULL,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    'approved_by' => $data['approved_by'],
                    'id' => $leave_id
                ]);
            } else {
                $stmt = $db->prepare("
                    UPDATE ar_leave_request 
                    SET 
                        status = 'rejected',
                        rejected_by = :rejected_by,
                        rejected_at = NOW(),
                        reject_note = :reject_note,
                        approved_by = NULL,
                        approved_at = NULL,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $stmt->execute([
                    'rejected_by' => $data['approved_by'],
                    'reject_note' => $rejectNote,
                    'id' => $leave_id
                ]);
            }

            // 🔹 Ambil info pengaju (id dan nama)
            $stmt = $db->prepare("
                SELECT l.employee_id, e.full_name AS employee_name
                FROM ar_leave_request l
                JOIN ar_employee e ON e.id = l.employee_id
                WHERE l.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $leave_id]);
            $leave = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($leave && isset($leave['employee_id'])) {
                $targetPositions = [
                    "PO00001", // President Director
                    "PO00013", // Corporate Secretary
                    "PO00005", // Head Sales Dept
                    "PO00003", // Advisory Product Dev.
                    "PO00020", // Head Administration & Support Dept
                    "PO00006", // Head Project and Customer Service Dept
                    "PO00004", // Head Finance & Accounting Dept
                    "PO00014", // Operational Manager
                ];

                $targetPlayerIds   = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);
                $employeePlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $leave['employee_id']);

                // 🚫 jangan kirim ke approver
                $approverPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data['approved_by']);

                // 💬 Pesan dasar
                $statusMsg = $status === 'approved' ? 'disetujui ✅' : 'ditolak ❌';
                $notifMsgForEmployee = "Pengajuan cuti kamu telah {$statusMsg}";
                $notifMsgForOthers   = "Permohonan cuti dari {$leave['employee_name']} telah {$statusMsg}";

                // 🔹 Kirim ke pengaju (tanpa nama)
                $employeeRecipients = array_diff($employeePlayerIds, $approverPlayerIds);
                if (!empty($employeeRecipients)) {
                    OneSignalHelper::sendNotification(
                        $employeeRecipients,
                        $notifMsgForEmployee,
                        "Update Pengajuan Cuti",
                        [
                            "type" => "leaveUpdate",
                            "leave_id" => $leave_id,
                            "status" => $status,
                            "reject_note" => $rejectNote
                        ]
                    );
                }

                // 🔹 Kirim ke target posisi (pakai nama)
                $targetRecipients = array_diff($targetPlayerIds, $approverPlayerIds);
                if (!empty($targetRecipients)) {
                    OneSignalHelper::sendNotification(
                        $targetRecipients,
                        $notifMsgForOthers,
                        "Update Pengajuan Cuti",
                        [
                            "type" => "leaveUpdate",
                            "leave_id" => $leave_id,
                            "status" => $status,
                            "reject_note" => $rejectNote
                        ]
                    );
                }

                // 🔹 Simpan notifikasi ke database
                $allRecipients = array_merge($employeeRecipients, $targetRecipients);
                foreach ($allRecipients as $playerId) {
                    $userId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                    if ($userId) {
                        $notifId = generateCustomId();
                        $insertNotif = $db->prepare("
                            INSERT INTO ar_user_notifications (id, user_id, type, read_status, created_at)
                            VALUES (:id, :user_id, :type, 0, NOW())
                        ");
                        $insertNotif->execute([
                            'id'      => $notifId,
                            'user_id' => $userId,
                            'type'    => 'leaveUpdate'
                        ]);
                    }
                }
            }

            return $response->withJson([
                "status" => true,
                "message" => "Leave request berhasil diupdate ke status {$status}"
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        } catch (Throwable $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Server error: " . $e->getMessage()
            ], 500);
        }
    });





    // DETAIL LEAVE REQUEST
    $app->get('/leave/{id:[A-Za-z0-9\-]+}', function ($request, $response, $args) {
        $db = $this->get('db_default');
        $leave_id = $args['id'];

        try {
            $stmt = $db->prepare("
                SELECT 
                    l.*, 
                    e.full_name, 
                    e.position_id,
                    ea.full_name AS approved_by_name,
                    er.full_name AS rejected_by_name
                FROM ar_leave_request l
                JOIN ar_employee e ON e.id = l.employee_id
                LEFT JOIN ar_employee ea ON ea.id = l.approved_by
                LEFT JOIN ar_employee er ON er.id = l.rejected_by
                WHERE l.id = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $leave_id]);
            $leave = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$leave) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Leave request tidak ditemukan"
                ], 404);
            }

            // Ubah approved_by & rejected_by jadi nama lengkap
            $leave['approved_by'] = $leave['approved_by_name'] ?? null;
            $leave['rejected_by'] = $leave['rejected_by_name'] ?? null;

            // Hapus field sementara dari SELECT
            unset($leave['approved_by_name'], $leave['rejected_by_name']);

            return $response->withJson([
                "status" => true,
                "data" => $leave
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    // UPDATE / EDIT LEAVE REQUEST
    $app->post('/leave/{id:[A-Za-z0-9\-]+}/edit', function ($request, $response, $args) {
        $db = $this->get('db_default');
        $leave_id = $args['id'];
        $data = $request->getParsedBody();

        // ✅ Validasi input
        if (!isset($data['start_date'], $data['end_date'], $data['reason'], $data['employee_id'])) {
            return $response->withJson([
                "status" => false,
                "message" => "Field start_date, end_date, reason, dan employee_id harus diisi!"
            ], 400);
        }

        try {
            // ✅ Pastikan leave ada
            $stmtCheck = $db->prepare("SELECT * FROM ar_leave_request WHERE id = :id LIMIT 1");
            $stmtCheck->execute(['id' => $leave_id]);
            $leave = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$leave) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Leave request tidak ditemukan"
                ], 404);
            }

            // ✅ Ubah status jadi 'pending' kalau sebelumnya 'rejected'
            $newStatus = strtolower($leave['status']) === 'rejected' ? 'pending' : $leave['status'];

            // ✅ Update data leave
            $stmt = $db->prepare("
                UPDATE ar_leave_request
                SET start_date = :start_date,
                    end_date = :end_date,
                    reason = :reason,
                    employee_id = :employee_id,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                "start_date"  => $data['start_date'],
                "end_date"    => $data['end_date'],
                "reason"      => $data['reason'],
                "employee_id" => $data['employee_id'],
                "status"      => $newStatus,
                "id"          => $leave_id
            ]);

            // ✅ Ambil nama lengkap pengaju
            $stmtEmp = $db->prepare("SELECT full_name FROM ar_employee WHERE id = :id LIMIT 1");
            $stmtEmp->execute(['id' => $data['employee_id']]);
            $employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);
            $employeeName = $employee['full_name'] ?? $data['employee_id'];

            // ✅ Tentukan target posisi notifikasi
            $targetPositions = [
                "PO00001", // President Director
                "PO00013", // Corporate Secretary
            ];

            // ✅ Ambil player IDs berdasarkan posisi target
            $playerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

            // 🚫 Keluarkan player_id milik pengaju dari daftar target
            $creatorPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data['employee_id']);
            $playerIds = array_diff($playerIds, $creatorPlayerIds);

            // ✅ Kirim notifikasi via OneSignal
            if (!empty($playerIds)) {
                $title = "Leave Request Updated";
                $msg   = "{$employeeName} telah memperbarui permohonan cuti.";

                OneSignalHelper::sendNotification(
                    $playerIds,
                    $msg,
                    $title,
                    [
                        "type"      => "leaveUpdate",
                        "leave_id"  => $leave_id
                    ]
                );

                // ✅ Simpan notifikasi ke database
                foreach ($playerIds as $playerId) {
                    $userId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                    if ($userId) {
                        $notifId = generateCustomId();
                        $insertNotif = $db->prepare("
                            INSERT INTO ar_user_notifications (id, user_id, type, read_status, created_at)
                            VALUES (:id, :user_id, :type, 0, NOW())
                        ");
                        $insertNotif->execute([
                            'id'      => $notifId,
                            'user_id' => $userId,
                            'type'    => 'leaveUpdate'
                        ]);
                    }
                }
            }

            // ✅ Response sukses
            return $response->withJson([
                "status" => true,
                "message" => "Leave request berhasil diperbarui dan dikirim ulang",
                "data" => [
                    "leave_id"     => $leave_id,
                    "employee_id"  => $data['employee_id'],
                    "employee_name"=> $employeeName,
                    "start_date"   => $data['start_date'],
                    "end_date"     => $data['end_date'],
                    "reason"       => $data['reason'],
                    "status"       => $newStatus,
                    "updated_at"   => date("Y-m-d H:i:s"),
                ]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


};
