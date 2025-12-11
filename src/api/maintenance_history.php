<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/maintenance/history', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $ruangan_id = $params['ruangan_id'] ?? null;

        try {
            if (!$ruangan_id) {
                $response->getBody()->write(json_encode([
                    'status' => false,
                    'message' => 'Parameter ruangan_id wajib diisi'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // ============================
            // Fungsi helper hitung durasi
            // ============================
            function hitungDurasi($startDate, $endDate) {
                if (empty($startDate) || empty($endDate)) return '-';

                $start = new DateTime($startDate);
                $end = new DateTime($endDate);
                $diff = $start->diff($end);

                $durasiParts = [];

                if ($diff->d > 0) {
                    $durasiParts[] = $diff->d . ' hari';
                }

                $durasiParts[] = $diff->h . ' jam';

                if ($diff->i > 0 || ($diff->d == 0 && $diff->h == 0)) {
                    $durasiParts[] = $diff->i . ' menit';
                }

                return implode(' ', $durasiParts) ?: '-';
            }

            // ============================
            // 1) Ambil riwayat alat
            // ============================
            $sqlAlat = "
                SELECT m.id, m.alat_id, a.nama_alat, a.merek_model, 
                    m.tanggal_mulai_maintenance, m.tanggal_selesai_maintenance, 
                    m.teknisi, m.biaya, m.judul_maintenance, 
                    m.deskripsi, m.next_maintenance, m.created_at
                FROM mr_maintenance m
                JOIN mr_alat a ON m.alat_id = a.id
                WHERE a.ruangan_id = :ruangan_id
                AND m.jenis_maintenance = 3
            ";
            $stmt = $db->prepare($sqlAlat);
            $stmt->execute(['ruangan_id' => $ruangan_id]);
            $alatHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($alatHistory as &$item) {
                $item['durasi'] = hitungDurasi($item['tanggal_mulai_maintenance'], $item['tanggal_selesai_maintenance']);
                $item['type'] = 'alat';
            }

            // ============================
            // 2) Ambil riwayat software
            // ============================
            $sqlSoftware = "
                SELECT m.id, m.software_id, s.nama_software, s.jenis_software, s.versi_tahun,
                    m.tanggal_mulai_maintenance, m.tanggal_selesai_maintenance, 
                    m.teknisi, m.biaya, m.judul_maintenance, 
                    m.deskripsi, m.next_maintenance, m.created_at
                FROM mr_maintenance m
                JOIN mr_software s ON m.software_id = s.id
                WHERE s.ruangan_id = :ruangan_id
                AND m.jenis_maintenance = 3
            ";
            $stmt = $db->prepare($sqlSoftware);
            $stmt->execute(['ruangan_id' => $ruangan_id]);
            $softwareHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($softwareHistory as &$item) {
                $item['durasi'] = hitungDurasi($item['tanggal_mulai_maintenance'], $item['tanggal_selesai_maintenance']);
                $item['type'] = 'software';
            }

            // ============================
            // MERGE & SORT ASC BERDASARKAN judul_maintenance
            // ============================
            $history = array_merge($alatHistory, $softwareHistory);

            usort($history, function ($a, $b) {
                return strcmp(
                    strtoupper(trim($a['judul_maintenance'])),
                    strtoupper(trim($b['judul_maintenance']))
                );
            });

            // ============================
            // RESPONSE
            // ============================
            $response->getBody()->write(json_encode([
                'status' => true,
                'history' => $history
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
