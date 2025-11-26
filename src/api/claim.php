<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {
    
    $app->get("/claim/check", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $employee_id = $params["employee_id"] ?? null;
    
        if (!$employee_id) {
            $data = [
                "status" => false,
                "message" => "Parameter 'employee_id' diperlukan"
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader("Content-Type", "application/json")->withStatus(400);
        }
    
        $sql = "
            SELECT 
                e.id AS employee_id, 
                e.id_number,
                e.full_name,
                e.email,
                e.no_tlpn,
                e.is_claim,
                e.is_exit,
                p.id AS position_id,
                p.position_name,
                d.id AS department_id,
                d.dept_name,
                c.id AS company_id,
                c.company_name
            FROM ar_employee e
            LEFT JOIN ar_position p ON e.position_id = p.id
            LEFT JOIN ar_department d ON p.dept_id = d.id
            LEFT JOIN ar_company c ON e.company_id = c.id
            WHERE e.id = :employee_id
        ";

        $db = $this->get('db_default');
    
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(["employee_id" => $employee_id]);
            $employee = $stmt->fetch();
    
            if (!$employee) {
                $data = [
                    "status" => false,
                    "message" => "Employee ID not found"
                ];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader("Content-Type", "application/json")->withStatus(404);
            }
    
            if ($employee["is_claim"]) {
                $data = [
                    "status" => false,
                    "message" => "Employee ID has already been claimed"
                ];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader("Content-Type", "application/json")->withStatus(404);
            } else {
                $data = [
                    "status" => true,
                    "data" => [
                        "employee" => [
                            "id" => $employee["employee_id"],
                            "id_number" => $employee["id_number"],
                            "full_name" => $employee["full_name"],
                            "email" => $employee["email"],
                            "no_tlpn" => $employee["no_tlpn"],
                            "position" => [
                                "id" => $employee["position_id"],
                                "position_name" => $employee["position_name"],
                                "department" => [
                                    "id" => $employee["department_id"],
                                    "dept_name" => $employee["dept_name"]
                                ]
                            ],
                            "company" => [
                                "id" => $employee["company_id"],
                                "company_name" => $employee["company_name"]
                            ],
                            "is_claim" => $employee["is_claim"],
                            "is_exit" => $employee["is_exit"],
                        ]
                    ]
                ];
            }
    
            $response->getBody()->write(json_encode($data));
            return $response->withHeader("Content-Type", "application/json")->withStatus(200);
    
        } catch (PDOException $e) {
            $data = [
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader("Content-Type", "application/json")->withStatus(500);
        }
    });
    

    $app->post("/claim", function (Request $request, Response $response) {
        try {
            $data = $request->getParsedBody();
            error_log("Parsed body: " . print_r($data, true));
    
            $employee_id = $data["employee_id"] ?? null;
            $username = $data["username"] ?? null;
            $passwordRaw = $data["password"] ?? null;
            $email = $data["email"] ?? null;
            $no_tlpn = $data["no_tlpn"] ?? null;
            $device_id = $data["device_id"] ?? null;
    
            if (!$employee_id || !$username || !$passwordRaw || !$device_id) {
                throw new Exception("Data tidak lengkap");
            }
    
            $password = password_hash($passwordRaw, PASSWORD_BCRYPT);
            $db = $this->get('db_default');
    
            // Cek apakah employee_id valid dan belum diklaim
            $checkSql = "SELECT * FROM ar_employee WHERE id = :id AND is_claim = 0";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute(["id" => $employee_id]);
            $employee = $checkStmt->fetch();
    
            if (!$employee) {
                return $response->withJson([
                    "status" => false,
                    "message" => "ID tidak ditemukan atau sudah diklaim"
                ], 400);
            }
    
            // Cek apakah device_id sudah dipakai oleh user lain
            $deviceCheckSql = "SELECT * FROM ar_users WHERE device_id = :device_id";
            $deviceCheckStmt = $db->prepare($deviceCheckSql);
            $deviceCheckStmt->execute(["device_id" => $device_id]);
            $existing = $deviceCheckStmt->fetch();
    
            if ($existing) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Device ini sudah digunakan oleh akun lain"
                ], 400);
            }
    
            // Insert ke ar_users
            $userId = sprintf(
                "%03d-%06d-%02d",
                rand(100, 999),
                rand(100000, 999999),
                (int)substr(microtime(true) * 100, -2)
            );
    
            $insertSql = "INSERT INTO ar_users (id, username, password, employee_id, device_id) 
                          VALUES (:id, :username, :password, :employee_id, :device_id)";
            $insertStmt = $db->prepare($insertSql);
            $insert = $insertStmt->execute([
                "id" => $userId,
                "username" => $username,
                "password" => $password,
                "employee_id" => $employee_id,
                "device_id" => $device_id
            ]);
    
            if (!$insert) {
                $errorInfo = $insertStmt->errorInfo();
                error_log("Insert error: " . print_r($errorInfo, true));
                throw new Exception("Insert ke ar_users gagal");
            }
    
            // Update data di ar_employee
            $updateSql = "UPDATE ar_employee SET is_claim = 1, email = :email, no_tlpn = :no_tlpn WHERE id = :id";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([
                "email" => $email,
                "no_tlpn" => $no_tlpn,
                "id" => $employee_id
            ]);
    
            return $response->withJson([
                "status" => true,
                "message" => "Klaim berhasil"
            ], 200);
    
        } catch (Exception $e) {
            error_log("Exception caught: " . $e->getMessage());
            return $response->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    });
    
    
};
