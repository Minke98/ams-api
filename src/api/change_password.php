<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {
    $app->post('/change-password', function (Request $request, Response $response) {
        $input = $request->getParsedBody();
        $employeeId = $input['employee_id'] ?? '';
        $oldPassword = $input['old_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
    
        if (empty($employeeId) || empty($oldPassword) || empty($newPassword)) {
            $data = [
                'status' => false,
                'message' => 'Employee ID, old password, and new password are required'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        // PILIH DATABASE YANG MAU DIPAKAI:
        $db = $this->get('db_default'); // <- default
        // $db = $this->get('db_second'); // <- kalau mau pakai database kedua
    
        $sql = "SELECT * FROM ar_users WHERE employee_id = :employee_id LIMIT 1";
    
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['employee_id' => $employeeId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                $data = [
                    'status' => false,
                    'message' => 'User not found with this employee ID'
                ];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
    
            if (!password_verify($oldPassword, $user['password'])) {
                $data = [
                    'status' => false,
                    'message' => 'Incorrect old password'
                ];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
    
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
            $updateSql = "UPDATE ar_users SET password = :new_password WHERE employee_id = :employee_id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([
                'new_password' => $hashedPassword,
                'employee_id' => $employeeId
            ]);
    
            $data = [
                'status' => true,
                'message' => 'Password successfully updated'
            ];
    
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
        } catch (PDOException $e) {
            $data = [
                'status' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
    
};

