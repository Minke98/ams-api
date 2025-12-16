<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    // =========================
    // Check claim status
    // =========================
    $app->get("/claim/check", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $nip = $params["nip"] ?? null;

        if (!$nip) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'nip' is required"
            ], 400);
        }

        $db = $this->get('db_default');

        try {

            // 1️⃣ Cari user berdasarkan nip (langsung dari mr_users)
            $sql_user = "SELECT * FROM mr_users WHERE nip = :nip LIMIT 1";
            $stmt = $db->prepare($sql_user);
            $stmt->execute(["nip" => $nip]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2️⃣ Jika user belum ada → return default profil kosong
            if (!$user) {
                return $response->withJson([
                    "status" => true,
                    "message" => "User not registered yet",
                    "data" => [
                        "user" => [
                            "id" => null,
                            "nip" => $nip,
                            "full_name" => null,
                            "username" => null,
                            "email" => null,
                            "device_id" => null,
                            "is_claim" => 0,
                            "foto" => null,
                            "last_login" => null
                        ]
                    ]
                ], 200);
            }
            if (intval($user["is_claim"]) === 1) {
                return $response->withJson([
                    "status" => false,
                    "message" => "User already claimed",
                ], 409);
            }

            // 4️⃣ Tambahkan base URL ke foto jika ada
            $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
            if ($request->getUri()->getPort()) $baseUrl .= ":" . $request->getUri()->getPort();

            if (!empty($user["foto"])) {
                $user["foto"] = $baseUrl . "/" . $user["foto"];
            } else {
                $user["foto"] = null;
            }

            // 5️⃣ Return berhasil
            return $response->withJson([
                "status" => true,
                "message" => "User found",
                "data" => ["user" => $user]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    // =========================
    // Claim endpoint
    // =========================
    $app->post("/claim", function (Request $request, Response $response) {
        try {
            $data = $request->getParsedBody();
            $nip         = $data["nip"] ?? null;
            $username    = $data["username"] ?? null;
            $passwordRaw = $data["password"] ?? null;
            $email       = $data["email"] ?? null;
            $device_id   = $data["device_id"] ?? null;

            if (!$nip || !$username || !$passwordRaw || !$device_id) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Data tidak lengkap"
                ], 400);
            }

            $db = $this->get('db_default');
            $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

            // 1️⃣ Cari user berdasarkan nip
            $sql_user = "SELECT * FROM mr_users WHERE nip = :nip LIMIT 1";
            $stmt = $db->prepare($sql_user);
            $stmt->execute(["nip" => $nip]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // CASE A: user sudah ada → update
            if ($user) {
                if ($user["is_claim"] == 1) {
                    return $response->withJson([
                        "status" => false,
                        "message" => "User ini sudah diklaim"
                    ], 409);
                }

                // Cek device dipakai user lain
                $deviceCheckSql = "SELECT id FROM mr_users WHERE device_id = :device_id AND id != :id LIMIT 1";
                $stmt = $db->prepare($deviceCheckSql);
                $stmt->execute(["device_id" => $device_id, "id" => $user["id"]]);
                if ($stmt->fetch()) {
                    return $response->withJson([
                        "status" => false,
                        "message" => "Device ini sudah digunakan oleh akun lain"
                    ], 400);
                }

                // UPDATE user
                $updateSql = "UPDATE mr_users 
                            SET username = :username, password = :password, email = :email, device_id = :device_id, is_claim = 1, updated_at = NOW()
                            WHERE id = :id";
                $stmt = $db->prepare($updateSql);
                $stmt->execute([
                    "username" => $username,
                    "password" => $password,
                    "email" => $email,
                    "device_id" => $device_id,
                    "id" => $user["id"]
                ]);

                $user["username"] = $username;
                $user["email"] = $email;
                $user["device_id"] = $device_id;
                $user["is_claim"] = 1;

                return $response->withJson([
                    "status" => true,
                    "message" => "Klaim berhasil",
                    "data" => ["user" => $user]
                ], 200);
            }

            // CASE B: user belum ada → buat baru
            $newUserId = generateUserId($db); // helper generate ID baru

            $insertSql = "INSERT INTO mr_users (id, nip, username, password, email, device_id, is_claim, created_at)
                        VALUES (:id, :nip, :username, :password, :email, :device_id, 1, NOW())";
            $stmt = $db->prepare($insertSql);
            $stmt->execute([
                "id" => $newUserId,
                "nip" => $nip,
                "username" => $username,
                "password" => $password,
                "email" => $email,
                "device_id" => $device_id
            ]);

            // Ambil user baru untuk response
            $sql_user = "SELECT * FROM mr_users WHERE id = :id LIMIT 1";
            $stmt = $db->prepare($sql_user);
            $stmt->execute(["id" => $newUserId]);
            $newUser = $stmt->fetch(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "message" => "Klaim berhasil dan akun baru dibuat",
                "data" => ["user" => $newUser]
            ], 201);

        } catch (Exception $e) {
            return $response->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    });

};
