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
            // 1️⃣ Cari SDM
            $sql_sdm = "SELECT * FROM mr_sdm WHERE user_id IN (SELECT id FROM mr_users WHERE nip = :nip) LIMIT 1";
            $stmt = $db->prepare($sql_sdm);
            $stmt->execute(["nip" => $nip]);
            $sdm = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sdm) {
                return $response->withJson([
                    "status" => false,
                    "message" => "SDM not found"
                ], 404);
            }

            // 2️⃣ Cari user berdasarkan sdm->user_id
            $user = null;
            if (!empty($sdm["user_id"])) {
                $sql_user = "SELECT * FROM mr_users WHERE id = :id LIMIT 1";
                $stmt = $db->prepare($sql_user);
                $stmt->execute(["id" => $sdm["user_id"]]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Base URL untuk foto
            $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
            if ($request->getUri()->getPort()) $baseUrl .= ":" . $request->getUri()->getPort();
            if (!empty($user["foto"])) $user["foto"] = $baseUrl . "/" . $user["foto"];

            // Jika user sudah klaim
            if ($user && intval($user["is_claim"]) === 1) {
                return $response->withJson([
                    "status" => false,
                    "message" => "SDM already claimed by another user"
                ], 409);
            }

            // Default user structure
            if (!$user) {
                $user = [
                    "id" => null,
                    "nip" => $nip,
                    "full_name" => null,
                    "username" => null,
                    "email" => null,
                    "device_id" => null,
                    "is_claim" => 0,
                    "foto" => null,
                    "last_login" => null,
                ];
            }

            // Gabungkan SDM
            $user["sdm"] = $sdm;

            return $response->withJson([
                "status" => true,
                "message" => "SDM found",
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

            // 1️⃣ Cari SDM
            $sql_sdm = "SELECT * FROM mr_sdm WHERE user_id IN (SELECT id FROM mr_users WHERE nip = :nip) LIMIT 1";
            $stmt = $db->prepare($sql_sdm);
            $stmt->execute(["nip" => $nip]);
            $sdm = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sdm) {
                return $response->withJson([
                    "status" => false,
                    "message" => "SDM not found"
                ], 404);
            }

            // 2️⃣ Cari user berdasarkan sdm->user_id
            $user = null;
            if (!empty($sdm["user_id"])) {
                $sql_user = "SELECT * FROM mr_users WHERE id = :id LIMIT 1";
                $stmt = $db->prepare($sql_user);
                $stmt->execute(["id" => $sdm["user_id"]]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // CASE A: user sudah ada → update
            if ($user) {
                if ($user["is_claim"] == 1) {
                    return $response->withJson([
                        "status" => false,
                        "message" => "SDM ini sudah diklaim oleh user lain"
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
            $newUserId = generateUserId($db);

            $insertSql = "INSERT INTO mr_users (id, nip, full_name, username, password, email, device_id, is_claim, created_at)
                          VALUES (:id, :nip, :full_name, :username, :password, :email, :device_id, 1, NOW())";
            $stmt = $db->prepare($insertSql);
            $stmt->execute([
                "id" => $newUserId,
                "nip" => $nip,
                "full_name" => $sdm["nama_lengkap"] ?? "",
                "username" => $username,
                "password" => $password,
                "email" => $email,
                "device_id" => $device_id
            ]);

            // Hubungkan SDM ke user baru
            $updateSdmSql = "UPDATE mr_sdm SET user_id = :user_id WHERE id = :sdm_id";
            $stmt = $db->prepare($updateSdmSql);
            $stmt->execute(["user_id" => $newUserId, "sdm_id" => $sdm["id"]]);

            // Ambil user baru untuk response
            $sql_user = "SELECT * FROM mr_users WHERE id = :id LIMIT 1";
            $stmt = $db->prepare($sql_user);
            $stmt->execute(["id" => $newUserId]);
            $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
            $newUser["sdm"] = $sdm;

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
