<?php
require_once __DIR__ . '/../helpers/mapOfficeStatus.php';
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {
    

    $app->get("/history-attendance", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $employee_id = $params["employee_id"] ?? null;

        if (!$employee_id) {
            $data = [
                "status" => false,
                "message" => "Parameter 'employee_id' is required"
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }

        $db = $this->get('db_default');
        $month = date("m");
        $year = date("Y");

        $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
        $port = $request->getUri()->getPort();
        if ($port) {
            $baseUrl .= ":" . $port;
        }

        try {
            // Cek posisi karyawan
            $checkPositionSql = "
                SELECT p.id
                FROM ar_employee e
                JOIN ar_position p ON e.position_id = p.id
                WHERE e.id = :employee_id
                LIMIT 1
            ";
            $stmt = $db->prepare($checkPositionSql);
            $stmt->execute(["employee_id" => $employee_id]);
            $position = $stmt->fetch(PDO::FETCH_ASSOC);

            $isPresident = isset($position['id']) &&
                in_array(
                    strtoupper(trim($position['id'])),
                    [
                        'PO00001', // President Director
                        'PO00013', // Corporate Secretary
                        'PO00006', // Head Project and Customer Service Dept
                        'PO00003', // Advisory Product Dev.
                        'PO00014'  // Operational Manager
                    ]
                );

            // Ambil lokasi kantor + radius
            $officeStmt = $db->query("SELECT office_name, lat, lon FROM ar_office_location");
            $offices = $officeStmt->fetchAll(PDO::FETCH_ASSOC);

            $radiusStmt = $db->prepare("
                SELECT setting_value 
                FROM ar_settings 
                WHERE setting_key = 'office_radius' 
                LIMIT 1
            ");
            $radiusStmt->execute();
            $officeRadius = (float)($radiusStmt->fetchColumn() ?? 100);

            // ============================================================
            //  BAGIAN PRESIDENT  (melihat semua karyawan)
            // ============================================================
            if ($isPresident) {
                $sql = "
                    SELECT 
                        e.id,
                        e.full_name,
                        e.photo AS photo,
                        p.position_name,
                        COALESCE(att.attendance_count, 0) AS attendance_count,
                        COALESCE(u.has_checked_in_today, 0) AS has_checked_in_today,
                        COALESCE(u.is_leave_today, 0) AS is_leave_today,
                        COALESCE(u.is_izin_today, 0) AS is_izin_today,
                        u.last_lat,
                        u.last_lon,
                        u.absent_photo
                    FROM ar_employee e
                    LEFT JOIN ar_position p ON p.id = e.position_id

                    -- hitung kehadiran per bulan
                    LEFT JOIN (
                        SELECT employee_id, COUNT(*) AS attendance_count
                        FROM ar_absent
                        WHERE MONTH(checkin) = :month AND YEAR(checkin) = :year
                        GROUP BY employee_id
                    ) att ON att.employee_id = e.id

                    -- gabungan absensi, izin, cuti hari ini (sudah di-aggregate per employee)
                    LEFT JOIN (
                        SELECT employee_id,
                            MAX(CASE WHEN activity_type = 'checkin' THEN 1 ELSE 0 END) AS has_checked_in_today,
                            MAX(CASE WHEN activity_type = 'leave' THEN 1 ELSE 0 END) AS is_leave_today,
                            MAX(CASE WHEN activity_type = 'izin' THEN 1 ELSE 0 END) AS is_izin_today,
                            MAX(last_lat) AS last_lat,
                            MAX(last_lon) AS last_lon,
                            MAX(absent_photo) AS absent_photo
                        FROM (
                            SELECT employee_id,
                                'checkin' AS activity_type,
                                lat AS last_lat,
                                lon AS last_lon,
                                photo AS absent_photo
                            FROM ar_absent
                            WHERE DATE(checkin) = CURDATE()

                            UNION ALL

                            SELECT employee_id,
                                'izin' AS activity_type,
                                NULL AS last_lat,
                                NULL AS last_lon,
                                NULL AS absent_photo
                            FROM ar_absence
                            WHERE DATE(absence_date) = CURDATE()

                            UNION ALL

                            SELECT employee_id,
                                'leave' AS activity_type,
                                NULL AS last_lat,
                                NULL AS last_lon,
                                NULL AS absent_photo
                            FROM ar_leave_request
                            WHERE CURDATE() BETWEEN start_date AND end_date
                        ) AS daily_activities
                        GROUP BY employee_id
                    ) u ON u.employee_id = e.id

                    ORDER BY e.full_name ASC
                ";

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    "month" => $month,
                    "year" => $year
                ]);

                $employeesRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $employees = array_map(function ($emp) use ($baseUrl, $offices, $officeRadius) {
                    $lastLat = isset($emp['last_lat']) ? (float)$emp['last_lat'] : null;
                    $lastLon = isset($emp['last_lon']) ? (float)$emp['last_lon'] : null;

                    $officeStatus = ['jakarta_office' => false, 'bandung_office' => false, 'is_dinas' => false];
                    if ($lastLat !== null && $lastLon !== null) {
                        $officeStatus = mapOfficeStatus($offices, $lastLat, $lastLon, $officeRadius);
                    }

                    return array_merge([
                        "id" => $emp["id"],
                        "full_name" => $emp["full_name"],
                        "position" => $emp["position_name"] ?? "-",
                        "photo" => !empty($emp["photo"]) ? $baseUrl . "/" . ltrim($emp["photo"], "/") : null,
                        "attendance_count" => (int)$emp["attendance_count"],
                        "has_checked_in_today" => (bool)$emp["has_checked_in_today"],
                        "is_leave_today" => (bool)$emp["is_leave_today"],
                        "is_izin_today" => (bool)$emp["is_izin_today"],
                    ], $officeStatus);
                }, $employeesRaw);

                $data = [
                    "status" => true,
                    "is_president" => true,
                    "data" => [
                        "employee_list" => $employees
                    ]
                ];
            }

            // ============================================================
            //  BAGIAN NON-PRESIDENT (riwayat pribadi)
            // ============================================================
            else {
                $sql = "
                    SELECT 
                        a.id,
                        a.employee_id,
                        a.checkin,
                        a.lat,
                        a.lon,
                        a.notes,
                        e.full_name,
                        e.photo AS employee_photo,
                        a.photo AS absent_photo,
                        p.position_name,
                        att.attendance_count
                    FROM ar_absent a
                    JOIN ar_employee e ON e.id = a.employee_id
                    LEFT JOIN ar_position p ON p.id = e.position_id
                    LEFT JOIN (
                        SELECT employee_id, COUNT(*) AS attendance_count
                        FROM ar_absent
                        WHERE MONTH(checkin) = :month AND YEAR(checkin) = :year
                        GROUP BY employee_id
                    ) att ON att.employee_id = e.id
                    WHERE a.employee_id = :employee_id
                    AND MONTH(a.checkin) = :month 
                    AND YEAR(a.checkin) = :year
                    ORDER BY a.checkin DESC
                ";

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    "employee_id" => $employee_id,
                    "month" => $month,
                    "year" => $year
                ]);

                $attendanceRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $attendance = array_map(function ($item) use ($baseUrl, $offices, $officeRadius) {
                    $lat = isset($item['lat']) ? (float)$item['lat'] : null;
                    $lon = isset($item['lon']) ? (float)$item['lon'] : null;

                    $officeStatus = ['jakarta_office' => false, 'bandung_office' => false, 'is_dinas' => false];
                    if ($lat !== null && $lon !== null) {
                        $officeStatus = mapOfficeStatus($offices, $lat, $lon, $officeRadius);
                    }

                    return array_merge([
                        "id" => $item["id"],
                        "employee_id" => $item["employee_id"],
                        "full_name" => $item["full_name"],
                        "position" => $item["position_name"] ?? "-",
                        "employee_photo" => !empty($item["employee_photo"]) ? $baseUrl . "/" . ltrim($item["employee_photo"], "/") : null,
                        "absent_photo" => !empty($item["absent_photo"]) ? $baseUrl . "/" . ltrim($item["absent_photo"], "/") : null,
                        "checkin" => $item["checkin"],
                        "location" => $item["lat"] . "," . $item["lon"],
                        "note" => $item["notes"],
                        "attendance_count" => (int)$item["attendance_count"],
                    ], $officeStatus);
                }, $attendanceRaw);

                $data = [
                    "status" => true,
                    "is_president" => false,
                    "data" => [
                        "history_attendance" => $attendance
                    ]
                ];
            }

            $response->getBody()->write(json_encode($data));
            return $response->withHeader("Content-Type", "application/json")->withStatus(200);

        } catch (PDOException $e) {
            $data = [
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    });





    $app->get("/history-attendance/detail", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $employee_id = $params["employee_id"] ?? null;

        if (!$employee_id) {
            return $response->withJson([
                "status" => false,
                "message" => "employee_id is required"
            ], 400);
        }

        $db = $this->get('db_default');
        $month = date("m");
        $year = date("Y");

        // Ambil lokasi kantor + nama kantor
        $officeStmt = $db->query("SELECT office_name, lat, lon FROM ar_office_location");
        $offices = $officeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil radius dari DB
        $radiusStmt = $db->prepare("
            SELECT setting_value 
            FROM ar_settings 
            WHERE setting_key = 'office_radius' 
            LIMIT 1
        ");
        $radiusStmt->execute();
        $officeRadius = (float)($radiusStmt->fetchColumn() ?? 100);

        // Base URL untuk foto
        $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
        $port    = $request->getUri()->getPort();
        if ($port) {
            $baseUrl .= ":" . $port;
        }

        $sql = "
            SELECT 
                a.id,
                a.employee_id,
                a.checkin,
                a.lat,
                a.lon,
                a.notes,
                a.photo AS absent_photo
            FROM ar_absent a
            WHERE a.employee_id = :employee_id
            AND MONTH(a.checkin) = :month AND YEAR(a.checkin) = :year
            ORDER BY a.checkin DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            "employee_id" => $employee_id,
            "month" => $month,
            "year" => $year
        ]);

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $attendance = array_map(function ($item) use ($offices, $officeRadius, $baseUrl) {
            $lat = isset($item['lat']) ? (float)$item['lat'] : null;
            $lon = isset($item['lon']) ? (float)$item['lon'] : null;

            $officeStatus = [
                'jakarta_office' => false,
                'bandung_office' => false,
                'is_dinas' => false,
            ];

            if ($lat !== null && $lon !== null) {
                $officeStatus = mapOfficeStatus($offices, $lat, $lon, $officeRadius);
            }

            return [
                "id" => $item["id"],
                "employee_id" => $item["employee_id"],
                "checkin" => $item["checkin"],
                "location" => $item["lat"] . "," . $item["lon"],
                "note" => $item["notes"],
                "absent_photo" => !empty($item["absent_photo"]) 
                    ? $baseUrl . "/" . ltrim($item["absent_photo"], "/") 
                    : null,
            ] + $officeStatus;
        }, $result);

        return $response->withJson([
            "status" => true,
            "data" => [
                'history_attendance' => $attendance
            ]
        ], 200);
    });



    $app->get("/history-attendance/by-date", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $employee_id = $params["employee_id"] ?? null;
        $date = $params["date"] ?? null; // Format YYYY-MM-DD

        if (!$employee_id || !$date) {
            return $response->withJson([
                "status" => false,
                "message" => "employee_id and date are required"
            ], 400);
        }

        $db = $this->get('db_default');

        // Base URL untuk foto
        $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
        $port    = $request->getUri()->getPort();
        if ($port) {
            $baseUrl .= ":" . $port;
        }

        // Ambil lokasi kantor + nama kantor
        $officeStmt = $db->query("SELECT office_name, lat, lon FROM ar_office_location");
        $offices = $officeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Ambil radius dari DB
        $radiusStmt = $db->prepare("
            SELECT setting_value 
            FROM ar_settings 
            WHERE setting_key = 'office_radius' 
            LIMIT 1
        ");
        $radiusStmt->execute();
        $officeRadius = (float)($radiusStmt->fetchColumn() ?? 100);

        try {
            $sql = "
                SELECT 
                    a.id,
                    a.employee_id,
                    a.checkin,
                    a.lat,
                    a.lon,
                    a.notes,
                    a.photo
                FROM ar_absent a
                WHERE a.employee_id = :employee_id
                AND DATE(a.checkin) = :date
                ORDER BY a.checkin DESC
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                "employee_id" => $employee_id,
                "date" => $date
            ]);

            $result = $stmt->fetchAll();

            $attendance = array_map(function ($item) use ($offices, $officeRadius, $baseUrl) {
                $lat = isset($item['lat']) ? (float)$item['lat'] : null;
                $lon = isset($item['lon']) ? (float)$item['lon'] : null;

                $officeStatus = [
                    'jakarta_office' => false,
                    'bandung_office' => false,
                    'is_dinas' => false,
                ];

                if ($lat !== null && $lon !== null) {
                    $officeStatus = mapOfficeStatus($offices, $lat, $lon, $officeRadius);
                }

                // Ambil path foto dengan base URL
                $photoPath = $item["photo"] ? $baseUrl . "/" . $item["photo"] : null;

                return [
                    "id" => $item["id"],
                    "employee_id" => $item["employee_id"],
                    "checkin" => $item["checkin"],
                    "location" => $item["lat"] . "," . $item["lon"],
                    "note" => $item["notes"],
                    "photo" => $photoPath
                ] + $officeStatus;
            }, $result);

            return $response->withJson([
                "status" => true,
                "data" => [
                    "history_attendance" => $attendance
                ]
            ], 200);
        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });



    $app->get("/today-attendance", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $employee_id = $params["employee_id"] ?? null;

        if (!$employee_id) {
            $data = [
                "status" => false,
                "message" => "Parameter 'employee_id' is required"
            ];
            $response->getBody()->write(json_encode($data));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(400); // parameter salah
        }

        $db = $this->get('db_default');

        // Base URL untuk foto
        $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
        $port    = $request->getUri()->getPort();
        if ($port) {
            $baseUrl .= ":" . $port;
        }

        try {
            // 🔹 Cek posisi user
            $checkPositionSql = "
                SELECT p.id
                FROM ar_employee e
                JOIN ar_position p ON e.position_id = p.id
                WHERE e.id = :employee_id
                LIMIT 1
            ";
            $stmt = $db->prepare($checkPositionSql);
            $stmt->execute(["employee_id" => $employee_id]);
            $position = $stmt->fetch();

            $isPresident = isset($position['id']) &&
                in_array(
                    strtoupper(trim($position['id'])),
                    [
                        'PO00001', // President Director
                        'PO00013', // Corporate Secretary
                        'PO00006', // Head Project and Customer Service Dept
                        'PO00003', // Advisory Product Dev.
                        'PO00014'  // Operational Manager
                    ]
                );

            // 🔹 Ambil daftar kantor
            $officeStmt = $db->query("SELECT office_name, lat, lon FROM ar_office_location");
            $offices = $officeStmt->fetchAll(PDO::FETCH_ASSOC);

            // Ambil radius dari settings (default 100 meter)
            $radiusStmt = $db->prepare("
                SELECT setting_value 
                FROM ar_settings 
                WHERE setting_key = 'office_radius' 
                LIMIT 1
            ");
            $radiusStmt->execute();
            $officeRadius = (float)($radiusStmt->fetchColumn() ?? 100);

            // Query absensi hari ini
            if ($isPresident) {
                // Presiden → ambil semua absen hari ini
                $sql = "
                    SELECT 
                        a.id,
                        a.employee_id,
                        e.full_name,
                        a.checkin,
                        a.lat,
                        a.lon,
                        a.notes,
                        a.photo
                    FROM ar_absent a
                    JOIN ar_employee e ON e.id = a.employee_id
                    WHERE DATE(a.checkin) = CURRENT_DATE
                    ORDER BY a.checkin DESC
                ";
                $stmt = $db->query($sql);
            } else {
                // Non-presiden → hanya miliknya
                $sql = "
                    SELECT 
                        a.id,
                        a.employee_id,
                        e.full_name,
                        a.checkin,
                        a.lat,
                        a.lon,
                        a.notes,
                        a.photo
                    FROM ar_absent a
                    JOIN ar_employee e ON e.id = a.employee_id
                    WHERE a.employee_id = :employee_id
                    AND DATE(a.checkin) = CURRENT_DATE
                    ORDER BY a.checkin DESC
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute(["employee_id" => $employee_id]);
            }

            $attendanceRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 🔹 Jika tidak ada absen hari ini
            if (!$attendanceRaw) {
                $data = [
                    "status" => false,
                    "is_president" => $isPresident,
                    "message" => "No check-in found for today"
                ];
                $response->getBody()->write(json_encode($data));
                return $response
                    ->withHeader("Content-Type", "application/json")
                    ->withStatus(200); // ✅ ubah ke 200 agar Flutter tidak anggap error
            }

            // 🔹 Mapping hasil + hitung jarak
            $attendance = array_map(function ($item) use ($offices, $officeRadius, $baseUrl) {
                $jakartaOffice = false;
                $bandungOffice = false;
                $insideAnyOffice = false;

                if (!empty($item["lat"]) && !empty($item["lon"])) {
                    foreach ($offices as $office) {
                        $dist = distanceInMeters(
                            (float)$item["lat"],
                            (float)$item["lon"],
                            (float)$office["lat"],
                            (float)$office["lon"]
                        );
                        $isInside = $dist <= $officeRadius;

                        if (strtolower($office["office_name"]) === "jakarta_office") {
                            $jakartaOffice = $isInside;
                        }
                        if (strtolower($office["office_name"]) === "bandung_office") {
                            $bandungOffice = $isInside;
                        }
                        if ($isInside) {
                            $insideAnyOffice = true;
                        }
                    }
                }

                return [
                    "id" => $item["id"],
                    "employee_id" => $item["employee_id"],
                    "full_name" => $item["full_name"],
                    "checkin" => $item["checkin"],
                    "location" => $item["lat"] . "," . $item["lon"],
                    "note" => $item["notes"],
                    "photo" => $item["photo"] ? $baseUrl . "/" . $item["photo"] : null,
                    "jakarta_office" => $jakartaOffice,
                    "bandung_office" => $bandungOffice,
                    "is_dinas" => (!empty($item["lat"]) && !empty($item["lon"])) ? !$insideAnyOffice : false
                ];
            }, $attendanceRaw);

            // 🔹 Data terakhir
            $lastAttendance = $attendance[0];

            $data = [
                "status" => true,
                "is_president" => $isPresident,
                "data" => [
                    "today_attendance" => $attendance,
                    "last_attendance" => $lastAttendance
                ]
            ];

            $response->getBody()->write(json_encode($data));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(200);

        } catch (PDOException $e) {
            $data = [
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
            $response->getBody()->write(json_encode($data));
            return $response
                ->withHeader("Content-Type", "application/json")
                ->withStatus(500);
        }
    });


};
