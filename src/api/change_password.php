<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {
    
    $app->post('/change-password', function (Request $request, Response $response) {
        $input = $request->getParsedBody();
        $user_id = $input['user_id'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (empty($user_id) || empty($newPassword)) {
            return $response->withJson([
                'status' => false,
                'message' => 'User ID dan new password diperlukan'
            ], 400);
        }

        $db = $this->get('db_default');

        try {
            // Hash password baru
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update password langsung
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

