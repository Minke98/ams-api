<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/report/weekly-stats', function ($request, $response) {
        $db = $this->get('db_default');

        try {
            // ===========================
            // PARAMETER TANGGAL
            // ===========================
            $params = $request->getQueryParams();

            $startDate = isset($params['start_date']) 
                ? new DateTime($params['start_date']) 
                : new DateTime('first day of this month');

            $endDate = isset($params['end_date']) 
                ? new DateTime($params['end_date']) 
                : new DateTime('last day of this month');

            $totalDays = (int)$startDate->diff($endDate)->days + 1;
            $totalWeeks = ceil($totalDays / 7);

            // ===========================
            // KERUSAKAN ALAT + SOFTWARE
            // ===========================
            $kerusakan = array_fill(1, $totalWeeks, ['total' => 0, 'detail' => []]);

            $stmt = $db->prepare("
                SELECT 
                    l.id,
                    l.alat_id,
                    l.software_id,
                    COALESCE(a.nama_alat, s.nama_software) AS nama_item,
                    COALESCE(a.merek_model, s.jenis_software) AS info_item,
                    l.tanggal_laporan,
                    l.deskripsi_kerusakan,
                    l.prioritas,
                    l.status,
                    FLOOR((DAY(l.tanggal_laporan) - DAY(:start_date)) / 7) + 1 AS minggu
                FROM mr_laporan_kerusakan l
                LEFT JOIN mr_alat a ON l.alat_id = a.id
                LEFT JOIN mr_software s ON l.software_id = s.id
                WHERE l.tanggal_laporan BETWEEN :start_date AND :end_date
                ORDER BY l.tanggal_laporan ASC
            ");

            $stmt->execute([
                ':start_date' => $startDate->format('Y-m-d'),
                ':end_date' => $endDate->format('Y-m-d')
            ]);

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $m = max(1, min((int)$row['minggu'], $totalWeeks));
                $kerusakan[$m]['total']++;
                $kerusakan[$m]['detail'][] = [
                    'id' => $row['id'],
                    'alat_id' => $row['alat_id'],
                    'software_id' => $row['software_id'],
                    'nama_item' => $row['nama_item'],
                    'info_item' => $row['info_item'],
                    'tanggal' => $row['tanggal_laporan'],
                    'deskripsi' => $row['deskripsi_kerusakan'],
                    'prioritas' => (int)$row['prioritas'],
                    'status' => (int)$row['status'],
                ];
            }

            // ===========================
            // PEMAKAIAN RUANGAN
            // ===========================
            $ruangan = array_fill(1, $totalWeeks, ['total' => 0, 'detail' => []]);

            $stmt = $db->prepare("
                SELECT 
                    p.id,
                    p.ruangan_id,
                    r.nama_ruangan,
                    p.kegiatan,
                    p.deskripsi,
                    p.tanggal_mulai,
                    p.tanggal_selesai,
                    FLOOR((DAY(p.tanggal_mulai) - DAY(:start_date)) / 7) + 1 AS minggu
                FROM mr_penggunaan_ruangan p
                LEFT JOIN mr_ruangan r ON p.ruangan_id = r.id
                WHERE p.tanggal_mulai BETWEEN :start_date AND :end_date
                ORDER BY p.tanggal_mulai ASC
            ");

            $stmt->execute([
                ':start_date' => $startDate->format('Y-m-d'),
                ':end_date' => $endDate->format('Y-m-d')
            ]);

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $m = max(1, min((int)$row['minggu'], $totalWeeks));
                $ruangan[$m]['total']++;
                $ruangan[$m]['detail'][] = [
                    'id' => $row['id'],
                    'ruangan_id' => $row['ruangan_id'],
                    'nama_ruangan' => $row['nama_ruangan'],
                    'kegiatan' => $row['kegiatan'],
                    'deskripsi' => $row['deskripsi'],
                    'tanggal_mulai' => $row['tanggal_mulai'],
                    'tanggal_selesai' => $row['tanggal_selesai']
                ];
            }

            // ===========================
            // SERTIFIKAT (JUMLAH + PERSEN + DETAIL)
            // ===========================
            $now = new DateTime();
            $active = 0;
            $willExpire = 0;
            $expired = 0;

            $detailActive = [];
            $detailWillExpire = [];
            $detailExpired = [];
            $allDetails = [];

            $stmt = $db->prepare("
                SELECT 
                    id,
                    sdm_id,
                    nama_sertifikat,
                    institusi,
                    no_sertifikat,
                    tanggal_terbit,
                    tanggal_expiry,
                    file_sertifikat,
                    status,
                    reminder_sent,
                    created_at
                FROM mr_sertifikasi
            ");
            $stmt->execute();

            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $exp = new DateTime($row['tanggal_expiry']);
                $status = (int)$row['status'];

                if ($status == 2 || $exp < $now) {
                    $expired++;
                    $row['status_type'] = 'expired';
                    $detailExpired[] = $row;
                    $allDetails[] = $row;
                } elseif ($exp <= (clone $now)->modify('+3 months')) {
                    $willExpire++;
                    $row['status_type'] = 'will_expire';
                    $detailWillExpire[] = $row;
                    $allDetails[] = $row;
                } else {
                    $active++;
                    $row['status_type'] = 'active';
                    $detailActive[] = $row;
                    $allDetails[] = $row;
                }
            }

            $totalSertifikat = $active + $willExpire + $expired;

            $result = [
                'kerusakan' => [],
                'ruangan' => [],
                'sertifikat' => [
                    'jumlah' => [
                        'aktif' => $active,
                        'akan_expired' => $willExpire,
                        'expired' => $expired
                    ],
                    'persen' => [
                        'aktif' => $totalSertifikat > 0 ? round($active / $totalSertifikat * 100, 1) : 0,
                        'akan_expired' => $totalSertifikat > 0 ? round($willExpire / $totalSertifikat * 100, 1) : 0,
                        'expired' => $totalSertifikat > 0 ? round($expired / $totalSertifikat * 100, 1) : 0
                    ],
                    'detail' => $allDetails
                ]
            ];

            for ($i = 1; $i <= $totalWeeks; $i++) {
                $result['kerusakan'][] = [
                    'minggu' => $i,
                    'total' => $kerusakan[$i]['total'],
                    'detail' => $kerusakan[$i]['detail']
                ];
                $result['ruangan'][] = [
                    'minggu' => $i,
                    'total' => $ruangan[$i]['total'],
                    'detail' => $ruangan[$i]['detail']
                ];
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
