<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/maintenance/schedule', function ($request, $response) {
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
            // 1) SCHEDULE ALAT
            // ============================
            $sqlAlat = "
                SELECT m.id, m.alat_id, a.nama_alat, a.merek_model, 
                    m.tanggal_mulai_maintenance, m.next_maintenance,
                    m.teknisi, m.judul_maintenance, m.deskripsi,
                    m.jenis_maintenance, m.created_at
                FROM mr_maintenance m
                JOIN mr_alat a ON m.alat_id = a.id
                WHERE a.ruangan_id = :ruangan_id
                AND m.jenis_maintenance IN (1, 2)
                ORDER BY m.next_maintenance ASC
            ";
            $stmt = $db->prepare($sqlAlat);
            $stmt->execute(['ruangan_id' => $ruangan_id]);
            $alatSchedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ============================
            // 2) SCHEDULE SOFTWARE
            // ============================
            $sqlSoftware = "
                SELECT m.id, m.software_id, s.nama_software, s.jenis_software, s.versi_tahun,
                    m.tanggal_mulai_maintenance, m.next_maintenance,
                    m.teknisi, m.judul_maintenance, m.deskripsi,
                    m.jenis_maintenance, m.created_at
                FROM mr_maintenance m
                JOIN mr_software s ON m.software_id = s.id
                WHERE s.ruangan_id = :ruangan_id
                AND m.jenis_maintenance IN (1, 2)
                ORDER BY m.next_maintenance ASC
            ";
            $stmt = $db->prepare($sqlSoftware);
            $stmt->execute(['ruangan_id' => $ruangan_id]);
            $softwareSchedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ============================
            // RESPONSE
            // ============================
            $response->getBody()->write(json_encode([
                'status' => true,
                'alat_schedule' => $alatSchedule,
                'software_schedule' => $softwareSchedule
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


    $app->put('/maintenance/schedule/update', function ($request, $response) {
        $db = $this->get('db_default');

        $data = $request->getParsedBody();
        $id = $data['id'] ?? null;
        $jenis_maintenance = $data['jenis_maintenance'] ?? null;
        $tanggal_selesai = $data['tanggal_selesai'] ?? null; // ← TAMBAHAN

        try {
            if (!$id || !$jenis_maintenance) {
                $response->getBody()->write(json_encode([
                    'status' => false,
                    'message' => 'Parameter id dan jenis_maintenance wajib diisi'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Jika selesai (jenis_maintenance = 3)
            if ($jenis_maintenance == 3) {

                // Jika frontend mengirim tanggal manual
                if ($tanggal_selesai) {
                    $sql = "
                        UPDATE mr_maintenance
                        SET jenis_maintenance = 3,
                            tanggal_selesai_maintenance = :tanggal_selesai
                        WHERE id = :id
                    ";
                } else {
                    // Default pakai NOW()
                    $sql = "
                        UPDATE mr_maintenance
                        SET jenis_maintenance = 3,
                            tanggal_selesai_maintenance = NOW()
                        WHERE id = :id
                    ";
                }

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'id' => $id,
                    'tanggal_selesai' => $tanggal_selesai
                ]);

            } else {
                // Update normal (1 atau 2)
                $sql = "
                    UPDATE mr_maintenance
                    SET jenis_maintenance = :jenis_maintenance
                    WHERE id = :id
                ";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'id' => $id,
                    'jenis_maintenance' => $jenis_maintenance
                ]);
            }

            $response->getBody()->write(json_encode([
                'status' => true,
                'message' => 'Status maintenance berhasil diupdate'
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


    $app->get('/equipment/list-by-room', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $ruanganId = $params['ruangan_id'] ?? null;

        if (!$ruanganId) {
            return $response->withJson([
                "status" => false,
                "message" => "ruangan_id is required"
            ]);
        }

        // AMBIL ALAT
        $alatStmt = $db->prepare("SELECT id, nama_alat AS nama FROM mr_alat WHERE ruangan_id = :id");
        $alatStmt->execute([':id' => $ruanganId]);
        $alat = $alatStmt->fetchAll(PDO::FETCH_ASSOC);

        // Add type
        $alat = array_map(function ($item) {
            $item['type'] = 'alat';
            return $item;
        }, $alat);

        // AMBIL SOFTWARE
        $softwareStmt = $db->prepare("SELECT id, nama_software AS nama FROM mr_software WHERE ruangan_id = :id");
        $softwareStmt->execute([':id' => $ruanganId]);
        $software = $softwareStmt->fetchAll(PDO::FETCH_ASSOC);

        // Add type
        $software = array_map(function ($item) {
            $item['type'] = 'software';
            return $item;
        }, $software);

        // Combine
        $result = array_merge($alat, $software);

        return $response->withJson([
            "status" => true,
            "data" => $result
        ]);
    });


    $app->post('/maintenance/schedule/add-multiple', function ($request, $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        $items = $data['items'] ?? [];        
        $judul = $data['judul_maintenance'] ?? null;
        $deskripsi = $data['deskripsi'] ?? null;
        $jenis = $data['jenis_maintenance'] ?? null;
        $mulai = $data['tanggal_mulai_maintenance'] ?? null;

        // ❗ FIELD BARU → opsional
        $selesai = $data['tanggal_selesai_maintenance'] ?? null;

        $teknisi = $data['teknisi'] ?? null;
        $biaya = $data['biaya'] ?? null;

        try {
            // Validasi items wajib
            if (!$items || !is_array($items)) {
                return $response->withJson([
                    'status' => false,
                    'message' => 'Array items wajib diisi'
                ], 400);
            }

            // Field wajib (selesai, teknisi, biaya → opsional)
            if (!$judul || !$deskripsi || !$jenis || !$mulai) {
                return $response->withJson([
                    'status' => false,
                    'message' => 'Field wajib belum lengkap'
                ], 400);
            }

            $inserted = [];

            foreach ($items as $item) {
                $id = generateMaintenanceId($db);

                $sql = "
                    INSERT INTO mr_maintenance
                    (id, alat_id, software_id, judul_maintenance, deskripsi, jenis_maintenance,
                    tanggal_mulai_maintenance, tanggal_selesai_maintenance, teknisi, biaya, created_at)
                    VALUES
                    (:id, :alat_id, :software_id, :judul, :deskripsi, :jenis,
                    :mulai, :selesai, :teknisi, :biaya, NOW())
                ";

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $id,
                    ':alat_id' => $item['type'] == 'alat' ? $item['id'] : null,
                    ':software_id' => $item['type'] == 'software' ? $item['id'] : null,
                    ':judul' => $judul,
                    ':deskripsi' => $deskripsi,
                    ':jenis' => $jenis,
                    ':mulai' => $mulai,
                    ':selesai' => $selesai ?: null,   // opsional
                    ':teknisi' => $teknisi ?: null,   // opsional
                    ':biaya' => $biaya ?: null        // opsional
                ]);

                $inserted[] = $id;
            }

            return $response->withJson([
                'status' => true,
                'message' => 'Maintenance schedule berhasil dibuat',
                'created_ids' => $inserted
            ]);
        } catch (PDOException $e) {
            return $response->withJson([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });


};
