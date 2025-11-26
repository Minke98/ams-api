<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once __DIR__ . '/../helpers/IdHelper.php';

return function ($app) {

    // ============================
    // GET ABSENCE LIST
    // ============================
    $app->get('/ar_absence/list', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $params = $request->getQueryParams();
        $employee_id = $params['employee_id'] ?? null;

        if (!$employee_id) {
            return $response->withJson([
                "status"  => false,
                "message" => "Parameter 'employee_id' wajib diisi"
            ], 400);
        }

        try {
            // Ambil posisi pegawai dari join ke ar_position
            $sql = "
                SELECT p.id 
                FROM ar_employee e
                JOIN ar_position p ON e.position_id = p.id
                WHERE e.id = :employee_id
                LIMIT 1
            ";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':employee_id', $employee_id);
            $stmt->execute();
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Pegawai tidak ditemukan"
                ], 404);
            }

           $position = strtoupper(trim($employee['id']));
            $superior_positions = [
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00005', // Head Sales Dept
                'PO00004', // Head Finance & Accounting Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            $is_president = in_array($position, $superior_positions); // 🔹 flag

            // 🔹 Ambil data absensi
            if ($is_president) {
                $sql = "
                    SELECT 
                        a.id,
                        a.employee_id,
                        e.full_name,
                        p.position_name,
                        a.absence_date,
                        a.reason,
                        a.created_at,
                        a.updated_at
                    FROM ar_absence a
                    JOIN ar_employee e ON a.employee_id = e.id
                    JOIN ar_position p ON e.position_id = p.id
                    ORDER BY a.absence_date DESC
                ";
                $stmt = $db->prepare($sql);
            } else {
                $sql = "
                    SELECT 
                        a.id,
                        a.employee_id,
                        e.full_name,
                        p.position_name,
                        a.absence_date,
                        a.reason,
                        a.created_at,
                        a.updated_at
                    FROM ar_absence a
                    JOIN ar_employee e ON a.employee_id = e.id
                    JOIN ar_position p ON e.position_id = p.id
                    WHERE a.employee_id = :employee_id
                    ORDER BY a.absence_date DESC
                ";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':employee_id', $employee_id);
            }

            $stmt->execute();
            $absences = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status"       => true,
                "is_president" => $is_president, // 🔹 ditambahkan
                "data"         => $absences
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    // ============================
    // POST ADD ABSENCE
    // ============================
    $app->post('/ar_absence/add', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        $employee_id  = $data['employee_id'] ?? null;
        $absence_date = $data['absence_date'] ?? null;
        $reason       = $data['reason'] ?? null;

        if (!$employee_id || !$absence_date || !$reason) {
            return $response->withJson([
                "status"  => false,
                "message" => "Field tidak boleh kosong"
            ], 400);
        }

        try {
            // 🔹 Generate custom ID
            $absence_id = generateCustomId();

            // Simpan izin ke database
            $sql = "INSERT INTO ar_absence (id, employee_id, absence_date, reason, created_at, updated_at)
                    VALUES (:id, :employee_id, :absence_date, :reason, NOW(), NOW())";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $absence_id);
            $stmt->bindParam(':employee_id', $employee_id);
            $stmt->bindParam(':absence_date', $absence_date);
            $stmt->bindParam(':reason', $reason);
            $stmt->execute();

            // 🔹 Ambil nama & posisi pegawai dari join
            $sqlEmp = "
                SELECT e.full_name, p.id AS position_id, p.position_name
                FROM ar_employee e
                JOIN ar_position p ON e.position_id = p.id
                WHERE e.id = :employee_id
                LIMIT 1
            ";
            $stmtEmp = $db->prepare($sqlEmp);
            $stmtEmp->bindParam(':employee_id', $employee_id);
            $stmtEmp->execute();
            $employee = $stmtEmp->fetch(PDO::FETCH_ASSOC);

            $employee_name = $employee['full_name'] ?? 'Seorang pegawai';
            $employee_position = strtoupper(trim($employee['position_id'] ?? ''));


            // 🔹 Target Positions (berdasarkan ID)
            $targetPositions = [
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00005', // Head Sales Dept
                'PO00004', // Head Finance & Accounting Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];


            // 🔔 Pesan notifikasi
            $title = "Izin Tidak Hadir";
            $formattedDate = date("d M Y", strtotime($absence_date));
            $msg = "$employee_name telah mengajukan izin pada tanggal $formattedDate. Alasan: $reason";

            // 🔹 Ambil semua player_id target posisi
            $targetPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

            // 🔹 Ambil player_id pembuat izin
            $creatorPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $employee_id);

            // 🔹 Jika pembuat izin juga termasuk target_positions, hapus dia dari daftar notifikasi
            if (in_array($employee_position, $targetPositions)) {
                $targetPlayerIds = array_diff($targetPlayerIds, $creatorPlayerIds);
            }

            // 🔹 Kirim notifikasi hanya ke target yang tersisa
            if (!empty($targetPlayerIds)) {
                OneSignalHelper::sendNotification($targetPlayerIds, $msg, $title, [
                    "type"         => "absence",
                    "absence_id"   => $absence_id,
                    "absence_date" => $absence_date,
                    "employee_id"  => $employee_id
                ]);
            }

            return $response->withJson([
                "status"  => true,
                "message" => "Izin tidak hadir berhasil disimpan dan notifikasi dikirim",
                "id"      => $absence_id
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    // ============================
    // DELETE ABSENCE
    // ============================
    $app->delete('/ar_absence/delete', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $params = $request->getQueryParams(); // ambil query parameter
        $id = $params['id'] ?? null;

        if (!$id) {
            return $response->withJson([
                "status"  => false,
                "message" => "Parameter 'id' wajib diisi"
            ], 400);
        }

        try {
            // 🔹 Cek apakah data ada
            $checkSql = "SELECT id FROM ar_absence WHERE id = :id LIMIT 1";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $exist = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$exist) {
                return $response->withJson([
                    "status"  => false,
                    "message" => "Data absensi tidak ditemukan"
                ], 404);
            }

            // 🔹 Hapus data
            $sql = "DELETE FROM ar_absence WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $response->withJson([
                "status"  => true,
                "message" => "Data absensi berhasil dihapus"
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status"  => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });

};
