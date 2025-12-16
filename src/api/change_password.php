<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {
    
    $app->post('/change-password', function (Request $request, Response $response) {
        $input = $request->getParsedBody();

        $user_id      = $input['user_id'] ?? '';
        $oldPassword  = $input['old_password'] ?? '';
        $newPassword  = $input['new_password'] ?? '';

        // Validasi input
        if (empty($user_id) || empty($oldPassword) || empty($newPassword)) {
            return $response->withJson([
                'status' => false,
                'message' => 'User ID, old password, dan new password wajib diisi'
            ], 400);
        }

        $db = $this->get('db_default');

        try {
            // Ambil password lama dari DB
            $stmt = $db->prepare("SELECT password FROM mr_users WHERE id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return $response->withJson([
                    'status' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Verifikasi old password
            if (!password_verify($oldPassword, $user['password'])) {
                return $response->withJson([
                    'status' => false,
                    'message' => 'Password lama salah'
                ], 401);
            }

            // Cek apakah new password sama dengan password lama
            if (password_verify($newPassword, $user['password'])) {
                return $response->withJson([
                    'status' => false,
                    'message' => 'Password baru tidak boleh sama dengan password lama'
                ], 400);
            }

            // Hash password baru
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password
            $updateSql = "UPDATE mr_users SET password = :new_password WHERE id = :user_id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([
                'new_password' => $hashedPassword,
                'user_id' => $user_id
            ]);

            return $response->withJson([
                'status' => true,
                'message' => 'Password berhasil diubah'
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                'status' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ], 500);
        }
    });


    
};

