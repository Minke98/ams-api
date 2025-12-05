<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/room/list', function ($request, $response) {
        $db = $this->get('db_default');

        // Base URL dinamis (otomatis dari domain/server)
        $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();

        // Jika memakai port custom, ikutkan port
        if ($request->getUri()->getPort()) {
            $baseUrl .= ':' . $request->getUri()->getPort();
        }

        // Ambil prodi_id dari query param
        $params = $request->getQueryParams();
        $prodi_id = $params['prodi_id'] ?? null;

        if (!$prodi_id) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Parameter prodi_id diperlukan'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Ambil daftar ruangan tanpa duplikasi
            $stmt = $db->prepare("
                SELECT 
                    r.id,
                    r.prodi_id,
                    p.nama_prodi,
                    r.kode_ruangan,
                    r.nama_ruangan,
                    r.kapasitas,
                    r.deskripsi,
                    r.foto,
                    r.created_at,
                    r.updated_at,
                    IFNULL((
                        SELECT MAX(peng.status) 
                        FROM mr_penggunaan_ruangan peng 
                        WHERE peng.ruangan_id = r.id
                    ), 0) AS status_penggunaan
                FROM mr_ruangan r
                LEFT JOIN mr_prodi p ON r.prodi_id = p.id
                WHERE r.prodi_id = :prodi_id
                ORDER BY r.nama_ruangan ASC
            ");

            $stmt->execute(['prodi_id' => $prodi_id]);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tambahkan URL lengkap untuk foto
            foreach ($rooms as &$room) {
                if (!empty($room['foto'])) {
                    $room['foto'] = $baseUrl . '/uploads/rooms/' . $room['foto'];
                } else {
                    $room['foto'] = null;
                }
            }

            $response->getBody()->write(json_encode([
                'status' => true,
                'data' => $rooms
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



    $app->get('/room/detail', function ($request, $response) {
        $db = $this->get('db_default');

        // Base URL dinamis
        $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
        if ($request->getUri()->getPort()) {
            $baseUrl .= ':' . $request->getUri()->getPort();
        }

        // Ambil parameter id dari query param
        $params = $request->getQueryParams();
        $room_id = $params['id'] ?? null;

        if (!$room_id) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Parameter id ruangan diperlukan'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $stmt = $db->prepare("
                SELECT 
                    r.id,
                    r.prodi_id,
                    p.nama_prodi,
                    r.kode_ruangan,
                    r.nama_ruangan,
                    r.kapasitas,
                    r.deskripsi,
                    r.foto,
                    r.created_at,
                    r.updated_at,
                    IFNULL(peng.status, 0) AS status_penggunaan
                FROM mr_ruangan r
                LEFT JOIN mr_prodi p ON r.prodi_id = p.id
                LEFT JOIN mr_penggunaan_ruangan peng ON r.id = peng.ruangan_id
                WHERE r.id = :id
                LIMIT 1
            ");

            $stmt->execute(['id' => $room_id]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                $response->getBody()->write(json_encode([
                    'status' => false,
                    'message' => 'Ruangan tidak ditemukan'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            // Tambahkan URL lengkap untuk foto
            if (!empty($room['foto'])) {
                $room['foto'] = $baseUrl . '/uploads/rooms/' . $room['foto'];
            } else {
                $room['foto'] = null;
            }

            $response->getBody()->write(json_encode([
                'status' => true,
                'data' => $room
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


    $app->get('/room/available', function ($request, $response) {
        $db = $this->get('db_default');

        // Base URL dinamis
        $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
        if ($request->getUri()->getPort()) {
            $baseUrl .= ':' . $request->getUri()->getPort();
        }

        // Ambil prodi_id
        $params = $request->getQueryParams();
        $prodi_id = $params['prodi_id'] ?? null;

        if (!$prodi_id) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Parameter prodi_id diperlukan'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Ambil daftar ruangan tanpa duplikasi
            $stmt = $db->prepare("
                SELECT 
                    r.id,
                    r.prodi_id,
                    p.nama_prodi,
                    r.kode_ruangan,
                    r.nama_ruangan,
                    r.kapasitas,
                    r.deskripsi,
                    r.foto,
                    r.created_at,
                    r.updated_at,
                    IFNULL((
                        SELECT MAX(peng.status) 
                        FROM mr_penggunaan_ruangan peng 
                        WHERE peng.ruangan_id = r.id
                    ), 0) AS status_penggunaan
                FROM mr_ruangan r
                LEFT JOIN mr_prodi p ON r.prodi_id = p.id
                WHERE r.prodi_id = :prodi_id
                HAVING status_penggunaan = 0  -- hanya yang tidak terpakai
                ORDER BY r.nama_ruangan ASC
            ");

            $stmt->execute(['prodi_id' => $prodi_id]);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Tambah URL foto lengkap
            foreach ($rooms as &$room) {
                if (!empty($room['foto'])) {
                    $room['foto'] = $baseUrl . '/uploads/rooms/' . $room['foto'];
                } else {
                    $room['foto'] = null;
                }
            }

            $response->getBody()->write(json_encode([
                'status' => true,
                'data' => $rooms
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




    $app->post('/room/add', function ($request, $response) {
        $db = $this->get('db_default');
        $parsed = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        // Field wajib (id tidak perlu karena auto generate)
        $required = ['prodi_id', 'kode_ruangan', 'nama_ruangan', 'kapasitas'];
        foreach ($required as $key) {
            if (empty($parsed[$key])) {
                return $response->withJson([
                    'status' => false,
                    'message' => "Field '$key' wajib diisi."
                ], 400);
            }
        }

        // ================================
        // 🔥 Generate ID ruangan otomatis
        // ================================
        $roomId = generateRoomId($db);

        if (!$roomId) {
            return $response->withJson([
                'status' => false,
                'message' => 'Gagal membuat ID ruangan.'
            ], 500);
        }

        // ================================
        // 🔥 Handle Upload Foto (Opsional)
        // ================================
        $fotoName = null;

        if (!empty($uploadedFiles['foto'])) {
            $foto = $uploadedFiles['foto'];

            if ($foto->getError() === UPLOAD_ERR_OK) {
                $ext = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
                $fotoName = 'room_' . time() . '.' . $ext;

                $uploadPath = __DIR__ . '/../../public/uploads/rooms/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                $foto->moveTo($uploadPath . $fotoName);
            }
        }

        // ================================
        // 🔥 Simpan Data ke Database
        // ================================
        try {
            $sql = "
                INSERT INTO mr_ruangan
                    (id, prodi_id, kode_ruangan, nama_ruangan, kapasitas, deskripsi, foto, created_at, updated_at)
                VALUES 
                    (:id, :prodi_id, :kode_ruangan, :nama_ruangan, :kapasitas, :deskripsi, :foto, NOW(), NOW())
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id'           => $roomId,                 // 🔥 ID otomatis
                ':prodi_id'     => $parsed['prodi_id'],
                ':kode_ruangan' => $parsed['kode_ruangan'],
                ':nama_ruangan' => $parsed['nama_ruangan'],
                ':kapasitas'    => $parsed['kapasitas'],
                ':deskripsi'    => $parsed['deskripsi'] ?? null,
                ':foto'         => $fotoName,
            ]);

            return $response->withJson([
                'status' => true,
                'message' => 'Room berhasil ditambahkan.',
                'id' => $roomId,                            // kembalikan ID otomatis
            ], 201);

        } catch (\Exception $e) {
            return $response->withJson([
                'status' => false,
                'message' => 'Gagal menambah room: ' . $e->getMessage()
            ], 500);
        }
    });


    $app->post('/room/update', function ($request, $response) {
        $db = $this->get('db_default');
        $parsed = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        // ================================
        // 🔥 Validasi ID
        // ================================
        if (empty($parsed['id'])) {
            return $response->withJson([
                'status' => false,
                'message' => "Parameter 'id' diperlukan."
            ], 400);
        }

        $roomId = $parsed['id'];

        // ================================
        // 🔥 Ambil data lama ruangan
        // ================================
        $stmtOld = $db->prepare("SELECT * FROM mr_ruangan WHERE id = :id LIMIT 1");
        $stmtOld->execute([':id' => $roomId]);
        $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

        if (!$old) {
            return $response->withJson([
                'status' => false,
                'message' => 'Ruangan tidak ditemukan.'
            ], 404);
        }

        // ================================
        // 🔥 Validasi field wajib
        // ================================
        $required = ['prodi_id', 'kode_ruangan', 'nama_ruangan', 'kapasitas'];
        foreach ($required as $key) {
            if (empty($parsed[$key])) {
                return $response->withJson([
                    'status' => false,
                    'message' => "Field '$key' wajib diisi."
                ], 400);
            }
        }

        // ================================
        // 🔥 Handle Foto Baru (Opsional)
        // ================================
        $fotoName = $old['foto']; // default: pakai foto lama

        if (!empty($uploadedFiles['foto'])) {
            $foto = $uploadedFiles['foto'];

            if ($foto->getError() === UPLOAD_ERR_OK) {
                $ext = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
                $fotoName = 'room_' . time() . '.' . $ext;

                $uploadPath = __DIR__ . '/../../public/uploads/rooms/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                // Hapus foto lama jika ada
                if (!empty($old['foto']) && file_exists($uploadPath . $old['foto'])) {
                    unlink($uploadPath . $old['foto']);
                }

                // Simpan foto baru
                $foto->moveTo($uploadPath . $fotoName);
            }
        }

        // ================================
        // 🔥 Update Data ke Database
        // ================================
        try {
            $sql = "
                UPDATE mr_ruangan SET 
                    prodi_id      = :prodi_id,
                    kode_ruangan  = :kode_ruangan,
                    nama_ruangan  = :nama_ruangan,
                    kapasitas     = :kapasitas,
                    deskripsi     = :deskripsi,
                    foto          = :foto,
                    updated_at    = NOW()
                WHERE id = :id
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id'           => $roomId,
                ':prodi_id'     => $parsed['prodi_id'],
                ':kode_ruangan' => $parsed['kode_ruangan'],
                ':nama_ruangan' => $parsed['nama_ruangan'],
                ':kapasitas'    => $parsed['kapasitas'],
                ':deskripsi'    => $parsed['deskripsi'] ?? null,
                ':foto'         => $fotoName,
            ]);

            // 🔥 Ambil lagi data ruangan terbaru
            $stmtNew = $db->prepare("SELECT * FROM mr_ruangan WHERE id = :id LIMIT 1");
            $stmtNew->execute([':id' => $roomId]);
            $updatedRoom = $stmtNew->fetch(PDO::FETCH_ASSOC);

            return $response->withJson([
                'status' => true,
                'message' => 'Ruangan berhasil diperbarui.',
                'data' => $updatedRoom
            ], 200);

        } catch (\Exception $e) {
            return $response->withJson([
                'status' => false,
                'message' => 'Gagal mengupdate ruangan: ' . $e->getMessage()
            ], 500);
        }
    });


    $app->delete('/room/delete', function ($request, $response) {
        $db = $this->get('db_default');

        // Ambil parameter id dari query param
        $params = $request->getQueryParams();
        $room_id = $params['id'] ?? null;

        if (!$room_id) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Parameter id ruangan diperlukan'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Cek apakah ruangan ada
            $stmtCheck = $db->prepare("SELECT foto FROM mr_ruangan WHERE id = :id LIMIT 1");
            $stmtCheck->execute([':id' => $room_id]);
            $room = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$room) {
                $response->getBody()->write(json_encode([
                    'status' => false,
                    'message' => 'Ruangan tidak ditemukan'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            // Hapus foto lama jika ada
            if (!empty($room['foto'])) {
                $fotoPath = __DIR__ . '/../../public/uploads/rooms/' . $room['foto'];
                if (file_exists($fotoPath)) {
                    unlink($fotoPath);
                }
            }

            // Hapus data ruangan
            $stmtDelete = $db->prepare("DELETE FROM mr_ruangan WHERE id = :id");
            $stmtDelete->execute([':id' => $room_id]);

            $response->getBody()->write(json_encode([
                'status' => true,
                'message' => 'Ruangan berhasil dihapus'
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


    $app->post('/room/borrow', function ($request, $response) {
        $db = $this->get('db_default');

        $post  = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        try {
            $sdmId          = $post['sdm_id'] ?? null;
            $ruanganId      = $post['room_id'] ?? null;
            $tanggalMulai   = $post['start_time'] ?? null;
            $tanggalSelesai = $post['end_time'] ?? null;
            $kegiatan       = $post['activity_name'] ?? null;
            $deskripsi      = $post['description'] ?? null; // <-- Tambahkan deskripsi

            $equipments = isset($post['equipments']) 
                ? (is_array($post['equipments']) ? $post['equipments'] : json_decode($post['equipments'], true))
                : [];

            // Upload foto
            $foto = $files['foto'] ?? null;
            $fileName = null;
            if ($foto && $foto->getError() === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/borrows/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = time() . '_' . $foto->getClientFilename();
                $foto->moveTo($uploadDir . $fileName);
            }

            // Insert 1 row per equipment (dengan deskripsi)
            $stmt = $db->prepare("
                INSERT INTO mr_penggunaan_ruangan
                (id, ruangan_id, alat_id, software_id, sdm_id, tanggal_mulai, tanggal_selesai, kegiatan, deskripsi, foto, status, created_at)
                VALUES
                (:id, :ruangan_id, :alat_id, :software_id, :sdm_id, :tanggal_mulai, :tanggal_selesai, :kegiatan, :deskripsi, :foto, 1, NOW())
            ");

            foreach ($equipments as $item) {
                $newId = generateRoomUsageId($db);

                $alatId = $item['type'] === 'alat' ? $item['id'] : null;
                $softwareId = $item['type'] === 'software' ? $item['id'] : null;

                $stmt->execute([
                    ':id'            => $newId,
                    ':ruangan_id'    => $ruanganId,
                    ':alat_id'       => $alatId,
                    ':software_id'   => $softwareId,
                    ':sdm_id'        => $sdmId,
                    ':tanggal_mulai' => $tanggalMulai,
                    ':tanggal_selesai'=> $tanggalSelesai,
                    ':kegiatan'      => $kegiatan,
                    ':deskripsi'     => $deskripsi, // <-- kirim deskripsi
                    ':foto'          => $fileName,
                ]);
            }

            return $response->withJson([
                'status' => true,
                'message' => 'Peminjaman ruangan berhasil disimpan'
            ]);

        } catch (PDOException $e) {
            return $response->withJson([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });




    $app->post('/room/update-status', function ($request, $response) {
        $db = $this->get('db_default');

        try {
            // Ambil waktu sekarang
            $now = date('Y-m-d H:i:s');

            // Update status menjadi 0 jika tanggal_selesai sudah lewat
            $stmt = $db->prepare("
                UPDATE mr_penggunaan_ruangan
                SET status = 0
                WHERE tanggal_selesai <= :now
                  AND status = 1
            ");

            $stmt->execute(['now' => $now]);
            $updatedRows = $stmt->rowCount();

            $response->getBody()->write(json_encode([
                'status' => true,
                'message' => "Berhasil memperbarui status",
                'updated_rows' => $updatedRows
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
