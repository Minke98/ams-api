<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/report/list', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $ruangan_id = $params['ruangan_id'] ?? null;

        if (!$ruangan_id) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Parameter ruangan_id wajib diisi'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // 🔹 Dapatkan BASE URL dinamis
        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . "://" . $uri->getHost();
        if ($uri->getPort()) {
            $baseUrl .= ":" . $uri->getPort();
        }

        try {
            // ============================
            // Fungsi helper format status & prioritas
            // ============================
            function mapStatus($status) {
                switch ($status) {
                    case 1: return 'Open';
                    case 2: return 'In Progress';
                    case 3: return 'Resolved';
                    case 4: return 'Cancelled';
                    default: return '-';
                }
            }

            function mapPrioritas($prioritas) {
                switch ($prioritas) {
                    case 1: return 'Low';
                    case 2: return 'Medium';
                    case 3: return 'High';
                    case 4: return 'Critical';
                    default: return '-';
                }
            }

            // ============================
            // QUERY LAPORAN
            // ============================
            $sql = "
                SELECT 
                    l.*, 
                    a.nama_alat, 
                    s.nama_software,
                    l.sdm_id,
                    sd.user_id,
                    u.full_name
                FROM mr_laporan_kerusakan l
                LEFT JOIN mr_alat a ON l.alat_id = a.id
                LEFT JOIN mr_software s ON l.software_id = s.id
                LEFT JOIN mr_sdm sd ON l.sdm_id = sd.id
                LEFT JOIN mr_users u ON sd.user_id = u.id
                WHERE (a.ruangan_id = :ruangan_id OR s.ruangan_id = :ruangan_id)
                ORDER BY l.tanggal_laporan DESC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute(['ruangan_id' => $ruangan_id]);
            $laporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Map status, prioritas & foto
            foreach ($laporan as &$item) {
                $item['status_text'] = mapStatus((int)($item['status'] ?? 0));
                $item['prioritas_text'] = mapPrioritas((int)($item['prioritas'] ?? 0));

                // 🔹 Update foto jadi URL lengkap
                $item['foto'] = !empty($item['foto'])
                    ? $baseUrl . "/uploads/damage_report/" . $item['foto']
                    : null;
            }

            // ============================
            // RESPONSE
            // ============================
            $response->getBody()->write(json_encode([
                'status' => true,
                'data' => $laporan
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




    $app->post('/report/add', function ($request, $response) {
            $db = $this->get('db_default');

            $data = $request->getParsedBody();
            $files = $request->getUploadedFiles();

            // ============================
            // Validasi field wajib
            // ============================
            $requiredFields = ['ruangan_id', 'type', 'prioritas', 'tanggal', 'deskripsi', 'pelapor'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(400)
                        ->write(json_encode(['status' => false, 'message' => "Field {$field} wajib diisi"]));
                }
            }

            // Tentukan alat_id atau software_id
            $alatId = null;
            $softwareId = null;
            if ($data['type'] === 'alat') {
                $alatId = $data['id'] ?? null;
            } elseif ($data['type'] === 'software') {
                $softwareId = $data['id'] ?? null;
            }

            // ============================
            // Upload foto
            // ============================
            $fotoName = null;
            if (isset($files['foto']) && $files['foto']->getError() === UPLOAD_ERR_OK) {
                $uploadedFile = $files['foto'];
                $fotoName = uniqid() . "-" . $uploadedFile->getClientFilename();
                $uploadPath = __DIR__ . "/../../public/uploads/damage_report/";

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                $uploadedFile->moveTo($uploadPath . $fotoName);
            }

            try {
                $id = generateReportDamageId($db); // buat helper generate ID baru

                $stmt = $db->prepare("
                    INSERT INTO mr_laporan_kerusakan (
                        id, alat_id, software_id, sdm_id, tanggal_laporan, deskripsi_kerusakan,
                        prioritas, status, foto, created_at
                    ) VALUES (
                        :id, :alat_id, :software_id, :sdm_id, :tanggal_laporan, :deskripsi_kerusakan,
                        :prioritas, 1, :foto, NOW()
                    )
                ");

                $stmt->execute([
                    'id' => $id,
                    'alat_id' => $alatId,
                    'software_id' => $softwareId,
                    'sdm_id' => $data['pelapor'], // ambil dari user
                    'tanggal_laporan' => $data['tanggal'],
                    'deskripsi_kerusakan' => $data['deskripsi'],
                    'prioritas' => $data['prioritas'], // 1=low,2=medium,3=high,4=critical
                    'foto' => $fotoName
                ]);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(201)
                    ->write(json_encode([
                        'status' => true,
                        'message' => 'Report damage berhasil ditambahkan',
                        'id' => $id
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


};
