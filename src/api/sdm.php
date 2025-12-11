<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->get('/sdm/list', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $role = isset($params['role']) ? (int)$params['role'] : null;
        $sdm_id = $params['sdm_id'] ?? null;

        if ($role === null || !$sdm_id) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(400)
                            ->write(json_encode([
                                'status' => false,
                                'message' => 'Parameter role dan sdm_id wajib diisi'
                            ]));
        }

        try {
            if (in_array($role, ['0', '1', '3'])) {
                // Role 0,1,3 → lihat semua SDM
                $stmt = $db->prepare("
                    SELECT 
                        s.id AS sdm_id,
                        s.user_id,
                        s.prodi_id,
                        p.nama_prodi AS prodi_nama,
                        s.jenis_kelamin,
                        s.tanggal_lahir,
                        s.pendidikan_terakhir,
                        s.bidang_studi,
                        s.klasifikasi,
                        s.kategori_pengajar,
                        s.status AS sdm_status,
                        s.created_at AS sdm_created_at,
                        s.updated_at AS sdm_updated_at,
                        
                        u.id AS user_id,
                        u.nip,
                        u.full_name,
                        u.username,
                        u.email,
                        u.role,
                        u.is_claim,
                        u.device_id,
                        u.player_id,
                        u.foto AS user_foto,
                        u.last_login,
                        u.created_at AS user_created_at,
                        u.updated_at AS user_updated_at
                    FROM mr_sdm s
                    INNER JOIN mr_users u ON s.user_id = u.id
                    LEFT JOIN mr_prodi p ON s.prodi_id = p.id
                    ORDER BY u.full_name ASC
                ");
                $stmt->execute();
            } else {
                // Role lain → hanya lihat SDM tertentu
                $stmt = $db->prepare("
                    SELECT 
                        s.id AS sdm_id,
                        s.user_id,
                        s.prodi_id,
                        p.nama_prodi AS prodi_nama,
                        s.jenis_kelamin,
                        s.tanggal_lahir,
                        s.pendidikan_terakhir,
                        s.bidang_studi,
                        s.klasifikasi,
                        s.kategori_pengajar,
                        s.status AS sdm_status,
                        s.created_at AS sdm_created_at,
                        s.updated_at AS sdm_updated_at,

                        u.id AS user_id,
                        u.nip,
                        u.full_name,
                        u.username,
                        u.email,
                        u.role,
                        u.is_claim,
                        u.device_id,
                        u.player_id,
                        u.foto AS user_foto,
                        u.last_login,
                        u.created_at AS user_created_at,
                        u.updated_at AS user_updated_at
                    FROM mr_sdm s
                    INNER JOIN mr_users u ON s.user_id = u.id
                    LEFT JOIN mr_prodi p ON s.prodi_id = p.id
                    WHERE s.id = :sdm_id
                    ORDER BY u.full_name ASC
                ");
                $stmt->execute(['sdm_id' => $sdm_id]);
            }


            $sdmList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];
            foreach ($sdmList as $sdm) {
                // Ambil sertifikat per SDM
                $stmtCert = $db->prepare("
                    SELECT *
                    FROM mr_sertifikasi
                    WHERE sdm_id = :sdm_id
                    ORDER BY tanggal_expiry DESC
                ");
                $stmtCert->execute(['sdm_id' => $sdm['sdm_id']]);
                $certificates = $stmtCert->fetchAll(PDO::FETCH_ASSOC);

                $result[] = [
                    'id' => $sdm['user_id'],
                    'nip' => $sdm['nip'],
                    'full_name' => $sdm['full_name'],
                    'username' => $sdm['username'],
                    'email' => $sdm['email'],
                    'role' => $sdm['role'],
                    'is_claim' => $sdm['is_claim'],
                    'device_id' => $sdm['device_id'],
                    'player_id' => $sdm['player_id'],
                    'foto' => $sdm['user_foto'],
                    'last_login' => $sdm['last_login'],
                    'created_at' => $sdm['user_created_at'],
                    'updated_at' => $sdm['user_updated_at'],

                    'sdm' => [
                        'id' => $sdm['sdm_id'],
                        'user_id' => $sdm['user_id'],
                        'prodi_id' => $sdm['prodi_id'],
                        'prodi_nama' => $sdm['prodi_nama'],
                        'jenis_kelamin' => $sdm['jenis_kelamin'],
                        'tanggal_lahir' => $sdm['tanggal_lahir'],
                        'pendidikan_terakhir' => $sdm['pendidikan_terakhir'],
                        'bidang_studi' => $sdm['bidang_studi'],
                        'klasifikasi' => $sdm['klasifikasi'],
                        'kategori_pengajar' => $sdm['kategori_pengajar'],
                        'status' => $sdm['sdm_status'],
                        'created_at' => $sdm['sdm_created_at'],
                        'updated_at' => $sdm['sdm_updated_at'],
                        'certificates' => $certificates,
                        'certificate_count' => count($certificates)
                    ]
                ];
            }

            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200)
                            ->write(json_encode([
                                'status' => true,
                                'data' => $result
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


    $app->get('/sdm/detail', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $sdm_id = $params['sdm_id'] ?? null;

        if (!$sdm_id) {
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(400)
                            ->write(json_encode([
                                'status' => false,
                                'message' => 'Parameter sdm_id wajib diisi'
                            ]));
        }

        try {
            // Base URL dinamis
            $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
            if ($request->getUri()->getPort()) {
                $baseUrl .= ':' . $request->getUri()->getPort();
            }

            // Ambil SDM berdasarkan sdm_id
            $stmt = $db->prepare("
                SELECT 
                    s.id AS sdm_id,
                    s.user_id,
                    s.prodi_id,
                    p.nama_prodi AS prodi_nama,
                    s.jenis_kelamin,
                    s.tanggal_lahir,
                    s.pendidikan_terakhir,
                    s.bidang_studi,
                    s.klasifikasi,
                    s.kategori_pengajar,
                    s.status AS sdm_status,
                    s.created_at AS sdm_created_at,
                    s.updated_at AS sdm_updated_at,
                    
                    u.id AS user_id,
                    u.nip,
                    u.full_name,
                    u.username,
                    u.email,
                    u.role,
                    u.is_claim,
                    u.device_id,
                    u.player_id,
                    u.foto AS user_foto,
                    u.last_login,
                    u.created_at AS user_created_at,
                    u.updated_at AS user_updated_at
                FROM mr_sdm s
                INNER JOIN mr_users u ON s.user_id = u.id
                LEFT JOIN mr_prodi p ON s.prodi_id = p.id
                WHERE s.id = :sdm_id
                LIMIT 1
            ");
            $stmt->execute(['sdm_id' => $sdm_id]);
            $sdm = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sdm) {
                return $response->withHeader('Content-Type', 'application/json')
                                ->withStatus(404)
                                ->write(json_encode([
                                    'status' => false,
                                    'message' => 'SDM tidak ditemukan'
                                ]));
            }

            // Ambil sertifikat SDM
            $stmtCert = $db->prepare("
                SELECT *
                FROM mr_sertifikasi
                WHERE sdm_id = :sdm_id
                ORDER BY tanggal_expiry DESC
            ");
            $stmtCert->execute(['sdm_id' => $sdm['sdm_id']]);
            $certificates = $stmtCert->fetchAll(PDO::FETCH_ASSOC);

            // Tambahkan base URL ke file sertifikat
            foreach ($certificates as &$cert) {
                if (!empty($cert['file_sertifikat'])) {
                    $cert['file_sertifikat'] = $baseUrl . '/uploads/certificate/' . $cert['file_sertifikat'];
                } else {
                    $cert['file_sertifikat'] = null;
                }
            }

            // Susun response final
            $result = [
                'id' => $sdm['user_id'],
                'nip' => $sdm['nip'],
                'full_name' => $sdm['full_name'],
                'username' => $sdm['username'],
                'email' => $sdm['email'],
                'role' => $sdm['role'],
                'is_claim' => $sdm['is_claim'],
                'device_id' => $sdm['device_id'],
                'player_id' => $sdm['player_id'],
                'foto' => $sdm['user_foto'],
                'last_login' => $sdm['last_login'],
                'created_at' => $sdm['user_created_at'],
                'updated_at' => $sdm['user_updated_at'],

                'sdm' => [
                    'id' => $sdm['sdm_id'],
                    'user_id' => $sdm['user_id'],
                    'prodi_id' => $sdm['prodi_id'],
                    'prodi_nama' => $sdm['prodi_nama'],
                    'jenis_kelamin' => $sdm['jenis_kelamin'],
                    'tanggal_lahir' => $sdm['tanggal_lahir'],
                    'pendidikan_terakhir' => $sdm['pendidikan_terakhir'],
                    'bidang_studi' => $sdm['bidang_studi'],
                    'klasifikasi' => $sdm['klasifikasi'],
                    'kategori_pengajar' => $sdm['kategori_pengajar'],
                    'status' => $sdm['sdm_status'],
                    'created_at' => $sdm['sdm_created_at'],
                    'updated_at' => $sdm['sdm_updated_at'],
                    'certificates' => $certificates,
                    'certificate_count' => count($certificates)
                ]
            ];

            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(200)
                            ->write(json_encode([
                                'status' => true,
                                'data' => [$result]
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




    $app->post('/sdm/add', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $body = $request->getParsedBody();

            if (!$body) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "Invalid body"
                ]);
            }

            // ======================================
            // Parsing CSV -> array
            // ======================================
            if (isset($body['prodi_ids']) && !is_array($body['prodi_ids'])) {
                $body['prodi_ids'] = array_filter(array_map('trim', explode(',', $body['prodi_ids'])));
            }
            if (isset($body['bidang']) && !is_array($body['bidang'])) {
                $body['bidang'] = array_filter(array_map('trim', explode(',', $body['bidang'])));
            }

            // ======================================
            // VALIDASI FIELD WAJIB
            // ======================================
            $required = ['nip','nama','jenis_kelamin','tgl_lahir','pendidikan','role','prodi_ids','bidang','klasifikasi','kategori'];
            foreach ($required as $field) {
                if (!isset($body[$field]) || $body[$field]==='' || $body[$field]===null) {
                    return $response->withStatus(400)->withJson([
                        "status"=>false,
                        "message"=>"Field '$field' wajib diisi"
                    ]);
                }
            }

            // ======================================
            // Validasi array
            // ======================================
            if (!is_array($body['prodi_ids']) || count($body['prodi_ids'])===0) {
                return $response->withJson([
                    "status"=>false,
                    "message"=>"Program studi wajib minimal 1"
                ]);
            }
            if (!is_array($body['bidang']) || count($body['bidang'])===0) {
                return $response->withJson([
                    "status"=>false,
                    "message"=>"Bidang studi wajib minimal 1"
                ]);
            }

            // ======================================
            // Role validasi
            // ======================================
            $allowedRoles=['1','2','3','4'];
            if(!in_array($body['role'],$allowedRoles)){
                return $response->withStatus(400)->withJson([
                    "status"=>false,
                    "message"=>"Role tidak valid"
                ]);
            }

            // ======================================
            // Cek NIP duplikat
            // ======================================
            $cekNip = $db->prepare("SELECT id FROM mr_users WHERE nip=?");
            $cekNip->execute([$body['nip']]);
            if($cekNip->fetch()){
                return $response->withJson([
                    "status"=>false,
                    "message"=>"NIP sudah digunakan"
                ]);
            }

            // ======================================
            // Transaction start
            // ======================================
            $db->beginTransaction();

            // ======================================
            // Generate user_id
            // ======================================
            $userId = generateUserId($db);

            // ======================================
            // Insert mr_users (1x saja)
            // ======================================
            $sqlUser = "
                INSERT INTO mr_users (id, nip, full_name, role, is_claim, created_at, updated_at)
                VALUES (:id, :nip, :full_name, :role, 1, NOW(), NOW())
            ";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ":id"=>$userId,
                ":nip"=>$body['nip'],
                ":full_name"=>$body['nama'],
                ":role"=>$body['role']
            ]);

            // ======================================
            // Insert mr_sdm (multi prodi)
            // ======================================
            $sqlSDM = "
                INSERT INTO mr_sdm (
                    id, user_id, prodi_id, jenis_kelamin, tanggal_lahir,
                    pendidikan_terakhir, bidang_studi, klasifikasi, kategori_pengajar,
                    status, created_at, updated_at
                ) VALUES (
                    :id, :user_id, :prodi_id, :jenis_kelamin, :tanggal_lahir,
                    :pendidikan_terakhir, :bidang_studi, :klasifikasi, :kategori_pengajar,
                    1, NOW(), NOW()
                )
            ";
            $stmtSDM = $db->prepare($sqlSDM);
            $sdmIds = [];

            foreach($body['prodi_ids'] as $prodi){
                $sdmId = generateSdmId($db);
                $sdmIds[] = $sdmId;

                $stmtSDM->execute([
                    ":id"=>$sdmId,
                    ":user_id"=>$userId,
                    ":prodi_id"=>$prodi,
                    ":jenis_kelamin"=>$body['jenis_kelamin'],
                    ":tanggal_lahir"=>$body['tgl_lahir'],
                    ":pendidikan_terakhir"=>$body['pendidikan'],
                    ":bidang_studi"=>implode(", ", $body['bidang']), // simpan sebagai string
                    ":klasifikasi"=>$body['klasifikasi'],
                    ":kategori_pengajar"=>$body['kategori']
                ]);
            }

            // ======================================
            // Commit
            // ======================================
            $db->commit();

            return $response->withJson([
                "status"=>true,
                "message"=>"SDM berhasil ditambahkan",
                "user_id"=>$userId,
                "sdm_ids"=>$sdmIds
            ]);

        } catch(Exception $e){
            if($db->inTransaction()) $db->rollBack();
            return $response->withStatus(500)->withJson([
                "status"=>false,
                "message"=>$e->getMessage()
            ]);
        }
    });



    $app->post('/sdm/update', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $body = $request->getParsedBody();

            if (!$body || !isset($body['user_id']) || !isset($body['sdm_id'])) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "Invalid body or missing user_id / sdm_id"
                ]);
            }

            $userId = $body['user_id'];
            $sdmId = $body['sdm_id'];

            // Parsing CSV -> array untuk bidang
            if (isset($body['bidang']) && !is_array($body['bidang'])) {
                $body['bidang'] = array_filter(array_map('trim', explode(',', $body['bidang'])));
            }

            // Parsing CSV -> array untuk prodi_ids
            if (isset($body['prodi_ids']) && !is_array($body['prodi_ids'])) {
                $body['prodi_ids'] = array_filter(array_map('trim', explode(',', $body['prodi_ids'])));
            }

            // VALIDASI FIELD WAJIB
            $required = ['nip','nama','jenis_kelamin','tgl_lahir','pendidikan','role','prodi_ids','bidang','klasifikasi','kategori'];
            foreach ($required as $field) {
                if (!isset($body[$field]) || $body[$field] === '' || $body[$field] === null) {
                    return $response->withStatus(400)->withJson([
                        "status"=>false,
                        "message"=>"Field '$field' wajib diisi"
                    ]);
                }
            }

            // Validasi array
            if (!is_array($body['bidang']) || count($body['bidang'])===0) {
                return $response->withJson([
                    "status"=>false,
                    "message"=>"Bidang studi wajib minimal 1"
                ]);
            }
            if (!is_array($body['prodi_ids']) || count($body['prodi_ids'])===0) {
                return $response->withJson([
                    "status"=>false,
                    "message"=>"Program Studi wajib minimal 1"
                ]);
            }

            // Role validasi
            $allowedRoles=['1','2','3','4'];
            if(!in_array($body['role'],$allowedRoles)){
                return $response->withStatus(400)->withJson([
                    "status"=>false,
                    "message"=>"Role tidak valid"
                ]);
            }

            // Cek NIP duplikat kecuali milik user ini
            $cekNip = $db->prepare("SELECT id FROM mr_users WHERE nip=? AND id!=?");
            $cekNip->execute([$body['nip'], $userId]);
            if($cekNip->fetch()){
                return $response->withJson([
                    "status"=>false,
                    "message"=>"NIP sudah digunakan oleh user lain"
                ]);
            }

            $db->beginTransaction();

            // Update mr_users
            $sqlUser = "
                UPDATE mr_users SET
                    nip=:nip,
                    full_name=:full_name,
                    role=:role,
                    updated_at=NOW()
                WHERE id=:id
            ";
            $stmtUser = $db->prepare($sqlUser);
            $stmtUser->execute([
                ":nip"=>$body['nip'],
                ":full_name"=>$body['nama'],
                ":role"=>$body['role'],
                ":id"=>$userId
            ]);

            // ===============================
            // UPDATE SDM EXISTING
            // ===============================
            // Ambil SDM berdasarkan sdm_id
            $stmtOldSdm = $db->prepare("SELECT * FROM mr_sdm WHERE id=? AND user_id=?");
            $stmtOldSdm->execute([$sdmId, $userId]);
            $oldSdm = $stmtOldSdm->fetch(PDO::FETCH_ASSOC);

            if (!$oldSdm) {
                $db->rollBack();
                return $response->withStatus(404)->withJson([
                    "status"=>false,
                    "message"=>"SDM tidak ditemukan"
                ]);
            }

            // Update SDM dengan prodi baru & data lain
            $stmtUpdate = $db->prepare("
                UPDATE mr_sdm SET
                    prodi_id=:prodi_id,
                    jenis_kelamin=:jenis_kelamin,
                    tanggal_lahir=:tanggal_lahir,
                    pendidikan_terakhir=:pendidikan_terakhir,
                    bidang_studi=:bidang_studi,
                    klasifikasi=:klasifikasi,
                    kategori_pengajar=:kategori_pengajar,
                    updated_at=NOW()
                WHERE id=:sdm_id
            ");

            // Jika multiple prodi, ambil prodi pertama (sesuaikan kebutuhan)
            $newProdiId = $body['prodi_ids'][0];

            $stmtUpdate->execute([
                ":prodi_id"=>$newProdiId,
                ":jenis_kelamin"=>$body['jenis_kelamin'],
                ":tanggal_lahir"=>$body['tgl_lahir'],
                ":pendidikan_terakhir"=>$body['pendidikan'],
                ":bidang_studi"=>implode(", ", $body['bidang']),
                ":klasifikasi"=>$body['klasifikasi'],
                ":kategori_pengajar"=>$body['kategori'],
                ":sdm_id"=>$sdmId
            ]);

            $db->commit();

            return $response->withJson([
                "status"=>true,
                "message"=>"SDM berhasil diperbarui"
            ]);

        } catch(Exception $e){
            if($db->inTransaction()) $db->rollBack();
            return $response->withStatus(500)->withJson([
                "status"=>false,
                "message"=>$e->getMessage()
            ]);
        }
    });



    $app->delete('/sdm/delete', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $body = $request->getParsedBody();

            // Validasi wajib
            if (!$body || !isset($body['user_id']) || !isset($body['sdm_id'])) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "user_id dan sdm_id wajib diisi"
                ]);
            }

            $userId = $body['user_id'];
            $sdmId = $body['sdm_id'];

            // Mulai transaction
            $db->beginTransaction();

            // Hapus SDM spesifik
            $stmt = $db->prepare("DELETE FROM mr_sdm WHERE id=? AND user_id=?");
            $stmt->execute([$sdmId, $userId]);

            // Cek apakah ada yang dihapus
            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                return $response->withStatus(404)->withJson([
                    "status" => false,
                    "message" => "SDM tidak ditemukan atau bukan milik user"
                ]);
            }

            $db->commit();

            return $response->withJson([
                "status" => true,
                "message" => "SDM berhasil dihapus"
            ]);

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            return $response->withStatus(500)->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    });




};