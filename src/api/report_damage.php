<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/OneSignalHelper.php';
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/report/list', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $ruangan_id = $params['ruangan_id'] ?? null;
        $role = $params['role'] ?? null;
        $user_id = $params['user_id'] ?? null;

        if (!$ruangan_id) {
            return $response->withJson([
                'status' => false,
                'message' => 'Parameter ruangan_id wajib diisi'
            ], 400);
        }

        // Jika role 4 → wajib kirim user_id
        if ($role == '4' && empty($user_id)) {
            return $response->withJson([
                'status' => false,
                'message' => 'Parameter user_id wajib untuk role 4 (dosen)'
            ], 400);
        }

        // Base URL
        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . "://" . $uri->getHost();
        if ($uri->getPort()) {
            $baseUrl .= ":" . $uri->getPort();
        }

        try {

            function mapStatus($status) {
                return [
                    1 => 'Open',
                    2 => 'In Progress',
                    3 => 'Close',
                    4 => 'Cancelled'
                ][$status] ?? '-';
            }

            function mapPrioritas($prioritas) {
                return [
                    1 => 'Low',
                    2 => 'Medium',
                    3 => 'High',
                    4 => 'Critical'
                ][$prioritas] ?? '-';
            }

            // ============================
            // BUILD QUERY
            // ============================
            $sql = "
                SELECT 
                    l.*, 
                    a.nama_alat, 
                    s.nama_software,
                    sd.user_id,
                    u.full_name
                FROM mr_laporan_kerusakan l
                LEFT JOIN mr_alat a ON l.alat_id = a.id
                LEFT JOIN mr_software s ON l.software_id = s.id
                LEFT JOIN mr_sdm sd ON l.sdm_id = sd.id
                LEFT JOIN mr_users u ON sd.user_id = u.id
                WHERE (a.ruangan_id = :ruangan_id OR s.ruangan_id = :ruangan_id)
            ";

            $paramsExecute = [
                'ruangan_id' => $ruangan_id
            ];

            // Jika role = 4 → filter hanya laporan yg dibuat dosen itu
            if ($role == '4') {
                $sql .= " AND sd.user_id = :user_id";
                $paramsExecute['user_id'] = $user_id;
            }

            $sql .= " ORDER BY l.tanggal_laporan DESC";

            // Eksekusi
            $stmt = $db->prepare($sql);
            $stmt->execute($paramsExecute);
            $laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format
            foreach ($laporan as &$item) {
                $item['status_text'] = mapStatus((int)($item['status'] ?? 0));
                $item['prioritas_text'] = mapPrioritas((int)($item['prioritas'] ?? 0));

                $item['foto'] = !empty($item['foto'])
                    ? $baseUrl . "/uploads/damage_report/" . $item['foto']
                    : null;
            }

            return $response->withJson([
                'status' => true,
                'data' => $laporan
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });




    $app->get('/report/detail', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $id = $params['id'] ?? null;

        if (!$id) {
            return $response->withJson([
                'status' => false,
                'message' => 'Parameter id wajib diisi'
            ], 400);
        }

        // Base URL
        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . "://" . $uri->getHost();
        if ($uri->getPort()) {
            $baseUrl .= ":" . $uri->getPort();
        }

        try {
            $sql = "
                SELECT 
                    l.*, 
                    a.nama_alat,
                    s.nama_software,
                    sd.user_id,
                    u.full_name
                FROM mr_laporan_kerusakan l
                LEFT JOIN mr_alat a ON l.alat_id = a.id
                LEFT JOIN mr_software s ON l.software_id = s.id
                LEFT JOIN mr_sdm sd ON l.sdm_id = sd.id
                LEFT JOIN mr_users u ON sd.user_id = u.id
                WHERE l.id = :id
                LIMIT 1
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return $response->withJson([
                    'status' => false,
                    'message' => 'Laporan tidak ditemukan'
                ], 404);
            }

            // Foto
            $item['foto'] = !empty($item['foto'])
                ? $baseUrl . "/uploads/damage_report/" . $item['foto']
                : null;

            // Text mapping
            function mapStatus($status) {
                return [1=>"Open",2=>"In Progress",3=>"Close",4=>"Cancelled"][$status] ?? "-";
            }
            function mapPrior($p) {
                return [1=>"Low",2=>"Medium",3=>"High",4=>"Critical"][$p] ?? "-";
            }

            $item['status_text'] = mapStatus((int)$item['status']);
            $item['prioritas_text'] = mapPrior((int)$item['prioritas']);

            return $response->withJson([
                'status' => true,
                'data' => $item
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });


    $app->post('/report/add', function ($request, $response) {
        $db = $this->get('db_default');

        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        // ============================
        // Validasi field wajib
        // ============================
        $requiredFields = ['type', 'prioritas', 'tanggal', 'deskripsi', 'pelapor'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400)
                    ->write(json_encode([
                        'status' => false,
                        'message' => "Field {$field} wajib diisi"
                    ]));
            }
        }

        // ============================
        // Tentukan alat_id / software_id
        // ============================
        $alatId = null;
        $softwareId = null;

        if ($data['type'] === 'alat') {
            $alatId = $data['id'] ?? null;
        } elseif ($data['type'] === 'software') {
            $softwareId = $data['id'] ?? null;
        }

        // ============================
        // Upload Foto
        // ============================
        $fotoName = null;

        if (isset($files['foto']) && $files['foto']->getError() === UPLOAD_ERR_OK) {
            $uploadedFile = $files['foto'];
            $fotoName = uniqid() . "-" . $uploadedFile->getClientFilename();

            $uploadPath = dirname(__DIR__, 2) . '/uploads/damage_report/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $uploadedFile->moveTo($uploadPath . $fotoName);
        }

        try {
            // Generate ID unik
            $id = generateReportDamageId($db);

            // ============================
            // Insert laporan kerusakan
            // ============================
            $stmt = $db->prepare("
                INSERT INTO mr_laporan_kerusakan (
                    id, alat_id, software_id, sdm_id,
                    tanggal_laporan, deskripsi_kerusakan,
                    prioritas, status, foto, created_at
                ) VALUES (
                    :id, :alat_id, :software_id, :sdm_id,
                    :tanggal_laporan, :deskripsi_kerusakan,
                    :prioritas, 1, :foto, NOW()
                )
            ");

            $stmt->execute([
                'id' => $id,
                'alat_id' => $alatId,
                'software_id' => $softwareId,
                'sdm_id' => $data['pelapor'],
                'tanggal_laporan' => $data['tanggal'],
                'deskripsi_kerusakan' => $data['deskripsi'],
                'prioritas' => $data['prioritas'],
                'foto' => $fotoName
            ]);

            // ============================
            // Notifikasi ke admin
            // ============================
            $prioritasText = [
                '1' => 'Low',
                '2' => 'Medium',
                '3' => 'High',
                '4' => 'Critical'
            ];

            $prioritasReadable = $prioritasText[$data['prioritas']] ?? $data['prioritas'];

            $stmtNotify = $db->prepare("
                SELECT u.player_id
                FROM mr_sdm s
                JOIN mr_users u ON s.user_id = u.id
                WHERE u.role IN ('0','1','3')
                AND u.player_id IS NOT NULL
                AND s.id != :pelapor
            ");

            $stmtNotify->execute([
                'pelapor' => $data['pelapor']
            ]);

            $usersToNotify = $stmtNotify->fetchAll(PDO::FETCH_ASSOC);

            $message =
                "Laporan kerusakan baru dibuat.\n" .
                "Deskripsi: {$data['deskripsi']}\n" .
                "Prioritas: {$prioritasReadable}";

            foreach ($usersToNotify as $u) {
                OneSignalHelper::sendNotification(
                    $u['player_id'],
                    $message,
                    "Laporan Kerusakan"
                );
            }

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201)
                ->write(json_encode([
                    'status'  => true,
                    'message' => 'Report damage berhasil ditambahkan',
                    'id'      => $id
                ]));

        } catch (PDOException $e) {
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500)
                ->write(json_encode([
                    'status' => false,
                    'message' => $e->getMessage()
                ]));
        }
    });




    $app->post('/report/update-status', function ($request, $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        // ============================
        // Validasi input wajib
        // ============================
        $requiredFields = ['id', 'status'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400)
                    ->write(json_encode([
                        'status' => false,
                        'message' => "Field {$field} wajib diisi"
                    ]));
            }
        }

        $id = $data['id'];
        $status = (int)$data['status'];
        
        // updater_sdm_id sekarang OPSIONAL
        $updaterSdmId = $data['updater_sdm_id'] ?? null;

        // ============================
        // Validasi status hanya 1–4
        // ============================
        if (!in_array($status, [1, 2, 3, 4])) {
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400)
                ->write(json_encode([
                    'status' => false,
                    'message' => "Status tidak valid (1=Open, 2=In Progress, 3=Close, 4=Cancelled)"
                ]));
        }

        try {
            // ============================
            // Update status laporan
            // ============================
            $tanggalSelesai = ($status === 3) ? date('Y-m-d H:i:s') : null;

            $stmt = $db->prepare("
                UPDATE mr_laporan_kerusakan
                SET status = :status,
                    tanggal_selesai = :tanggal_selesai
                WHERE id = :id
            ");
            $stmt->execute([
                'status' => $status,
                'tanggal_selesai' => $tanggalSelesai,
                'id' => $id
            ]);

            // ============================
            // Ambil deskripsi laporan untuk notif
            // ============================
            $stmtReport = $db->prepare("SELECT deskripsi_kerusakan FROM mr_laporan_kerusakan WHERE id = :id");
            $stmtReport->execute(['id' => $id]);
            $report = $stmtReport->fetch(PDO::FETCH_ASSOC);
            $deskripsi = $report['deskripsi_kerusakan'] ?? '';

            // ============================
            // Build query notifikasi
            // ============================
            if ($updaterSdmId) {
                // Jika updater_sdm_id ADA → exclude user tsb
                $stmtNotify = $db->prepare("
                    SELECT u.id AS user_id, u.player_id, u.full_name
                    FROM mr_sdm s
                    JOIN mr_users u ON s.user_id = u.id
                    WHERE u.role IN ('0','1','3')
                    AND u.player_id IS NOT NULL
                    AND s.id != :updater_sdm_id
                ");
                $stmtNotify->execute(['updater_sdm_id' => $updaterSdmId]);
            } else {
                // Jika updater_sdm_id TIDAK ADA → kirim ke semua user role 0,1,3
                $stmtNotify = $db->query("
                    SELECT u.id AS user_id, u.player_id, u.full_name
                    FROM mr_sdm s
                    JOIN mr_users u ON s.user_id = u.id
                    WHERE u.role IN ('0','1','3')
                    AND u.player_id IS NOT NULL
                ");
            }

            $usersToNotify = $stmtNotify->fetchAll(PDO::FETCH_ASSOC);

            $statusText = [
                '1' => 'Open',
                '2' => 'In Progress',
                '3' => 'Close',
                '4' => 'Cancelled'
            ];
            
            $message = "Status laporan kerusakan telah diubah menjadi '{$statusText[$status]}'\nDeskripsi: {$deskripsi}";

            foreach ($usersToNotify as $u) {
                OneSignalHelper::sendNotification(
                    $u['player_id'],
                    $message,
                    "Update Laporan Kerusakan"
                );
            }

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200)
                ->write(json_encode([
                    'status' => true,
                    'message' => 'Status laporan berhasil diperbarui dan notifikasi terkirim',
                    'tanggal_selesai' => $tanggalSelesai
                ]));

        } catch (PDOException $e) {
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500)
                ->write(json_encode([
                    'status' => false,
                    'message' => $e->getMessage()
                ]));
        }
    });





    // $app->post('/report/add', function ($request, $response) {
    //         $db = $this->get('db_default');

    //         $data = $request->getParsedBody();
    //         $files = $request->getUploadedFiles();

    //         // ============================
    //         // Validasi field wajib
    //         // ============================
    //         $requiredFields = ['ruangan_id', 'type', 'prioritas', 'tanggal', 'deskripsi', 'pelapor'];
    //         foreach ($requiredFields as $field) {
    //             if (empty($data[$field])) {
    //                 return $response
    //                     ->withHeader('Content-Type', 'application/json')
    //                     ->withStatus(400)
    //                     ->write(json_encode(['status' => false, 'message' => "Field {$field} wajib diisi"]));
    //             }
    //         }

    //         // Tentukan alat_id atau software_id
    //         $alatId = null;
    //         $softwareId = null;
    //         if ($data['type'] === 'alat') {
    //             $alatId = $data['id'] ?? null;
    //         } elseif ($data['type'] === 'software') {
    //             $softwareId = $data['id'] ?? null;
    //         }

    //         // ============================
    //         // Upload foto
    //         // ============================
    //         $fotoName = null;
    //         if (isset($files['foto']) && $files['foto']->getError() === UPLOAD_ERR_OK) {
    //             $uploadedFile = $files['foto'];
    //             $fotoName = uniqid() . "-" . $uploadedFile->getClientFilename();
    //             $uploadPath = __DIR__ . "/../../public/uploads/damage_report/";

    //             if (!is_dir($uploadPath)) {
    //                 mkdir($uploadPath, 0777, true);
    //             }

    //             $uploadedFile->moveTo($uploadPath . $fotoName);
    //         }

    //         try {
    //             $id = generateReportDamageId($db); // buat helper generate ID baru

    //             $stmt = $db->prepare("
    //                 INSERT INTO mr_laporan_kerusakan (
    //                     id, alat_id, software_id, sdm_id, tanggal_laporan, deskripsi_kerusakan,
    //                     prioritas, status, foto, created_at
    //                 ) VALUES (
    //                     :id, :alat_id, :software_id, :sdm_id, :tanggal_laporan, :deskripsi_kerusakan,
    //                     :prioritas, 1, :foto, NOW()
    //                 )
    //             ");

    //             $stmt->execute([
    //                 'id' => $id,
    //                 'alat_id' => $alatId,
    //                 'software_id' => $softwareId,
    //                 'sdm_id' => $data['pelapor'], // ambil dari user
    //                 'tanggal_laporan' => $data['tanggal'],
    //                 'deskripsi_kerusakan' => $data['deskripsi'],
    //                 'prioritas' => $data['prioritas'], // 1=low,2=medium,3=high,4=critical
    //                 'foto' => $fotoName
    //             ]);

    //             return $response
    //                 ->withHeader('Content-Type', 'application/json')
    //                 ->withStatus(201)
    //                 ->write(json_encode([
    //                     'status' => true,
    //                     'message' => 'Report damage berhasil ditambahkan',
    //                     'id' => $id
    //                 ]));

    //         } catch (PDOException $e) {
    //             return $response
    //                 ->withHeader('Content-Type', 'application/json')
    //                 ->withStatus(500)
    //                 ->write(json_encode([
    //                     'status' => false,
    //                     'message' => $e->getMessage()
    //                 ]));
    //         }
    // });

    


    // $app->post('/report/update-status', function ($request, $response) {
    //     $db = $this->get('db_default');

    //     $data = $request->getParsedBody();

    //     // ============================
    //     // Validasi input wajib
    //     // ============================
    //     $requiredFields = ['id', 'status'];
    //     foreach ($requiredFields as $field) {
    //         if (empty($data[$field])) {
    //             return $response
    //                 ->withHeader('Content-Type', 'application/json')
    //                 ->withStatus(400)
    //                 ->write(json_encode([
    //                     'status' => false,
    //                     'message' => "Field {$field} wajib diisi"
    //                 ]));
    //         }
    //     }

    //     $id = $data['id'];
    //     $status = (int)$data['status'];

    //     // ============================
    //     // Validasi status hanya 1–4
    //     // ============================
    //     if (!in_array($status, [1, 2, 3, 4])) {
    //         return $response
    //             ->withHeader('Content-Type', 'application/json')
    //             ->withStatus(400)
    //             ->write(json_encode([
    //                 'status' => false,
    //                 'message' => "Status tidak valid (1=Open, 2=In Progress, 3=Close, 4=Cancelled)"
    //             ]));
    //     }

    //     try {
    //         // Jika status = 3 (Close) → set tanggal_selesai
    //         // Status lain → null
    //         $tanggalSelesai = ($status === 3) ? date('Y-m-d H:i:s') : null;

    //         $stmt = $db->prepare("
    //             UPDATE mr_laporan_kerusakan
    //             SET status = :status,
    //                 tanggal_selesai = :tanggal_selesai
    //             WHERE id = :id
    //         ");

    //         $stmt->execute([
    //             'status' => $status,
    //             'tanggal_selesai' => $tanggalSelesai,
    //             'id' => $id
    //         ]);

    //         return $response
    //             ->withHeader('Content-Type', 'application/json')
    //             ->withStatus(200)
    //             ->write(json_encode([
    //                 'status' => true,
    //                 'message' => 'Status laporan berhasil diperbarui',
    //                 'tanggal_selesai' => $tanggalSelesai
    //             ]));

    //     } catch (PDOException $e) {
    //         return $response
    //             ->withHeader('Content-Type', 'application/json')
    //             ->withStatus(500)
    //             ->write(json_encode([
    //                 'status' => false,
    //                 'message' => $e->getMessage()
    //             ]));
    //     }
    // });



};
