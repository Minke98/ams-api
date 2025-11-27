<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/dashboard/summary', function ($request, $response) {
        $db = $this->get('db_default');

        try {
            // 1️⃣ Hitung Ruangan
            $stmt = $db->query("SELECT COUNT(*) AS total, 
                                    (SELECT COUNT(*) FROM mr_alat) AS total_alat,
                                    (SELECT COUNT(*) FROM mr_software) AS total_software
                                FROM mr_ruangan");
            $ruangan = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2️⃣ Hitung SDM
            $stmt = $db->query("SELECT COUNT(*) AS total,
                                    SUM(CASE WHEN status='1' THEN 1 ELSE 0 END) AS certified,
                                    SUM(CASE WHEN status!='1' THEN 1 ELSE 0 END) AS uncertified
                                FROM mr_sdm");
            $sdm = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3️⃣ Hitung Maintenance
            $stmt = $db->query("SELECT COUNT(*) AS total,
                                    SUM(CASE WHEN jenis_maintenance=1 THEN 1 ELSE 0 END) AS open,
                                    SUM(CASE WHEN jenis_maintenance=2 THEN 1 ELSE 0 END) AS in_progress,
                                    SUM(CASE WHEN jenis_maintenance=3 THEN 1 ELSE 0 END) AS solved
                                FROM mr_maintenance");
            $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4️⃣ Hitung Laporan Kerusakan total
            $stmt = $db->query("SELECT COUNT(*) AS total FROM mr_laporan_kerusakan");
            $laporan = $stmt->fetch(PDO::FETCH_ASSOC);

            // 5️⃣ Hitung Ruangan Dipinjam & Tidak Digunakan dari mr_penggunaan_ruangan
            $stmt = $db->query("SELECT 
                                    SUM(CASE WHEN status=1 THEN 1 ELSE 0 END) AS borrowed,
                                    SUM(CASE WHEN status=0 THEN 1 ELSE 0 END) AS not_used
                                FROM mr_penggunaan_ruangan");
            $ruangan_status = $stmt->fetch(PDO::FETCH_ASSOC);

            $data = [
                [
                    'title' => $ruangan['total'] . ' Ruangan',
                    'details' => [
                        $ruangan['total_alat'] . ' Alat',
                        $ruangan['total_software'] . ' Software',
                    ],
                ],
                [
                    'title' => $sdm['total'] . ' SDM',
                    'details' => [
                        $sdm['certified'] . ' Bersertifikat',
                        $sdm['uncertified'] . ' Tidak Bersertifikat',
                    ],
                ],
                [
                    'title' => $maintenance['total'] . ' Maintenance',
                    'details' => [
                        $maintenance['open'] . ' Open',
                        $maintenance['in_progress'] . ' On Progress',
                        $maintenance['solved'] . ' Solved',
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


};