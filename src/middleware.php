<?php

return function ($app) {
    // middleware API key validation
    $app->add(function ($request, $response, $next) use ($app) {
        // Ambil API Key dari query parameter
        $key = $request->getQueryParam("key");

        if (!isset($key)) {
            return $response->withJson(["status" => "API Key required"], 401);
        }

        // Menggunakan db_default untuk mengecek keberadaan API Key
        $db = $app->getContainer()->get('db_default');
        $sql = "SELECT * FROM ar_api_users WHERE api_key = :api_key";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([":api_key" => $key]);

            // Cek apakah API Key ditemukan
            if ($stmt->rowCount() > 0) {
                // Update hit count jika API key valid
                $sql = "UPDATE ar_api_users SET hit = hit + 1 WHERE api_key = :api_key";
                $stmt = $db->prepare($sql);
                $stmt->execute([":api_key" => $key]);

                // Lanjutkan eksekusi request
                return $next($request, $response);
            }
        } catch (PDOException $e) {
            // Tangani error database
            return $response->withJson(["status" => "Database error: " . $e->getMessage()], 500);
        }

        // Jika API Key tidak valid
        return $response->withJson(["status" => "Unauthorized"], 401);
    });
};

