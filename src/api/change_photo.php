<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->post("/change-photo", function (Request $request, Response $response) {
        $params = $request->getParsedBody();
        $employee_id = $params["employee_id"] ?? null;
        
        // Periksa jika parameter employee_id ada
        if (!$employee_id) {
            $data = [
                "status" => false,
                "message" => "Parameter 'employee_id' diperlukan"
            ];
            return $response->withJson($data, 400);
        }
    
        // Periksa jika file foto di-upload
        $uploadedFiles = $request->getUploadedFiles();
        $photo = $uploadedFiles['photo'] ?? null;
        
        if (!$photo || $photo->getError() !== UPLOAD_ERR_OK) {
            $data = [
                "status" => false,
                "message" => "File foto tidak ditemukan atau terjadi kesalahan dalam upload"
            ];
            return $response->withJson($data, 400);
        }
    
        // Validasi jenis file (hanya gambar yang diperbolehkan)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $photo->getClientMediaType();
        if (!in_array($fileType, $allowedTypes)) {
            $data = [
                'status' => false,
                'message' => 'Hanya file gambar (JPEG, PNG, GIF) yang diperbolehkan'
            ];
            return $response->withJson($data, 400);
        }
    
        // Validasi ukuran file (maksimal 20MB)
        $maxSize = 20 * 1024 * 1024; // 20MB
        if ($photo->getSize() > $maxSize) {
            $data = [
                'status' => false,
                'message' => 'Ukuran file terlalu besar. Maksimal 5MB.'
            ];
            return $response->withJson($data, 400);
        }
    
        // Tentukan path untuk menyimpan foto di folder public/uploads/photos/
        $directory =  __DIR__ . '/../../public/uploads/photos/';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    
        // Simpan file foto ke direktori tujuan
        $filename = $employee_id . "-" . $photo->getClientFilename();
        $filePath = $directory . $filename;
        
        // Pindahkan file foto dari temporary path ke lokasi yang diinginkan
        $photo->moveTo($filePath);

        $db = $this->get('db_default');
    
        // Update foto di database
        try {
            $sql = "UPDATE ar_employee SET photo = :photo WHERE id = :employee_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                "photo" => "uploads/photos/" . $filename, // URL relatif untuk akses foto
                "employee_id" => $employee_id
            ]);
            
            // Jika berhasil
            $data = [
                "status" => true,
                "message" => "Foto berhasil diubah",
                "data" => [
                    "photo" => $filename
                ]
            ];
            return $response->withJson($data, 200);
            
        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });
    
    

    // $app->post('/change-photo', function (Request $request, Response $response) {
    //     try {
    //         $parsedBody = $request->getParsedBody();
    //         $employee_id = $parsedBody['employee_id'] ?? null;
    
    //         if (!$employee_id) {
    //             return $response->withJson([
    //                 "status" => "error",
    //                 "message" => "Employee ID diperlukan"
    //             ], 400);
    //         }
    
    //         $uploadedFiles = $request->getUploadedFiles();
    //         $photo = $uploadedFiles['photo'] ?? null;
    
    //         // Fallback kalau ga ada file, coba baca dari body (kalau memang dikirim pakai base64/file path)
    //         if (!$photo || $photo->getError() !== UPLOAD_ERR_OK) {
    //             $photoField = $parsedBody['photo'] ?? null;
    
    //             if (!$photoField) {
    //                 return $response->withJson([
    //                     "status" => "error",
    //                     "message" => "Foto tidak ditemukan"
    //                 ], 400);
    //             }
    
    //             // Coba decode base64
    //             if (preg_match('/^data:image\/(\w+);base64,/', $photoField, $type)) {
    //                 $data = substr($photoField, strpos($photoField, ',') + 1);
    //                 $data = base64_decode($data);
    //                 $extension = strtolower($type[1]);
    //             } else {
    //                 return $response->withJson([
    //                     "status" => "error",
    //                     "message" => "Format foto base64 tidak valid"
    //                 ], 400);
    //             }
    
    //             // Simpan file
    //             $uploadDir = __DIR__ . '/../../public/uploads/';
    //             if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    //             $filename = "photo_{$employee_id}_" . time() . ".{$extension}";
    //             $filePath = $uploadDir . $filename;
    //             file_put_contents($filePath, $data);
    
    //             $relativePath = "uploads/" . $filename;
    //         } else {
    //             // Proses file upload seperti biasa
    //             $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    //             $fileMime = $photo->getClientMediaType();
    
    //             if (!in_array($fileMime, $allowedMimeTypes)) {
    //                 return $response->withJson([
    //                     "status" => "error",
    //                     "message" => "File harus berupa gambar (jpg, jpeg, png, webp)"
    //                 ], 400);
    //             }
    
    //             $uploadDir = __DIR__ . '/../../public/uploads/';
    //             if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    //             $filename = sprintf("photo_%s_%s", $employee_id, $photo->getClientFilename());
    //             $filePath = $uploadDir . $filename;
    //             $photo->moveTo($filePath);
    
    //             $relativePath = "uploads/" . $filename;
    //         }
    
    //         $sql = "UPDATE ar_employee SET photo = :photo WHERE id = :id";
    //         $stmt = $this->db->prepare($sql);
    //         $stmt->execute([
    //             'photo' => $relativePath,
    //             'id' => $employee_id
    //         ]);
    
    //         return $response->withJson([
    //             "status" => "success",
    //             "message" => "Foto berhasil diperbarui",
    //             "photo_url" => $relativePath
    //         ], 200);
    
    //     } catch (Exception $e) {
    //         error_log("Error updating photo: " . $e->getMessage());
    //         return $response->withJson([
    //             "status" => "error",
    //             "message" => $e->getMessage()
    //         ], 500);
    //     }
    // });
    

};
