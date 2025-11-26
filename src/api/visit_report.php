<?php
require_once __DIR__ . '/../helpers/OneSignalHelper.php';
require_once __DIR__ . '/../helpers/EmployeeHelper.php';
require_once __DIR__ . '/../helpers/IdHelper.php';
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    // ==========================
    // CREATE VISIT
    // ==========================
    $app->post('/ar_visit', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        try {
            $db->beginTransaction();
            $visitId = generateCustomId();

            // 🔹 Insert ke ar_visit
            $sql = "INSERT INTO ar_visit (id, client_name, subject, created_by, created_at, updated_at) 
                    VALUES (:id, :client_name, :subject, :created_by, NOW(), NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                "id"          => $visitId,
                "client_name" => $data["client_name"],
                "subject"     => $data["subject"],
                "created_by"  => $data["created_by"],
            ]);

            // 🔹 Insert ke ar_visit_log
            $logId = generateCustomId();
            $sqlLog = "INSERT INTO ar_visit_log 
                        (id, visit_id, visit_date, note, pic, next_pic, status, created_by, created_at, updated_at) 
                        VALUES (:id, :visit_id, :visit_date, :note, :pic, :next_pic, :status, :created_by, NOW(), NOW())";
            $stmtLog = $db->prepare($sqlLog);
            $stmtLog->execute([
                "id"         => $logId,
                "visit_id"   => $visitId,
                "visit_date" => $data["visit_date"] ?? date('Y-m-d'),
                "note"       => $data["note"] ?? null,
                "pic"        => $data["created_by"],
                "next_pic"   => $data["next_pic"] ?? null,
                "status"     => $data["status"] ?? "planned",
                "created_by" => $data["created_by"],
            ]);

            $db->commit();

            // 🔔 Notifikasi Friendly
            $title = "Visit Baru";
            $visitDateText = isset($data["visit_date"]) ? date("d M Y", strtotime($data["visit_date"])) : "hari ini";
            $noteText = isset($data["note"]) ? " Catatan: {$data['note']}" : "";
            $msg = "Hai! Visit baru untuk client '{$data['client_name']}' telah dibuat pada $visitDateText.$noteText";

            // 🔹 Target Positions
            $targetPositions = [
                'PO00005', // Head Sales Dept
                'PO00019', // Sales Representative
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            // 🔹 Ambil player IDs posisi tertentu via helper
            $positionPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);


            // 🔹 Hapus player_id pembuat visit dari posisi target saja
            $creatorPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data["created_by"]);
            $positionPlayerIds = array_diff($positionPlayerIds, $creatorPlayerIds);

            // 🔹 Ambil player ID next_pic jika ada
            $nextPicPlayerIds = [];
            if (!empty($data["next_pic"])) {
                $nextPicPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data["next_pic"]);
            }

            // 🔹 Gabungkan posisi target + next_pic, hindari duplikasi
            $allPlayerIds = array_unique(array_merge($positionPlayerIds, $nextPicPlayerIds));

            // 🔹 Kirim notifikasi jika ada player ID tersisa
            if (!empty($allPlayerIds)) {
                OneSignalHelper::sendNotification(
                    $allPlayerIds,
                    $msg,
                    $title,
                    [
                        "type" => "visitAdd",
                        "visit_id" => $visitId
                    ]
                );

                // 🔹 Simpan notifikasi ke database (ar_user_notifications)
                foreach ($allPlayerIds as $playerId) {
                    $userId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                    if ($userId) {
                        $notifId = generateCustomId();
                        $insertNotif = $db->prepare("
                            INSERT INTO ar_user_notifications (id, user_id, type, read_status, created_at)
                            VALUES (:id, :user_id, :type, 0, NOW())
                        ");
                        $insertNotif->execute([
                            'id'       => $notifId,
                            'user_id'  => $userId,
                            'type'     => 'visitAdd'
                        ]);
                    }
                }
            }

            return $response->withJson([
                "status"   => true,
                "message"  => "Visit created successfully",
                "data"     => [
                    "visit_id" => $visitId,
                    "log_id"   => $logId
                ]
            ], 201);

        } catch (PDOException $e) {
            $db->rollBack();
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });




    // ==========================
    // LIST VISITS
    // ==========================
    $app->get('/ar_visit/list', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $params = $request->getQueryParams();
        $employeeId = $params['employee_id'] ?? null;

        if (!$employeeId) {
            return $response->withJson([
                "status" => false,
                "message" => "employee_id required"
            ], 400);
        }

        try {
            // cek posisi employee berdasarkan ID
            $stmt = $db->prepare("
                SELECT p.id
                FROM ar_employee e
                JOIN ar_position p ON e.position_id = p.id
                WHERE e.id = :employee_id
                LIMIT 1
            ");
            $stmt->execute(["employee_id" => $employeeId]);
            $position = $stmt->fetch(PDO::FETCH_ASSOC);

            // daftar ID posisi yang diizinkan / privileged
            $privilegedPositions = [
                'PO00005', // Head Sales Dept
                'PO00019', // Sales Representative
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            // cek apakah employee termasuk privileged
            $isPrivileged = isset($position['id']) && in_array($position['id'], $privilegedPositions);


            if ($isPrivileged) {
                // Semua visit untuk privileged
                $sqlVisit = "SELECT DISTINCT v.*, e.full_name AS created_by_name
                            FROM ar_visit v
                            LEFT JOIN ar_employee e ON v.created_by = e.id
                            ORDER BY v.created_at DESC";
                $stmtVisit = $db->query($sqlVisit);
                $visits = $stmtVisit->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Hanya visit terkait employee ini
                $sqlVisit = "SELECT DISTINCT v.*, e.full_name AS created_by_name
                            FROM ar_visit v
                            LEFT JOIN ar_employee e ON v.created_by = e.id
                            LEFT JOIN ar_visit_log l ON v.id = l.visit_id
                            WHERE v.created_by = :employee_id 
                            OR l.pic = :employee_id 
                            OR l.next_pic = :employee_id
                            ORDER BY v.created_at DESC";
                $stmtVisit = $db->prepare($sqlVisit);
                $stmtVisit->execute(["employee_id" => $employeeId]);
                $visits = $stmtVisit->fetchAll(PDO::FETCH_ASSOC);
            }

            $result = [];
            foreach ($visits as $visit) {
                $visitId = $visit['id'];

                if ($isPrivileged) {
                    // Privileged -> semua log
                    $sqlLog = "SELECT l.*, 
                                    ep.full_name AS pic_name,
                                    en.full_name AS next_pic_name
                            FROM ar_visit_log l
                            LEFT JOIN ar_employee ep ON l.pic = ep.id
                            LEFT JOIN ar_employee en ON l.next_pic = en.id
                            WHERE l.visit_id = :visit_id
                            ORDER BY l.visit_date DESC, l.created_at DESC";
                    $stmtLog = $db->prepare($sqlLog);
                    $stmtLog->execute(["visit_id" => $visitId]);
                } else {
                    // Non-privileged -> hanya log yg terkait employee login
                    $sqlLog = "SELECT l.*, 
                                    ep.full_name AS pic_name,
                                    en.full_name AS next_pic_name
                            FROM ar_visit_log l
                            LEFT JOIN ar_employee ep ON l.pic = ep.id
                            LEFT JOIN ar_employee en ON l.next_pic = en.id
                            WHERE l.visit_id = :visit_id 
                                AND (l.pic = :employee_id OR l.next_pic = :employee_id OR l.created_by = :employee_id)
                            ORDER BY l.visit_date DESC, l.created_at DESC";
                    $stmtLog = $db->prepare($sqlLog);
                    $stmtLog->execute([
                        "visit_id"    => $visitId,
                        "employee_id" => $employeeId
                    ]);
                }

                $logs = $stmtLog->fetchAll(PDO::FETCH_ASSOC);

                $result[] = [
                    "visit" => $visit,
                    "logs"  => $logs
                ];
            }

            return $response->withJson([
                "status" => true,
                "is_privileged" => $isPrivileged,
                "data"   => $result
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });



    // ==========================
    // VISIT DETAIL + LOGS
    // ==========================
    // NOTE: route regex diubah supaya menerima ID custom seperti 123-456789-01
    $app->get('/ar_visit/{id:[A-Za-z0-9\-]+}', function (Request $request, Response $response, $args) {
        $db = $this->get('db_default');
        $visit_id = $args['id'];
        $employeeId = $request->getQueryParams()['employee_id'] ?? null;

        if (!$employeeId) {
            return $response->withJson([
                "status"  => false,
                "message" => "employee_id required"
            ], 400);
        }

        try {
            // cek posisi employee berdasarkan ID
            $stmt = $db->prepare("
                SELECT p.id
                FROM ar_employee e
                JOIN ar_position p ON e.position_id = p.id
                WHERE e.id = :employee_id
                LIMIT 1
            ");
            $stmt->execute(["employee_id" => $employeeId]);
            $position = $stmt->fetch(PDO::FETCH_ASSOC);

            // daftar ID posisi yang privileged
            $privilegedPositions = [
                'PO00005', // Head Sales Dept
                'PO00019', // Sales Representative
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            // cek apakah employee termasuk privileged
            $isPrivileged = isset($position['id']) && in_array($position['id'], $privilegedPositions);


            if ($isPrivileged) {
                // boleh lihat semua visit
                $sql = "SELECT v.*, e.full_name AS created_by_name
                        FROM ar_visit v
                        LEFT JOIN ar_employee e ON v.created_by = e.id
                        WHERE v.id = :id
                        LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute(["id" => $visit_id]);
            } else {
                // hanya boleh lihat visit yang terkait dirinya
                $sql = "SELECT v.*, e.full_name AS created_by_name
                        FROM ar_visit v
                        LEFT JOIN ar_employee e ON v.created_by = e.id
                        LEFT JOIN ar_visit_log l ON v.id = l.visit_id
                        WHERE v.id = :id
                        AND (v.created_by = :employee_id OR l.pic = :employee_id OR l.next_pic = :employee_id)
                        LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    "id" => $visit_id,
                    "employee_id" => $employeeId
                ]);
            }

            $visit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$visit) {
                return $response->withJson([
                    "status"  => false,
                    "message" => "Visit not found or employee not allowed"
                ], 404);
            }

            // ambil log visit termasuk tipe next_pic
            $sqlLog = "SELECT l.*, 
                            ep.full_name AS pic_name, 
                            en.full_name AS next_pic_name, 
                            ec.full_name AS created_by_name,
                            dn.id AS next_pic_dept_id,
                            CASE 
                                WHEN dn.id = 'DEP000003' THEN 'sales'
                                WHEN dn.id = 'DEP000004' THEN 'technician'
                                ELSE 'all'
                            END AS next_pic_type
                    FROM ar_visit_log l
                    LEFT JOIN ar_employee ep ON l.pic = ep.id
                    LEFT JOIN ar_employee en ON l.next_pic = en.id
                    LEFT JOIN ar_position pn ON en.position_id = pn.id
                    LEFT JOIN ar_department dn ON pn.dept_id = dn.id
                    LEFT JOIN ar_employee ec ON l.created_by = ec.id
                    WHERE l.visit_id = :visit_id
                    ORDER BY l.visit_date DESC, l.created_at DESC";
            $stmt = $db->prepare($sqlLog);
            $stmt->execute(["visit_id" => $visit_id]);
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "is_privileged" => $isPrivileged,
                "data"   => [
                    "visit" => $visit,
                    "logs"  => $logs
                ]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });




    // ==========================
    // ADD LOG
    // ==========================
    $app->post('/ar_visit/{id:[A-Za-z0-9\-]+}/log', function (Request $request, Response $response, $args) {
        $db = $this->get('db_default');
        $visit_id = $args['id'];
        $data = $request->getParsedBody();

        try {
            $status = $data["status"] ?? "follow_up";

            // 🔹 Ambil log terakhir
            $stmt = $db->prepare("SELECT * FROM ar_visit_log WHERE visit_id = :visit_id ORDER BY created_at DESC LIMIT 1");
            $stmt->execute(["visit_id" => $visit_id]);
            $lastLog = $stmt->fetch(PDO::FETCH_ASSOC);

            // 🔹 Tentukan pic & next_pic
            if ($status === "done") {
                $pic = $lastLog['next_pic'] ?? $data['created_by'];
                $next_pic = null;
            } else { // follow_up
                $pic = $lastLog['next_pic'] ?? $data['created_by']; // ambil next_pic dari log sebelumnya
                $next_pic = $data['next_pic'] ?? null; // next PIC baru
            }

            // 🔹 Generate Custom ID untuk log
            $logId = generateCustomId();

            // 🔹 Insert log
            $sqlLog = "INSERT INTO ar_visit_log 
                        (id, visit_id, visit_date, note, pic, next_pic, status, created_by, created_at, updated_at) 
                    VALUES (:id, :visit_id, :visit_date, :note, :pic, :next_pic, :status, :created_by, NOW(), NOW())";
            $stmt = $db->prepare($sqlLog);
            $stmt->execute([
                "id"         => $logId,
                "visit_id"   => $visit_id,
                "visit_date" => $data["visit_date"] ?? date('Y-m-d'),
                "note"       => $data["note"] ?? null,
                "pic"        => $pic,
                "next_pic"   => $next_pic,
                "status"     => $status,
                "created_by" => $data["created_by"],
            ]);

            // 🔔 Notifikasi Friendly
            $title = "Update Laporan Kunjungan";
            $visitDateText = isset($data["visit_date"]) ? date("d M Y", strtotime($data["visit_date"])) : "hari ini";
            $noteText = isset($data["note"]) ? " Catatan: {$data['note']}" : "";
            $statusText = $status === "done" ? "Laporan kunjungan telah selesai." : "Ada update baru pada laporan kunjungan.";
            $msg = "Hai! $statusText Tanggal visit: $visitDateText.$noteText";

            // 🔹 Target Positions
            $targetPositions = [
                'PO00005', // Head Sales Dept
                'PO00019', // Sales Representative
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            // Ambil player IDs posisi tertentu
            $positionPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

            // Ambil player_id creator log
            $creatorPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data["created_by"]);

            // Hapus creator dari targetPositions
            $positionPlayerIds = array_diff($positionPlayerIds, $creatorPlayerIds);

            // Ambil player ID next_pic jika ada
            $nextPicPlayerIds = [];
            if (!empty($next_pic)) {
                $nextPicPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $next_pic);
            }

            // Gabungkan posisi target + next_pic, hindari duplikasi
            $allPlayerIds = array_unique(array_merge($positionPlayerIds, $nextPicPlayerIds));

            // Hapus creator dari daftar final, termasuk jika dia juga next_pic
            $allPlayerIds = array_diff($allPlayerIds, $creatorPlayerIds);

            // 🔹 Kirim notifikasi hanya jika ada player ID tersisa
            if (!empty($allPlayerIds)) {
                OneSignalHelper::sendNotification(
                    $allPlayerIds,
                    $msg,
                    $title,
                    [
                        "type"     => "visitLogAdd",
                        "visit_id" => $visit_id,
                        "log_id"   => $logId
                    ]
                );

                // Simpan notifikasi ke database
                foreach ($allPlayerIds as $playerId) {
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
                            'type'    => 'visitLogAdd'
                        ]);
                    }
                }
            }

            return $response->withJson([
                "status"  => true,
                "message" => "Log added successfully",
                "data"    => ["log_id" => $logId]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    // ==========================
    // UPDATE VISIT (Partial Update)
    // ==========================
    $app->put('/ar_visit/{id:[A-Za-z0-9\-]+}', function (Request $request, Response $response, $args) {
        $db = $this->get('db_default');
        $visit_id = $args['id'];
        $data = $request->getParsedBody();

        try {
            $fields = [];
            $params = ["id" => $visit_id];

            if (isset($data["client_name"])) {
                $fields[] = "client_name = :client_name";
                $params["client_name"] = $data["client_name"];
            }
            if (isset($data["subject"])) {
                $fields[] = "subject = :subject";
                $params["subject"] = $data["subject"];
            }

            if (empty($fields)) {
                return $response->withJson([
                    "status"  => false,
                    "message" => "No data provided for update"
                ], 400);
            }

            // 🔹 Update data visit
            $sql = "UPDATE ar_visit SET " . implode(", ", $fields) . ", updated_at = NOW() WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // 🔔 Notifikasi Friendly
            $title = "Update Visit";
            $clientText = isset($data["client_name"]) ? $data["client_name"] : "klien";
            $subjectText = isset($data["subject"]) ? " dengan subject '{$data["subject"]}'" : "";
            $msg = "Hai! Visit untuk $clientText$subjectText telah diperbarui. Silakan cek detailnya.";

            // 🔹 Target Positions
            $targetPositions = [
                'PO00005', // Head Sales Dept
                'PO00019', // Sales Representative
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            // 🔹 Ambil player IDs posisi tertentu
            $positionPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

            // 🔹 Ambil player ID next_pic dari log terakhir jika ada
            $nextPicPlayerIds = [];
            $stmtLog = $db->prepare("SELECT next_pic FROM ar_visit_log WHERE visit_id = :visit_id ORDER BY created_at DESC LIMIT 1");
            $stmtLog->execute(["visit_id" => $visit_id]);
            $lastLog = $stmtLog->fetch(PDO::FETCH_ASSOC);
            if (!empty($lastLog["next_pic"])) {
                $nextPicPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $lastLog["next_pic"]);
            }

            // 🔹 Hapus updater sendiri dari daftar notifikasi
            $updaterPlayerIds = [];
            if (isset($data["updated_by"])) {
                $updaterPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data["updated_by"]);
            }

            // 🔹 Gabungkan posisi target + next_pic, lalu hapus updater
            $allPlayerIds = array_diff(array_unique(array_merge($positionPlayerIds, $nextPicPlayerIds)), $updaterPlayerIds);

            // 🔹 Kirim notifikasi dan simpan ke database
            if (!empty($allPlayerIds)) {
                OneSignalHelper::sendNotification(
                    $allPlayerIds,
                    $msg,
                    $title,
                    [
                        "type" => "visitUpdate",
                        "visit_id" => $visit_id
                    ]
                );

                // 🔹 Simpan notifikasi ke tabel ar_user_notifications
                foreach ($allPlayerIds as $playerId) {
                    $userId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                    if ($userId) {
                        $notifId = generateCustomId();
                        $stmtNotif = $db->prepare("
                            INSERT INTO ar_user_notifications (id, user_id, type, read_status, created_at)
                            VALUES (:id, :user_id, :type, 0, NOW())
                        ");
                        $stmtNotif->execute([
                            "id"      => $notifId,
                            "user_id" => $userId,
                            "type"    => "visitUpdate"
                        ]);
                    }
                }
            }

            return $response->withJson([
                "status"  => true,
                "message" => "Visit updated successfully",
                "data"    => ["visit_id" => $visit_id]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });



    // ==========================
    // UPDATE VISIT LOG (Partial Update)
    // ==========================
    $app->put('/ar_visit/{visit_id:[A-Za-z0-9\-]+}/log/{log_id:[A-Za-z0-9\-]+}', function (Request $request, Response $response, $args) {
        $db = $this->get('db_default');
        $visit_id = $args['visit_id'];
        $log_id   = $args['log_id'];
        $data = $request->getParsedBody();

        try {
            $fields = [];
            $params = ["visit_id" => $visit_id, "log_id" => $log_id];

            if (isset($data["visit_date"])) {
                $fields[] = "visit_date = :visit_date";
                $params["visit_date"] = $data["visit_date"];
            }
            if (isset($data["note"])) {
                $fields[] = "note = :note";
                $params["note"] = $data["note"];
            }
            if (isset($data["status"])) {
                $fields[] = "status = :status";
                $params["status"] = $data["status"];
                if ($data["status"] === "done") {
                    $fields[] = "next_pic = NULL";
                }
            }
            if (isset($data["next_pic"]) && ($data["status"] ?? '') !== "done") {
                $fields[] = "next_pic = :next_pic";
                $params["next_pic"] = $data["next_pic"];
            }

            if (empty($fields)) {
                return $response->withJson([
                    "status"  => false,
                    "message" => "No data provided for update"
                ], 400);
            }

            $sql = "UPDATE ar_visit_log 
                    SET " . implode(", ", $fields) . ", updated_at = NOW()
                    WHERE id = :log_id AND visit_id = :visit_id";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            // 🔔 Notifikasi Friendly
            $title = "Update Laporan Kunjungan";
            $visitDateText = isset($data["visit_date"]) ? date("d M Y", strtotime($data["visit_date"])) : "baru saja";
            $noteText = isset($data["note"]) ? " Catatan: {$data['note']}" : "";
            $statusText = ($data["status"] ?? '') === "done" ? "Laporan kunjungan telah selesai." : "Ada update baru pada laporan kunjungan.";
            $msg = "Hai! $statusText Tanggal visit: $visitDateText.$noteText";

            // 🎯 Target Positions
            $targetPositions = [
                'PO00005', // Head Sales Dept
                'PO00019', // Sales Representative
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            // 🎯 Ambil player IDs posisi tertentu
            $positionPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

            // 🎯 Hapus player_id pembuat log dari posisi target
            if (isset($data["updated_by"])) {
                $creatorPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data["updated_by"]);
                $positionPlayerIds = array_diff($positionPlayerIds, $creatorPlayerIds);
            }

            // 🎯 Ambil player ID next_pic jika ada dan status belum done
            $nextPicPlayerIds = [];
            if (!empty($data["next_pic"]) && ($data["status"] ?? '') !== "done") {
                $nextPicPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data["next_pic"]);
            }

            // 🧩 Gabungkan semua target player
            $allPlayerIds = array_unique(array_merge($positionPlayerIds, $nextPicPlayerIds));

            // 🧩 Simpan ke tabel ar_user_notifications untuk background handling
            if (!empty($allPlayerIds)) {
                foreach ($allPlayerIds as $pid) {
                    $userId = EmployeeHelper::getUserIdByPlayerId($db, $pid);
                    if ($userId) {
                        $notifId = generateCustomId();
                        $stmtNotif = $db->prepare("
                            INSERT INTO ar_user_notifications (id, user_id, type, read_status, created_at)
                            VALUES (:id, :user_id, :type, 0, NOW())
                        ");
                        $stmtNotif->execute([
                            'id' => $notifId,
                            'user_id' => $userId,
                            'type' => 'visitLogUpdate'
                        ]);
                    }
                }

                // 🔹 Kirim push notif ke OneSignal
                OneSignalHelper::sendNotification(
                    $allPlayerIds,
                    $msg,
                    $title,
                    [
                        "type" => "visitLogUpdate",
                        "visit_id" => $visit_id,
                        "log_id" => $log_id
                    ]
                );
            }

            return $response->withJson([
                "status"  => true,
                "message" => "Log updated successfully",
                "data"    => ["log_id" => $log_id]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


};
