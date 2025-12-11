<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->post("/change-photo", function (Request $request, Response $response) {
        $params = $request->getParsedBody(); // untuk field text
        $uploadedFiles = $request->getUploadedFiles(); // untuk file

        $user_id = $params['user_id'] ?? null; // pastikan field ini dikirim di form-data
        if (!$user_id) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'user_id' diperlukan"
            ], 400);
        }

        $full_name = $params['full_name'] ?? null;
        $email = $params['email'] ?? null;

        $foto = $uploadedFiles['foto'] ?? null;
        $fotoPath = null;

        if ($foto && $foto->getError() === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($foto->getClientMediaType(), $allowedTypes)) {
                return $response->withJson([
                    'status' => false,
                    'message' => 'Hanya file gambar (JPEG, PNG, GIF) yang diperbolehkan'
                ], 400);
            }

            $directory = __DIR__ . '/../../public/uploads/photos/';
            if (!is_dir($directory)) mkdir($directory, 0777, true);

            $filename = $user_id . "-" . $foto->getClientFilename();
            $foto->moveTo($directory . $filename);
            $fotoPath = "/uploads/photos/" . $filename;
        }

        $db = $this->get('db_default');
        $fields = [];
        $paramsToUpdate = ['id' => $user_id];

        if ($full_name !== null) {
            $fields[] = "full_name = :full_name";
            $paramsToUpdate['full_name'] = $full_name;
        }
        if ($email !== null) {
            $fields[] = "email = :email";
            $paramsToUpdate['email'] = $email;
        }
        if ($fotoPath !== null) {
            $fields[] = "foto = :foto";
            $paramsToUpdate['foto'] = $fotoPath;
        }

        if (empty($fields)) {
            return $response->withJson([
                "status" => false,
                "message" => "Tidak ada data untuk diupdate"
            ], 400);
        }

        $sql = "UPDATE mr_users SET " . implode(", ", $fields) . " WHERE id = :id";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($paramsToUpdate);

            return $response->withJson([
                "status" => true,
                "message" => "Data berhasil diperbarui",
                "data" => [
                    "full_name" => $full_name,
                    "email" => $email,
                    "foto" => $fotoPath
                ]
            ], 200);
        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });

    

};
