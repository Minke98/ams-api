<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/equipment/list', function ($request, $response) {
        $db = $this->get('db_default');

        // Ambil ruangan_id
        $params = $request->getQueryParams();
        $ruangan_id = $params['ruangan_id'] ?? null;

        if (!$ruangan_id) {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400)
                ->write(json_encode([
                    'status' => false,
                    'message' => 'Parameter ruangan_id diperlukan'
                ]));
        }

        // 🔹 Dapatkan BASE URL dinamis
        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . "://" . $uri->getHost();
        if ($uri->getPort()) {
            $baseUrl .= ":" . $uri->getPort();
        }

        try {
            // ============================
            // 🔹 Ambil ALAT
            // ============================
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
                ORDER BY UPPER(TRIM(nama_alat)) ASC
            ");
            $stmtAlat->execute(['ruangan_id' => $ruangan_id]);
            $alatList = $stmtAlat->fetchAll(PDO::FETCH_ASSOC);

            foreach ($alatList as &$item) {
                $item['type'] = 'alat';
                $item['foto'] = $item['foto'] 
                    ? $baseUrl . "/uploads/alat/" . $item['foto']
                    : null;
            }

            // ============================
            // 🔹 Ambil SOFTWARE
            // ============================
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

            foreach ($softwareList as &$item) {
                $item['type'] = 'software';
                $item['foto'] = $item['foto'] 
                    ? $baseUrl . "/uploads/software/" . $item['foto']
                    : null;
            }

            // Merge alat + software
            $equipment = array_merge($alatList, $softwareList);

            // Sort final ASC berdasarkan name
            usort($equipment, function ($a, $b) {
                return strcmp(strtoupper(trim($a['name'])), strtoupper(trim($b['name'])));
            });


            // Output final
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200)
                ->write(json_encode([
                    'status' => true,
                    'equipment' => $equipment
                ]));

        } catch (PDOException $e) {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(500)
                ->write(json_encode([
                    'status' => false,
                    'message' => $e->getMessage()
                ]));
        }
    });

    
    $app->get('/equipment/detail', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $id = $params['id'] ?? null;
        $type = $params['type'] ?? null;

        if (!$id || !$type) {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400)
                ->write(json_encode([
                    'status' => false,
                    'message' => 'Parameter id dan type diperlukan'
                ]));
        }

        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . "://" . $uri->getHost();
        if ($uri->getPort()) $baseUrl .= ":" . $uri->getPort();

        try {
            if ($type === "alat") {
                $stmt = $db->prepare("
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
                    WHERE id = :id
                ");
            } else {
                $stmt = $db->prepare("
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
                    WHERE id = :id
                ");
            }

            $stmt->execute(['id' => $id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(404)
                    ->write(json_encode([
                        'status' => false,
                        'message' => 'Data tidak ditemukan'
                    ]));
            }

            $item['type'] = $type;
            $item['foto'] = $item['foto']
                ? $baseUrl . "/uploads/{$type}/" . $item['foto']
                : null;

            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200)
                ->write(json_encode([
                    'status' => true,
                    'data' => $item
                ]));

        } catch (PDOException $e) {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(500)
                ->write(json_encode([
                    'status' => false,
                    'message' => $e->getMessage()
                ]));
        }
    });



    $app->post('/equipment/add-alat', function ($request, $response) {
        $db = $this->get('db_default');

        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $requiredFields = ['ruangan_id', 'nama_alat', 'merek_model', 'kuantitas', 'tahun_pengadaan', 'status_alat', 'kondisi'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $response->withHeader('Content-Type', 'application/json')
                                ->withStatus(400)
                                ->write(json_encode(['status'=>false,'message'=>"Field {$field} wajib diisi"]));
            }
        }

        // Upload Foto
        $fotoName = null;
        if (isset($files['foto']) && $files['foto']->getError() === UPLOAD_ERR_OK) {
            $uploadedFile = $files['foto'];

            $fotoName = uniqid() . "-" . $uploadedFile->getClientFilename();
            $uploadPath = __DIR__ . "/../../public/uploads/alat/";

            // 👉 Pastikan foldernya ada
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            // 👉 moveTo harus lengkap sampai nama file
            $uploadedFile->moveTo($uploadPath . $fotoName);
        }

        try {
            $id = generateAlatId($db);

            $stmt = $db->prepare("
                INSERT INTO mr_alat (
                    id, ruangan_id, nama_alat, merek_model, kuantitas, tahun_pengadaan,
                    status_alat, kondisi, sparepart_tersedia, sparepart_list,
                    deskripsi, foto, created_at, updated_at
                ) VALUES (
                    :id, :ruangan_id, :nama_alat, :merek_model, :kuantitas, :tahun_pengadaan,
                    :status_alat, :kondisi, :sparepart_tersedia, :sparepart_list,
                    :deskripsi, :foto, NOW(), NOW()
                )
            ");

            $stmt->execute([
                'id' => $id,
                'ruangan_id' => $data['ruangan_id'],
                'nama_alat' => $data['nama_alat'],
                'merek_model' => $data['merek_model'],
                'kuantitas' => $data['kuantitas'],
                'tahun_pengadaan' => $data['tahun_pengadaan'],
                'status_alat' => $data['status_alat'],
                'kondisi' => $data['kondisi'],
                'sparepart_tersedia' => $data['sparepart_tersedia'] ?? null,
                'sparepart_list' => $data['sparepart_list'] ?? null,
                'deskripsi' => $data['deskripsi'] ?? null,
                'foto' => $fotoName
            ]);

            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(201)
                            ->write(json_encode(['status'=>true,'message'=>'Alat berhasil ditambahkan','id'=>$id]));

        } catch (PDOException $e) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(500)
                            ->write(json_encode(['status'=>false,'message'=>$e->getMessage()]));
        }
    });


    $app->post('/equipment/add-software', function ($request, $response) {
        $db = $this->get('db_default');

        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        $requiredFields = ['ruangan_id', 'nama_software', 'jenis_software', 'versi_tahun', 'status_lisensi'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $response->withHeader('Content-Type', 'application/json')
                                ->withStatus(400)
                                ->write(json_encode(['status'=>false,'message'=>"Field {$field} wajib diisi"]));
            }
        }

        // Upload Foto
        $fotoName = null;
        if (isset($files['foto']) && $files['foto']->getError() === UPLOAD_ERR_OK) {
            $uploadedFile = $files['foto'];

            $fotoName = uniqid() . "-" . $uploadedFile->getClientFilename();
            $uploadPath = __DIR__ . "/../../public/uploads/software/";

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $uploadedFile->moveTo($uploadPath . $fotoName);
        }

        try {
            $id = generateSoftwareId($db);

            $stmt = $db->prepare("
                INSERT INTO mr_software (
                    id, ruangan_id, nama_software, jenis_software, versi_tahun, status_lisensi, jenis_lisensi,
                    tanggal_aktif_lisensi, tanggal_habis_lisensi, jumlah_lisensi,
                    lokasi_penggunaan, status_penggunaan, keterangan_tambahan, foto,
                    created_at, updated_at
                ) VALUES (
                    :id, :ruangan_id, :nama_software, :jenis_software, :versi_tahun, :status_lisensi, :jenis_lisensi,
                    :tanggal_aktif_lisensi, :tanggal_habis_lisensi, :jumlah_lisensi,
                    :lokasi_penggunaan, :status_penggunaan, :keterangan_tambahan, :foto,
                    NOW(), NOW()
                )
            ");

            $stmt->execute([
                'id' => $id,
                'ruangan_id' => $data['ruangan_id'],
                'nama_software' => $data['nama_software'],
                'jenis_software' => $data['jenis_software'],
                'versi_tahun' => $data['versi_tahun'],
                'status_lisensi' => $data['status_lisensi'],
                'jenis_lisensi' => $data['jenis_lisensi'] ?? null,
                'tanggal_aktif_lisensi' => $data['tanggal_aktif_lisensi'] ?? null,
                'tanggal_habis_lisensi' => $data['tanggal_habis_lisensi'] ?? null,
                'jumlah_lisensi' => $data['jumlah_lisensi'] ?? null,
                'lokasi_penggunaan' => $data['lokasi_penggunaan'] ?? null,
                'status_penggunaan' => $data['status_penggunaan'] ?? null,
                'keterangan_tambahan' => $data['keterangan_tambahan'] ?? null,
                'foto' => $fotoName
            ]);

            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(201)
                            ->write(json_encode(['status'=>true,'message'=>'Software berhasil ditambahkan','id'=>$id]));

        } catch (PDOException $e) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(500)
                            ->write(json_encode(['status'=>false,'message'=>$e->getMessage()]));
        }
    });


    $app->post('/equipment/update-alat', function ($request, $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        // ID wajib ada
        if (empty($data['id'])) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(400)
                            ->write(json_encode(['status'=>false,'message'=>"ID alat wajib diisi"]));
        }

        // Ambil data lama (untuk foto lama)
        $stmtOld = $db->prepare("SELECT foto FROM mr_alat WHERE id = :id");
        $stmtOld->execute(['id' => $data['id']]);
        $old = $stmtOld->fetch();

        if (!$old) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(404)
                            ->write(json_encode(['status'=>false,'message'=>"Alat tidak ditemukan"]));
        }

        $fotoName = $old['foto']; // default: pakai foto lama

        // Jika ada foto baru
        if (isset($files['foto']) && $files['foto']->getError() === UPLOAD_ERR_OK) {
            $uploadedFile = $files['foto'];
            $fotoName = uniqid() . "-" . $uploadedFile->getClientFilename();

            $uploadPath = __DIR__ . "/../../public/uploads/alat/";
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

            $uploadedFile->moveTo($uploadPath . $fotoName);
        }

        try {
            $stmt = $db->prepare("
                UPDATE mr_alat SET
                    ruangan_id = :ruangan_id,
                    nama_alat = :nama_alat,
                    merek_model = :merek_model,
                    kuantitas = :kuantitas,
                    tahun_pengadaan = :tahun_pengadaan,
                    status_alat = :status_alat,
                    kondisi = :kondisi,
                    sparepart_tersedia = :sparepart_tersedia,
                    sparepart_list = :sparepart_list,
                    deskripsi = :deskripsi,
                    foto = :foto,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $data['id'],
                'ruangan_id' => $data['ruangan_id'],
                'nama_alat' => $data['nama_alat'],
                'merek_model' => $data['merek_model'],
                'kuantitas' => $data['kuantitas'],
                'tahun_pengadaan' => $data['tahun_pengadaan'],
                'status_alat' => $data['status_alat'],
                'kondisi' => $data['kondisi'],
                'sparepart_tersedia' => $data['sparepart_tersedia'] ?? null,
                'sparepart_list' => $data['sparepart_list'] ?? null,
                'deskripsi' => $data['deskripsi'] ?? null,
                'foto' => $fotoName
            ]);

            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200)
                            ->write(json_encode(['status'=>true,'message'=>'Alat berhasil diupdate']));

        } catch (PDOException $e) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(500)
                            ->write(json_encode(['status'=>false,'message'=>$e->getMessage()]));
        }
    });


    $app->post('/equipment/update-software', function ($request, $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        // ID wajib
        if (empty($data['id'])) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(400)
                            ->write(json_encode(['status'=>false,'message'=>"ID software wajib diisi"]));
        }

        // Ambil data lama
        $stmtOld = $db->prepare("SELECT foto FROM mr_software WHERE id = :id");
        $stmtOld->execute(['id' => $data['id']]);
        $old = $stmtOld->fetch();

        if (!$old) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(404)
                            ->write(json_encode(['status'=>false,'message'=>"Software tidak ditemukan"]));
        }

        $fotoName = $old['foto'];

        // Upload foto baru jika ada
        if (isset($files['foto']) && $files['foto']->getError() === UPLOAD_ERR_OK) {
            $uploadedFile = $files['foto'];
            $fotoName = uniqid() . "-" . $uploadedFile->getClientFilename();

            $uploadPath = __DIR__ . "/../../public/uploads/software/";
            if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);

            $uploadedFile->moveTo($uploadPath . $fotoName);
        }

        try {
            $stmt = $db->prepare("
                UPDATE mr_software SET
                    ruangan_id = :ruangan_id,
                    nama_software = :nama_software,
                    jenis_software = :jenis_software,
                    versi_tahun = :versi_tahun,
                    status_lisensi = :status_lisensi,
                    jenis_lisensi = :jenis_lisensi,
                    tanggal_aktif_lisensi = :tanggal_aktif_lisensi,
                    tanggal_habis_lisensi = :tanggal_habis_lisensi,
                    jumlah_lisensi = :jumlah_lisensi,
                    lokasi_penggunaan = :lokasi_penggunaan,
                    status_penggunaan = :status_penggunaan,
                    keterangan_tambahan = :keterangan_tambahan,
                    foto = :foto,
                    updated_at = NOW()
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $data['id'],
                'ruangan_id' => $data['ruangan_id'],
                'nama_software' => $data['nama_software'],
                'jenis_software' => $data['jenis_software'],
                'versi_tahun' => $data['versi_tahun'],
                'status_lisensi' => $data['status_lisensi'],
                'jenis_lisensi' => $data['jenis_lisensi'] ?? null,
                'tanggal_aktif_lisensi' => $data['tanggal_aktif_lisensi'] ?? null,
                'tanggal_habis_lisensi' => $data['tanggal_habis_lisensi'] ?? null,
                'jumlah_lisensi' => $data['jumlah_lisensi'] ?? null,
                'lokasi_penggunaan' => $data['lokasi_penggunaan'] ?? null,
                'status_penggunaan' => $data['status_penggunaan'] ?? null,
                'keterangan_tambahan' => $data['keterangan_tambahan'] ?? null,
                'foto' => $fotoName
            ]);

            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200)
                            ->write(json_encode(['status'=>true,'message'=>'Software berhasil diupdate']));

        } catch (PDOException $e) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(500)
                            ->write(json_encode(['status'=>false,'message'=>$e->getMessage()]));
        }
    });

    $app->delete('/equipment/delete', function ($request, $response) {
        $db = $this->get('db_default');

        // Ambil query param
        $params = $request->getQueryParams();
        $type = $params['type'] ?? null;
        $id   = $params['id'] ?? null;

        // Validasi
        if (!$type || !$id) {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400)
                ->write(json_encode([
                    'status' => false,
                    'message' => 'Parameter type dan id diperlukan'
                ]));
        }

        // Validasi type
        if (!in_array($type, ['alat', 'software'])) {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(400)
                ->write(json_encode([
                    'status' => false,
                    'message' => 'Type harus alat atau software'
                ]));
        }

        // Tentukan tabel & folder
        $table  = $type === 'alat' ? 'mr_alat' : 'mr_software';
        $folder = $type === 'alat' ? 'uploads/alat/' : 'uploads/software/';

        try {
            // 1️⃣ Ambil data lama (untuk hapus foto)
            $stmtCheck = $db->prepare("SELECT foto FROM $table WHERE id = :id LIMIT 1");
            $stmtCheck->execute(['id' => $id]);
            $item = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(404)
                    ->write(json_encode([
                        'status' => false,
                        'message' => 'Data tidak ditemukan'
                    ]));
            }

            // 2️⃣ Hapus foto fisik jika ada
            if (!empty($item['foto'])) {
                $filePath = __DIR__ . '/../' . $folder . $item['foto'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // 3️⃣ Hapus data dari database
            $stmtDelete = $db->prepare("DELETE FROM $table WHERE id = :id");
            $stmtDelete->execute(['id' => $id]);

            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(200)
                ->write(json_encode([
                    'status' => true,
                    'message' => ucfirst($type) . ' berhasil dihapus'
                ]));

        } catch (PDOException $e) {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(500)
                ->write(json_encode([
                    'status' => false,
                    'message' => $e->getMessage()
                ]));
        }
    });


    $app->get('/maintenance/list', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $alat_id = $params['alat_id'] ?? null;
        $software_id = $params['software_id'] ?? null;

        try {
            // ============================
            // 1) AMBIL HISTORY (jenis_maintenance = 3)
            // ============================
            $sqlHistory = "
                SELECT
                    id,
                    alat_id,
                    software_id,
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
            } elseif ($software_id) {
                $sqlHistory .= " AND software_id = :software_id";
                $bindHistory['software_id'] = $software_id;
            }

            $sqlHistory .= " ORDER BY tanggal_selesai_maintenance DESC";
            $stmt = $db->prepare($sqlHistory);
            $stmt->execute($bindHistory);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // ============================
            // 2) AMBIL SCHEDULE (jenis_maintenance = 1,2)
            // ============================
            $sqlSchedule = "
                SELECT
                    id,
                    alat_id,
                    software_id,
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
                WHERE jenis_maintenance IN (1,2)
            ";

            $bindSchedule = [];
            if ($alat_id) {
                $sqlSchedule .= " AND alat_id = :alat_id";
                $bindSchedule['alat_id'] = $alat_id;
            } elseif ($software_id) {
                $sqlSchedule .= " AND software_id = :software_id";
                $bindSchedule['software_id'] = $software_id;
            }

            $sqlSchedule .= " ORDER BY tanggal_mulai_maintenance ASC";
            $stmt = $db->prepare($sqlSchedule);
            $stmt->execute($bindSchedule);
            $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

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



    $app->post('/maintenance/add', function ($request, $response) {
        $db = $this->get('db_default');

        // Ambil data body (JSON / FormData)
        $data = $request->getParsedBody();

        $alat_id = $data['alat_id'] ?? null;
        $software_id = $data['software_id'] ?? null;
        $tanggal_mulai = $data['tanggal_mulai_maintenance'] ?? null;
        $tanggal_selesai = isset($data['tanggal_selesai_maintenance']) && $data['tanggal_selesai_maintenance'] !== ''
            ? $data['tanggal_selesai_maintenance']
            : null;
        $jenis = $data['jenis_maintenance'] ?? null; 
        $biaya = isset($data['biaya']) && $data['biaya'] !== '' ? $data['biaya'] : null;
        $teknisi = isset($data['teknisi']) && $data['teknisi'] !== '' ? $data['teknisi'] : null;
        $judul = $data['judul_maintenance'] ?? null;
        $deskripsi = $data['deskripsi'] ?? null;
        $next_maintenance = $data['next_maintenance'] ?? null;

        // VALIDASI WAJIB
        if ((!$alat_id && !$software_id) || !$tanggal_mulai || !$jenis || !$judul || !$deskripsi) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'alat_id / software_id, tanggal_mulai_maintenance, jenis_maintenance, judul_maintenance, dan deskripsi wajib diisi.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $maintenance_id = generateMaintenanceId($db);

        try {
            $sql = "
                INSERT INTO mr_maintenance (
                    id,
                    alat_id,
                    software_id,
                    tanggal_mulai_maintenance,
                    tanggal_selesai_maintenance,
                    jenis_maintenance,
                    teknisi,
                    biaya,
                    judul_maintenance,
                    deskripsi,
                    next_maintenance,
                    created_at
                )
                VALUES (
                    :id,
                    :alat_id,
                    :software_id,
                    :tanggal_mulai,
                    :tanggal_selesai,
                    :jenis,
                    :teknisi,
                    :biaya,
                    :judul,
                    :deskripsi,
                    :next_maintenance,
                    NOW()
                )
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $maintenance_id,
                'alat_id' => $alat_id,
                'software_id' => $software_id,
                'tanggal_mulai' => $tanggal_mulai,
                'tanggal_selesai' => $tanggal_selesai, // bisa null
                'jenis' => $jenis,
                'teknisi' => $teknisi,
                'biaya' => $biaya,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'next_maintenance' => $next_maintenance
            ]);

            $response->getBody()->write(json_encode([
                'status' => true,
                'message' => 'Jadwal maintenance berhasil ditambahkan',
                'id' => $maintenance_id,
                'insert_id' => $db->lastInsertId()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });


};
