<?php
require_once __DIR__ . '/../helpers/OneSignalHelper.php';
require_once __DIR__ . '/../helpers/EmployeeHelper.php';
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->post('/attendance', function (Request $request, Response $response) {
        $postData      = $request->getParsedBody();     // data form biasa
        $uploadedFiles = $request->getUploadedFiles();  // file upload

        $employeeId  = $postData['employee_id'] ?? null;
        $latitude    = $postData['latitude'] ?? null;
        $longitude   = $postData['longitude'] ?? null;
        $checkinTime = $postData['checkin'] ?? null;
        $notes       = $postData['notes'] ?? '';
        $photo       = $uploadedFiles['photo'] ?? null;

        // Validasi input
        if (empty($employeeId) || empty($latitude) || empty($longitude) || empty($checkinTime)) {
            $data = [
                'status'  => false,
                'message' => 'Employee ID, latitude, longitude, and check-in time are required'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Direktori simpan foto
        $uploadDir   = __DIR__ . '/../../public/uploads/checkin/';
        $relativeDir = 'uploads/checkin/'; // ini yang disimpan ke DB

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $photoPath = null;
        if ($photo && $photo->getError() === UPLOAD_ERR_OK) {
            $extension     = pathinfo($photo->getClientFilename(), PATHINFO_EXTENSION);
            $photoFileName = uniqid('checkin_') . '.' . $extension;

            // simpan file ke folder fisik
            $photo->moveTo($uploadDir . $photoFileName);

            // simpan path relatif
            $photoPath = $relativeDir . $photoFileName;
        }

        try {
            $attenId = sprintf(
                "%03d-%06d-%02d",
                rand(100, 999),
                rand(100000, 999999),
                (int)substr(microtime(true) * 100, -2)
            );

            // Pilih database
            $db = $this->get('db_default');

            $sql = "INSERT INTO ar_absent (id, employee_id, lat, lon, checkin, notes, photo) 
                    VALUES (:id, :employee_id, :lat, :lon, :checkin, :notes, :photo)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                "id"          => $attenId,
                'employee_id' => $employeeId,
                'lat'         => $latitude,
                'lon'         => $longitude,
                'checkin'     => $checkinTime,
                'notes'       => $notes,
                'photo'       => $photoPath
            ]);

            $data = [
                'status'  => true,
                'message' => 'Check-in successful',
                'photo'   => $photoPath
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (PDOException $e) {
            $data = [
                'status'  => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });


    $app->get("/check-missing-attendance", function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $today = date("Y-m-d");

        try {
            // 🔹 Ambil semua employee yang tidak absen, tidak cuti, dan tidak izin
            $stmt = $db->prepare("
                SELECT e.id, e.full_name
                FROM ar_employee e
                WHERE e.id NOT IN (
                    SELECT employee_id FROM ar_absent WHERE DATE(checkin) = :today
                    UNION
                    SELECT employee_id FROM ar_leave_request 
                    WHERE :today BETWEEN start_date AND end_date AND status = 'approved'
                    UNION
                    SELECT employee_id FROM ar_absence WHERE absence_date = :today
                )
            ");
            $stmt->execute(["today" => $today]);
            $missing = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($missing)) {
                $missingNames = array_column($missing, 'full_name');

                // 🔹 Target posisi berdasarkan ID
                $targetPositions = [
                    'PO00001', // President Director
                    'PO00013'  // Corporate Secretary
                ];

                // 🔹 Ambil semua player_id target posisi via helper
                $playerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

                // 🔹 Kirim notifikasi & simpan log notifikasi
                if (!empty($playerIds)) {
                    foreach ($playerIds as $playerId) {
                        // Ambil user_id dari player_id
                        $userId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                        if (!$userId) continue;

                        // Simpan log notifikasi
                        $stmtNotif = $db->prepare("
                            INSERT INTO ar_user_notifications (id, user_id, type, created_at)
                            VALUES (:id, :user_id, :type, NOW())
                        ");
                        $stmtNotif->execute([
                            ':id' => generateCustomId(),
                            ':user_id' => $userId,
                            ':type' => 'missing_attendance'
                        ]);
                    }

                    // Kirim notifikasi via OneSignal ke semua player_id sekaligus
                    OneSignalHelper::sendNotification(
                        $playerIds,
                        "⚠️ Belum absen: " . implode(", ", $missingNames),
                        "Reminder Absensi",
                        [
                            'type' => 'missing_attendance',
                            'missing_list' => $missingNames
                        ]
                    );
                }
            }

            return $response->withJson([
                "status" => true,
                "missing" => $missing
            ]);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });





    // $app->post('/notify_out_of_zone', function (Request $request, Response $response) {
    //     $db = $this->get('db_default');
    //     $data = $request->getParsedBody();

    //     $employeeId = $data['employee_id'] ?? 'Unknown';
    //     $lat = $data['latitude'] ?? '';
    //     $lon = $data['longitude'] ?? '';

    //     $msg = "Employee $employeeId is out of attendance zone at lat:$lat, lon:$lon";
    //     $title = "Out of Zone Alert";

    //     // 🔹 Target posisi manajemen
    //     $targetPositions = [
    //         "Head Sales Dept",
    //         "Sales Representative",
    //         "President Director",
    //         "Corporate Secretary",
    //         "Head Project and Customer Service Dept",
    //         'Operational Manager'
    //     ];

    //     // 🔹 Ambil player IDs posisi manajemen
    //     $positionPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);

    //     // 🔹 Ambil player ID employee yang keluar zona
    //     $employeePlayerIds = EmployeeHelper::getPlayerIdsByEmployee($employeeId);

    //     // 🔹 Gabungkan semua dan hapus duplikasi agar tidak ada double notif
    //     $allPlayerIds = array_unique(array_merge($positionPlayerIds, $employeePlayerIds));

    //     // 🔹 Kirim notifikasi jika ada player ID
    //     if (!empty($allPlayerIds)) {
    //         $result = OneSignalHelper::sendNotification($allPlayerIds, $msg, $title, [
    //             "type" => "notify_out_of_zone",
    //             "employee_id" => $employeeId,
    //             "latitude" => $lat,
    //             "longitude" => $lon
    //         ]);
    //     }

    //     return $response->withJson([
    //         'status' => 'success',
    //         'result' => $result ?? null
    //     ]);
    // });

};
