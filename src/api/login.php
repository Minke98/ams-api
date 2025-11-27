<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->post('/login', function (Request $request, Response $response) {
        $input = $request->getParsedBody();
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        $device_id = $input['device_id'] ?? '';
        $player_id = $input['player_id'] ?? null;

        if (empty($username) || empty($password) || empty($device_id)) {
            $response->getBody()->write(json_encode([
                "status" => false,
                "message" => "Username, password, and device_id are required"
            ]));
            return $response->withHeader('Content-Type','application/json')->withStatus(400);
        }

        $db = $this->get("db_default");

        try {
            // 1️⃣ Ambil user dari mr_users
            $stmt = $db->prepare("SELECT * FROM mr_users WHERE username = :username LIMIT 1");
            $stmt->execute(["username" => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $response->getBody()->write(json_encode([
                    "status" => false,
                    "message" => "Username or password not found"
                ]));
                return $response->withHeader('Content-Type','application/json')->withStatus(400);
            }

            // 2️⃣ Validasi password
            if (!password_verify($password, $user["password"])) {
                $response->getBody()->write(json_encode([
                    "status" => false,
                    "message" => "Incorrect password"
                ]));
                return $response->withHeader('Content-Type','application/json')->withStatus(400);
            }

            // 3️⃣ Validasi / Set device_id
            if (empty($user["device_id"])) {
                $update = $db->prepare("UPDATE mr_users SET device_id = :device_id WHERE id = :id");
                $update->execute([
                    "device_id" => $device_id,
                    "id" => $user["id"]
                ]);
                $user["device_id"] = $device_id;
            } else {
                if ($user["device_id"] !== $device_id) {
                    $response->getBody()->write(json_encode([
                        "status" => false,
                        "message" => "Account already bound to another device"
                    ]));
                    return $response->withHeader('Content-Type','application/json')->withStatus(400);
                }
            }

            // 4️⃣ Update player_id bila dikirim
            if ($player_id && $player_id !== $user["player_id"]) {
                $updatePlayer = $db->prepare("UPDATE mr_users SET player_id = :player_id WHERE id = :id");
                $updatePlayer->execute([
                    "player_id" => $player_id,
                    "id" => $user["id"]
                ]);
                $user["player_id"] = $player_id;
            }

            unset($user["password"]);

            // 5️⃣ Ambil SEMUA data SDM dari mr_sdm
            $sdm_id = $user["sdm_id"] ?? null;
            if ($sdm_id) {
                $stmtSdm = $db->prepare("SELECT * FROM mr_sdm WHERE id = :id LIMIT 1");
                $stmtSdm->execute(["id" => $sdm_id]);
                $sdmData = $stmtSdm->fetch(PDO::FETCH_ASSOC);

                // Tambahkan base URL untuk foto
                if ($sdmData) {
                    $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
                    if ($request->getUri()->getPort()) {
                        $baseUrl .= ":" . $request->getUri()->getPort();
                    }

                    if (!empty($sdmData["foto"])) {
                        $sdmData["foto"] = $baseUrl . "/" . $sdmData["foto"];
                    }

                    // Masukkan SDM ke dalam user
                    $user["sdm"] = $sdmData;
                }
            }

            // 6️⃣ Response final
            $response->getBody()->write(json_encode([
                "status" => true,
                "message" => "Login successful",
                "data" => [
                    "user" => $user
                ]
            ]));

            return $response->withHeader('Content-Type','application/json')->withStatus(200);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type','application/json')->withStatus(500);
        }
    });

};
