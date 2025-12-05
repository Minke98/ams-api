<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/report/weekly-stats', function ($request, $response) {
        $db = $this->get('db_default');

        try {
            $params = $request->getQueryParams();
            $startDate = isset($params['start_date']) ? new DateTime($params['start_date']) : new DateTime('first day of this month');
            $endDate = isset($params['end_date']) ? new DateTime($params['end_date']) : new DateTime('last day of this month');

            // Total hari & minggu
            $totalDays = (int)$startDate->diff($endDate)->days + 1;
            $totalWeeks = ceil($totalDays / 7);

            // =========================
            // Grafik Kerusakan Alat
            // =========================
            $kerusakanWeeks = array_fill(1, $totalWeeks, 0);
            $stmt = $db->prepare("
                SELECT FLOOR((DAY(tanggal_laporan)-DAY(:start_date))/7)+1 AS minggu, COUNT(*) AS total
                FROM mr_laporan_kerusakan
                WHERE tanggal_laporan BETWEEN :start_date AND :end_date
                GROUP BY minggu
            ");
            $stmt->execute([
                ':start_date' => $startDate->format('Y-m-d'),
                ':end_date' => $endDate->format('Y-m-d')
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $row) {
                $kerusakanWeeks[$row['minggu']] = (int)$row['total'];
            }

            // =========================
            // Grafik Pemakaian Ruangan
            // =========================
            $ruanganWeeks = array_fill(1, $totalWeeks, 0);
            $stmt = $db->prepare("
                SELECT FLOOR((DAY(tanggal_mulai)-DAY(:start_date))/7)+1 AS minggu, COUNT(*) AS total
                FROM mr_penggunaan_ruangan
                WHERE tanggal_mulai BETWEEN :start_date AND :end_date
                GROUP BY minggu
            ");
            $stmt->execute([
                ':start_date' => $startDate->format('Y-m-d'),
                ':end_date' => $endDate->format('Y-m-d')
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($data as $row) {
                $ruanganWeeks[$row['minggu']] = (int)$row['total'];
            }

            // =========================
            // Sertifikat
            // =========================
            $now = new DateTime();
            $active = 0;
            $willExpire = 0;
            $expired = 0;

            $stmt = $db->prepare("SELECT tanggal_expiry, status FROM mr_sertifikasi");
            $stmt->execute();
            $sertifikatData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($sertifikatData as $row) {
                $expiry = new DateTime($row['tanggal_expiry']);
                $status = (int)$row['status'];

                if ($status == 2 || $expiry < $now) {
                    $expired++;
                } elseif ($expiry >= $now && $expiry <= (clone $now)->modify('+3 months')) {
                    $willExpire++;
                } else {
                    $active++;
                }
            }

            $totalSertifikat = $active + $willExpire + $expired;
            $sertifikatPercent = $totalSertifikat > 0 ? [
                'aktif' => round($active / $totalSertifikat * 100, 1),
                'akan_expired' => round($willExpire / $totalSertifikat * 100, 1),
                'expired' => round($expired / $totalSertifikat * 100, 1)
            ] : ['aktif' => 0, 'akan_expired' => 0, 'expired' => 0];

            // Format output per minggu
            $result = [
                'kerusakan' => [],
                'ruangan' => [],
                'sertifikat' => [
                    'jumlah' => [
                        'aktif' => $active,
                        'akan_expired' => $willExpire,
                        'expired' => $expired
                    ],
                    'persen' => $sertifikatPercent
                ]
            ];

            for ($i = 1; $i <= $totalWeeks; $i++) {
                $result['kerusakan'][] = ['minggu' => $i, 'total' => $kerusakanWeeks[$i]];
                $result['ruangan'][] = ['minggu' => $i, 'total' => $ruanganWeeks[$i]];
            }

            return $response->withJson([
                'status' => true,
                'data' => $result
            ]);

        } catch (Exception $e) {
            return $response->withStatus(500)->withJson([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    });


};