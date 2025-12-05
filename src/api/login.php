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
            return $response->withJson([
                "status" => false,
                "message" => "Username, password, and device_id are required"
            ], 400);
        }

        $db = $this->get("db_default");

        try {
            // Ambil user
            $stmt = $db->prepare("SELECT * FROM mr_users WHERE username = :username LIMIT 1");
            $stmt->execute(["username" => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Username or password not found"
                ], 400);
            }

            // Validasi password
            if (!password_verify($password, $user["password"])) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Incorrect password"
                ], 400);
            }

            // Validasi device_id
            if (empty($user["device_id"])) {
                $update = $db->prepare("UPDATE mr_users SET device_id = :device_id WHERE id = :id");
                $update->execute([
                    "device_id" => $device_id,
                    "id" => $user["id"]
                ]);
                $user["device_id"] = $device_id;
            } elseif ($user["device_id"] !== $device_id) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Account already bound to another device"
                ], 400);
            }

            // Update player_id jika dikirim dan berbeda
            if ($player_id && $player_id !== $user["player_id"]) {
                $updatePlayer = $db->prepare("UPDATE mr_users SET player_id = :player_id WHERE id = :id");
                $updatePlayer->execute([
                    "player_id" => $player_id,
                    "id" => $user["id"]
                ]);
                $user["player_id"] = $player_id;
            }

            unset($user["password"]); // jangan kirim password

            // Ambil data SDM berdasarkan user_id
            $stmtSdm = $db->prepare("SELECT * FROM mr_sdm WHERE user_id = :user_id LIMIT 1");
            $stmtSdm->execute(["user_id" => $user["id"]]);
            $sdm = $stmtSdm->fetch(PDO::FETCH_ASSOC);

            if ($sdm) {
                // Tambahkan base URL untuk foto SDM
                $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
                if ($request->getUri()->getPort()) $baseUrl .= ":" . $request->getUri()->getPort();
                if (!empty($sdm["foto"])) $sdm["foto"] = $baseUrl . "/" . $sdm["foto"];

                $user["sdm"] = $sdm;
            } else {
                $user["sdm"] = null;
            }

            return $response->withJson([
                "status" => true,
                "message" => "Login successful",
                "data" => ["user" => $user]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


};
