<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {
    
    $app->get("/claim/check", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $nip = $params["nip"] ?? null;

        if (!$nip) {
            $response->getBody()->write(json_encode([
                "status" => false,
                "message" => "Parameter 'nip' is required"
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }

        $db = $this->get('db_default');

        try {
            // ---- 1️⃣ Cek SDM ----
            $sql_sdm = "SELECT * FROM mr_sdm WHERE nip = :nip LIMIT 1";
            $stmt = $db->prepare($sql_sdm);
            $stmt->execute(["nip" => $nip]);
            $sdm = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sdm) {
                $response->getBody()->write(json_encode([
                    "status" => false,
                    "message" => "NIP not found"
                ]));
                return $response->withHeader("Content-Type", "application/json")->withStatus(404);
            }

            // Tambahkan base URL untuk foto
            $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
            if ($request->getUri()->getPort()) {
                $baseUrl .= ":" . $request->getUri()->getPort();
            }
            if (!empty($sdm["foto"])) {
                $sdm["foto"] = $baseUrl . "/" . $sdm["foto"];
            }

            // ---- 2️⃣ Cek user berdasarkan sdm_id ----
            $sql_user = "
                SELECT 
                    u.id AS user_id,
                    u.username,
                    u.email,
                    u.role,
                    u.device_id,
                    u.is_claim,
                    u.status,
                    u.foto,
                    u.last_login,
                    u.sdm_id
                FROM mr_users u
                WHERE u.sdm_id = :sdm_id
                LIMIT 1
            ";
            $stmt = $db->prepare($sql_user);
            $stmt->execute(["sdm_id" => $sdm["id"]]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // ---- Gabungkan data SDM ke user ----
            if ($user) {
                if (intval($user["is_claim"]) === 1) {
                    $response->getBody()->write(json_encode([
                        "status" => false,
                        "message" => "SDM already claimed by another user"
                    ]));
                    return $response->withHeader("Content-Type", "application/json")->withStatus(409);
                }
            } else {
                $user = [
                    "user_id" => null,
                    "username" => null,
                    "email" => null,
                    "role" => null,
                    "device_id" => null,
                    "is_claim" => 0,
                    "status" => null,
                    "foto" => null,
                    "last_login" => null,
                    "sdm_id" => $sdm["id"]
                ];
            }

            // Masukkan data SDM ke dalam field 'sdm'
            $user["sdm"] = $sdm;

            // ---- Response final ----
            $response->getBody()->write(json_encode([
                "status" => true,
                "message" => "SDM found",
                "data" => [
                    "user" => $user
                ]
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(200);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ]));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    });




    $app->post("/claim", function (Request $request, Response $response) {
        try {
            $data = $request->getParsedBody();
            error_log("Parsed body: " . print_r($data, true));

            $nip        = $data["nip"] ?? null;
            $username   = $data["username"] ?? null;
            $passwordRaw = $data["password"] ?? null;
            $email      = $data["email"] ?? null;
            $device_id  = $data["device_id"] ?? null;
            $role       = $data["role"] ?? null;

            // Validasi input
            if (!$nip || !$username || !$passwordRaw || !$device_id || !$role) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Data tidak lengkap"
                ], 400);
            }

            $db = $this->get('db_default');
            $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

            // 1. Cek SDM berdasarkan NIP
            $sql_sdm = "SELECT id, nip, nama_lengkap
                        FROM mr_sdm 
                        WHERE nip = :nip LIMIT 1";

            $stmt = $db->prepare($sql_sdm);
            $stmt->execute(["nip" => $nip]);
            $sdm = $stmt->fetch();

            if (!$sdm) {
                return $response->withJson([
                    "status" => false,
                    "message" => "NIP tidak ditemukan"
                ], 404);
            }

            $sdm_id = $sdm["id"];

            // 2. Cek apakah SDM sudah punya user
            $sql_user = "SELECT * FROM mr_users WHERE sdm_id = :sdm_id LIMIT 1";
            $stmt = $db->prepare($sql_user);
            $stmt->execute(["sdm_id" => $sdm_id]);
            $user = $stmt->fetch();

            // === CASE A: SDM sudah punya user ===
            if ($user) {

                // Sudah diklaim → tolak
                if ($user["is_claim"] == 1) {
                    return $response->withJson([
                        "status" => false,
                        "message" => "SDM ini sudah diklaim oleh user lain"
                    ], 409);
                }

                // Device ID digunakan user lain
                $deviceCheckSql = "SELECT id FROM mr_users 
                                WHERE device_id = :device_id AND id != :id LIMIT 1";

                $deviceCheckStmt = $db->prepare($deviceCheckSql);
                $deviceCheckStmt->execute([
                    "device_id" => $device_id,
                    "id" => $user["id"]
                ]);

                if ($deviceCheckStmt->fetch()) {
                    return $response->withJson([
                        "status" => false,
                        "message" => "Device ini sudah digunakan oleh akun lain"
                    ], 400);
                }

                // UPDATE user
                $updateSql = "
                    UPDATE mr_users 
                    SET 
                        username = :username,
                        password = :password,
                        email = :email,
                        device_id = :device_id,
                        role = :role,
                        is_claim = 1,
                        updated_at = NOW()
                    WHERE id = :id
                ";

                $updateStmt = $db->prepare($updateSql);
                $updateStmt->execute([
                    "username" => $username,
                    "password" => $password,
                    "email" => $email,
                    "device_id" => $device_id,
                    "role" => $role,
                    "id" => $user["id"]
                ]);

                return $response->withJson([
                    "status" => true,
                    "message" => "Klaim berhasil",
                    "data" => [
                        "user_id" => $user["id"],
                        "username" => $username,
                        "email" => $email,
                        "device_id" => $device_id,
                        "role" => intval($role),
                    ]
                ], 200);
            }

            // === CASE B: SDM belum punya user → buat baru ===
            $newUserId = generateUserId($db);

            $insertSql = "
                INSERT INTO mr_users (id, sdm_id, username, password, email, device_id, role, is_claim, status, created_at)
                VALUES (:id, :sdm_id, :username, :password, :email, :device_id, :role, 1, 1, NOW())
            ";

            $insertStmt = $db->prepare($insertSql);
            $insertStmt->execute([
                "id"        => $newUserId,
                "sdm_id"    => $sdm_id,
                "username"  => $username,
                "password"  => $password,
                "email"     => $email,
                "device_id" => $device_id,
                "role"      => $role
            ]);

            // Sukses
            return $response->withJson([
                "status" => true,
                "message" => "Klaim berhasil dan akun baru dibuat",
                "data" => [
                    "user_id" => $newUserId,
                    "username" => $username,
                    "email" => $email,
                    "device_id" => $device_id,
                    "role" => intval($role),
                ]
            ], 201);

        } catch (Exception $e) {
            error_log("Exception: " . $e->getMessage());
            return $response->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    });

    
    
};
