<?php
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (\Slim\App $app) {

    $app->post('/sdm/certificate/add', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $post = $request->getParsedBody();
            $files = $request->getUploadedFiles();

            if (!$post || !isset($post['sdm_id'])) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "sdm_id wajib diisi"
                ]);
            }

            $sdmId = $post['sdm_id'];

            // Validasi input fields
            $required = ['nama_sertifikat', 'no_sertifikat', 'tanggal_terbit', 'tanggal_expiry'];
            foreach ($required as $field) {
                if (!isset($post[$field]) || $post[$field] === '') {
                    return $response->withStatus(400)->withJson([
                        "status" => false,
                        "message" => "Field '$field' wajib diisi"
                    ]);
                }
            }

            // Validasi file
            if (!isset($files['file_sertifikat'])) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "File sertifikat wajib diunggah"
                ]);
            }

            $uploadedFile = $files['file_sertifikat'];
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "Gagal mengunggah file"
                ]);
            }

            $ext = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'pdf') {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "Hanya file PDF yang diperbolehkan"
                ]);
            }

            // Simpan file
            $uploadDir = dirname(__DIR__, 2) . '/uploads/certificate/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = uniqid('cert_') . '.' . $ext;
            $uploadedFile->moveTo($uploadDir . $fileName);

            // Insert ke database
            $stmt = $db->prepare("
                INSERT INTO mr_sertifikasi (
                    id, sdm_id, nama_sertifikat, no_sertifikat,
                    tanggal_terbit, tanggal_expiry, file_sertifikat, status, reminder_sent, created_at
                ) VALUES (
                    :id, :sdm_id, :nama_sertifikat, :no_sertifikat,
                    :tanggal_terbit, :tanggal_expiry, :file_sertifikat, 1, 0, NOW()
                )
            ");

            $stmt->execute([
                ':id' => generateCertificateId($db),
                ':sdm_id' => $sdmId,
                ':nama_sertifikat' => $post['nama_sertifikat'],
                ':no_sertifikat' => $post['no_sertifikat'],
                ':tanggal_terbit' => date('Y-m-d', strtotime($post['tanggal_terbit'])),
                ':tanggal_expiry' => date('Y-m-d', strtotime($post['tanggal_expiry'])),
                ':file_sertifikat' => $fileName,
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Sertifikat berhasil ditambahkan"
            ]);

        } catch (Exception $e) {
            return $response->withStatus(500)->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    });


    $app->post('/sdm/certificate/update', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $post = $request->getParsedBody();
            $files = $request->getUploadedFiles();

            if (!$post || !isset($post['sertifikat_id'])) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "sertifikat_id wajib diisi"
                ]);
            }

            $id = $post['sertifikat_id'];

            // Ambil data lama
            $stmt = $db->prepare("SELECT file_sertifikat FROM mr_sertifikasi WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $oldData = $stmt->fetch();

            if (!$oldData) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Data sertifikat tidak ditemukan"
                ]);
            }

            $oldFile = $oldData['file_sertifikat'];

            // Validasi field
            $required = ['nama_sertifikat', 'no_sertifikat', 'tanggal_terbit', 'tanggal_expiry'];
            foreach ($required as $field) {
                if (!isset($post[$field]) || $post[$field] === "") {
                    return $response->withStatus(400)->withJson([
                        "status" => false,
                        "message" => "Field '$field' wajib diisi"
                    ]);
                }
            }

            // Handle file
            $newFileName = $oldFile;

            if (isset($files['file_sertifikat'])) {
                $uploadedFile = $files['file_sertifikat'];

                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $ext = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

                    if (strtolower($ext) !== 'pdf') {
                        return $response->withJson([
                            "status" => false,
                            "message" => "Hanya file PDF yang diperbolehkan"
                        ]);
                    }

                    $uploadDir = dirname(__DIR__, 2) . '/uploads/certificate/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                    $newFileName = uniqid('cert_') . "." . $ext;
                    $uploadedFile->moveTo($uploadDir . $newFileName);

                    // Hapus file lama
                    if ($oldFile && file_exists($uploadDir . $oldFile)) {
                        @unlink($uploadDir . $oldFile);
                    }
                }
            }

            // UPDATE tanpa koma !
            $stmt = $db->prepare("
                UPDATE mr_sertifikasi SET
                    nama_sertifikat = :nama_sertifikat,
                    no_sertifikat = :no_sertifikat,
                    tanggal_terbit = :tanggal_terbit,
                    tanggal_expiry = :tanggal_expiry,
                    file_sertifikat = :file_sertifikat
                WHERE id = :id
            ");

            $stmt->execute([
                ':id' => $id,
                ':nama_sertifikat' => $post['nama_sertifikat'],
                ':no_sertifikat' => $post['no_sertifikat'],
                ':tanggal_terbit' => date('Y-m-d', strtotime($post['tanggal_terbit'])),
                ':tanggal_expiry' => date('Y-m-d', strtotime($post['tanggal_expiry'])),
                ':file_sertifikat' => $newFileName
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Sertifikat berhasil diperbarui"
            ]);

        } catch (Exception $e) {
            return $response->withStatus(500)->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    });



    $app->delete('/sdm/certificate/delete', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            // Ambil body
            $post = $request->getParsedBody();

            if (!$post || !isset($post['sertifikat_id'])) {
                return $response->withStatus(400)->withJson([
                    "status" => false,
                    "message" => "sertifikat_id wajib diisi"
                ]);
            }

            $id = $post['sertifikat_id'];

            // Ambil data lama untuk cek file
            $stmt = $db->prepare("SELECT file_sertifikat FROM mr_sertifikasi WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();

            if (!$data) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Data sertifikat tidak ditemukan"
                ]);
            }

            $file = $data['file_sertifikat'];
            $uploadDir = dirname(__DIR__, 2) . '/uploads/certificate/';

            // Delete record
            $stmt = $db->prepare("DELETE FROM mr_sertifikasi WHERE id = :id");
            $stmt->execute([':id' => $id]);

            // Hapus file fisik jika ada
            if ($file && file_exists($uploadDir . $file)) {
                @unlink($uploadDir . $file);
            }

            return $response->withJson([
                "status" => true,
                "message" => "Sertifikat berhasil dihapus"
            ]);

        } catch (Exception $e) {
            return $response->withStatus(500)->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ]);
        }
    });



};
