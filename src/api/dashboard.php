<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/dashboard/summary', function ($request, $response) {
        $db = $this->get('db_default');
    
        try {
            // 1️⃣ Hitung Ruangan + alat + software
            $stmt = $db->query("SELECT COUNT(*) AS total, 
                                    (SELECT COUNT(*) FROM mr_alat) AS total_alat,
                                    (SELECT COUNT(*) FROM mr_software) AS total_software
                                FROM mr_ruangan");
            $ruangan = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // 2️⃣ Hitung SDM total
            $stmt = $db->query("SELECT COUNT(*) AS total FROM mr_sdm");
            $sdm_total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
            // 3️⃣ Hitung SDM yang punya sertifikat aktif
            $stmt = $db->query("SELECT COUNT(DISTINCT sdm_id) AS certified 
                                FROM mr_sertifikasi 
                                WHERE status = 1"); // active
            $sdm_certified = $stmt->fetch(PDO::FETCH_ASSOC)['certified'];
    
            // 4️⃣ Tidak bersertifikat
            $sdm_uncertified = $sdm_total - $sdm_certified;
    
            // 5️⃣ Hitung Maintenance
            $stmt = $db->query("SELECT COUNT(*) AS total,
                                    SUM(CASE WHEN jenis_maintenance=1 THEN 1 ELSE 0 END) AS open,
                                    SUM(CASE WHEN jenis_maintenance=2 THEN 1 ELSE 0 END) AS in_progress,
                                    SUM(CASE WHEN jenis_maintenance=3 THEN 1 ELSE 0 END) AS close
                                FROM mr_maintenance");
            $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // 6️⃣ Hitung Laporan Kerusakan total
            $stmt = $db->query("SELECT COUNT(*) AS total FROM mr_laporan_kerusakan");
            $laporan = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // 7️⃣ Ruangan Digunakan & Tidak Digunakan
            $stmt = $db->query("SELECT 
                                    SUM(CASE WHEN status=1 THEN 1 ELSE 0 END) AS borrowed,
                                    SUM(CASE WHEN status=0 THEN 1 ELSE 0 END) AS not_used
                                FROM mr_penggunaan_ruangan");
            $ruangan_status = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // 8️⃣ Response Dashboard
            $data = [
                [
                    'title' => $ruangan['total'] . ' Ruangan',
                    'details' => [
                        $ruangan['total_alat'] . ' Alat',
                        $ruangan['total_software'] . ' Software',
                    ],
                ],
                [
                    'title' => $sdm_total . ' SDM',
                    'details' => [
                        $sdm_certified . ' Bersertifikat',
                        $sdm_uncertified . ' Tidak Bersertifikat',
                    ],
                ],
                [
                    'title' => $maintenance['total'] . ' Maintenance',
                    'details' => [
                        $maintenance['open'] . ' Open',
                        $maintenance['in_progress'] . ' In Progress',
                        $maintenance['close'] . ' Close',
                    ],
                ],
                [
                    'title' => $laporan['total'] . ' Laporan Kerusakan',
                    'details' => [
                        $ruangan_status['borrowed'] . ' Ruangan Digunakan',
                        $ruangan_status['not_used'] . ' Ruangan Tidak Digunakan',
                    ],
                ],
            ];
    
            $response->getBody()->write(json_encode([
                'status' => true,
                'data' => $data
            ]));
    
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });



    $app->get('/user-data', function (Request $request, Response $response) {
        $queryParams = $request->getQueryParams();
        $user_id = $queryParams['user_id'] ?? null;

        if (!$user_id) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'user_id' diperlukan"
            ], 400);
        }

        $db = $this->get("db_default");

        try {
            // Ambil user
            $stmt = $db->prepare("SELECT * FROM mr_users WHERE id = :id LIMIT 1");
            $stmt->execute(["id" => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $response->withJson([
                    "status" => false,
                    "message" => "User tidak ditemukan"
                ], 404);
            }

            unset($user['password']); // jangan kirim password

            // Base URL dinamis
            $uri = $request->getUri();
            $baseUrl = $uri->getScheme() . "://" . $uri->getHost();
            if ($uri->getPort() && !in_array($uri->getPort(), [80, 443])) {
                $baseUrl .= ":" . $uri->getPort();
            }

            if (!empty($user["foto"])) {
                $user["foto"] = $baseUrl . "/" . $user["foto"];
            }

            // Ambil data SDM
            $stmtSdm = $db->prepare("SELECT * FROM mr_sdm WHERE user_id = :user_id LIMIT 1");
            $stmtSdm->execute(["user_id" => $user["id"]]);
            $sdm = $stmtSdm->fetch(PDO::FETCH_ASSOC);

            if ($sdm) {
                if (!empty($sdm["foto"])) {
                    $sdm["foto"] = $baseUrl . "/" . $sdm["foto"];
                }
                $user["sdm"] = $sdm;
            } else {
                $user["sdm"] = null;
            }

            return $response->withJson([
                "status" => true,
                "message" => "User data retrieved successfully",
                "data" => ["user" => $user]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });



};