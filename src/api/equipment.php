<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/equipment/list', function ($request, $response) {
        $db = $this->get('db_default');

        // Ambil ruangan_id
        $params = $request->getQueryParams();
        $ruangan_id = $params['ruangan_id'] ?? null;

        if (!$ruangan_id) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Parameter ruangan_id diperlukan'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // 🔹 Ambil ALAT
            $stmtAlat = $db->prepare("
                SELECT 
                    id,
                    ruangan_id,
                    nama_alat AS name,
                    merek_model,
                    kuantitas,
                    tahun_pengadaan AS tahun,
                    status_alat,
                    kondisi,
                    sparepart_tersedia,
                    sparepart_list,
                    deskripsi,
                    foto,
                    created_at,
                    updated_at
                FROM mr_alat
                WHERE ruangan_id = :ruangan_id
                ORDER BY nama_alat ASC
            ");
            $stmtAlat->execute(['ruangan_id' => $ruangan_id]);
            $alatList = $stmtAlat->fetchAll(PDO::FETCH_ASSOC);

            // Tambahkan type ke setiap item alat
            foreach ($alatList as &$item) {
                $item['type'] = 'alat';
            }

            // 🔹 Ambil SOFTWARE
            $stmtSoftware = $db->prepare("
                SELECT
                    id,
                    ruangan_id,
                    nama_software AS name,
                    jenis_software,
                    versi_tahun AS tahun,
                    status_lisensi,
                    jenis_lisensi,
                    tanggal_aktif_lisensi,
                    tanggal_habis_lisensi,
                    jumlah_lisensi,
                    lokasi_penggunaan,
                    status_penggunaan,
                    keterangan_tambahan,
                    foto,
                    created_at,
                    updated_at
                FROM mr_software
                WHERE ruangan_id = :ruangan_id
                ORDER BY nama_software ASC
            ");
            $stmtSoftware->execute(['ruangan_id' => $ruangan_id]);
            $softwareList = $stmtSoftware->fetchAll(PDO::FETCH_ASSOC);

            // Tambahkan type ke setiap item software
            foreach ($softwareList as &$item) {
                $item['type'] = 'software';
            }

            // 🔹 Satukan (merge) alat + software
            $equipment = array_merge($alatList, $softwareList);

            // 🔹 Response final
            $response->getBody()->write(json_encode([
                'status' => true,
                'equipment' => $equipment
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


    $app->get('/maintenance/list', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $alat_id = $params['alat_id'] ?? null;

        try {

            // ============================
            // 1) AMBIL HISTORY (status = 3)
            // ============================
            $sqlHistory = "
                SELECT
                    id,
                    alat_id,
                    tanggal_mulai_maintenance,
                    tanggal_selesai_maintenance,
                    jenis_maintenance,
                    teknisi,
                    biaya,
                    judul_maintenance,
                    deskripsi,
                    next_maintenance,
                    created_at
                FROM mr_maintenance
                WHERE jenis_maintenance = 3
            ";

            $bindHistory = [];

            if ($alat_id) {
                $sqlHistory .= " AND alat_id = :alat_id";
                $bindHistory['alat_id'] = $alat_id;
            }

            $sqlHistory .= " ORDER BY tanggal_selesai_maintenance DESC";

            $stmt = $db->prepare($sqlHistory);
            $stmt->execute($bindHistory);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // ======================================
            // 2) AMBIL SCHEDULE (status 1 & 2)
            // ======================================
            $sqlSchedule = "
                SELECT
                    id,
                    alat_id,
                    tanggal_mulai_maintenance,
                    tanggal_selesai_maintenance,
                    jenis_maintenance,
                    teknisi,
                    biaya,
                    judul_maintenance,
                    deskripsi,
                    next_maintenance,
                    created_at
                FROM mr_maintenance
                WHERE jenis_maintenance IN (1, 2)
            ";

            $bindSchedule = [];

            if ($alat_id) {
                $sqlSchedule .= " AND alat_id = :alat_id";
                $bindSchedule['alat_id'] = $alat_id;
            }

            $sqlSchedule .= " ORDER BY tanggal_mulai_maintenance ASC";

            $stmt = $db->prepare($sqlSchedule);
            $stmt->execute($bindSchedule);
            $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);


            // ============================
            // RESPONSE
            // ============================
            $response->getBody()->write(json_encode([
                'status' => true,
                'history' => $history,
                'schedule' => $schedule
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
