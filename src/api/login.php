<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function ($app) {
    $app->post('/login', function (Request $request, Response $response) {
        $input = $request->getParsedBody();
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        $device_id = $input['device_id'] ?? '';
        $player_id = $input['player_id'] ?? null; // Ambil player_id

        if (empty($username) || empty($password) || empty($device_id)) {
            $data = [
                'status' => false,
                'message' => 'Username, password, and device_id are required'
            ];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $db = $this->get('db_default');
        $sql = "SELECT * FROM ar_users WHERE username = :username LIMIT 1";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $data = [
                    'status' => false,
                    'message' => 'Username or password not found'
                ];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            if ($user['status'] == 0 || strtolower($user['status']) === 'inactive') {
                $data = [
                    'status' => false,
                    'message' => 'User is inactive'
                ];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            if (!password_verify($password, $user['password'])) {
                $data = [
                    'status' => false,
                    'message' => 'Incorrect password'
                ];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // ✅ Validasi atau set device_id
            if (empty($user['device_id'])) {
                $updateSql = "UPDATE ar_users SET device_id = :device_id WHERE id = :id";
                $stmtUpdate = $db->prepare($updateSql);
                $stmtUpdate->execute([
                    'device_id' => $device_id,
                    'id' => $user['id']
                ]);
                $user['device_id'] = $device_id;
            } else {
                if ($user['device_id'] !== $device_id) {
                    $data = [
                        'status' => false,
                        'message' => 'Account already bound to another device'
                    ];
                    $response->getBody()->write(json_encode($data));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
            }

            unset($user['password']);
            $employee_id = $user['employee_id'] ?? null;
            $employeeData = null;

            if ($employee_id) {
                $stmtEmp = $db->prepare("SELECT * FROM ar_employee WHERE id = :employee_id LIMIT 1");
                $stmtEmp->execute(['employee_id' => $employee_id]);
                $employeeData = $stmtEmp->fetch(PDO::FETCH_ASSOC);

                if ($employeeData) {
                    // Simpan player_id jika tersedia
                    if ($player_id) {
                        $updatePlayerSql = "UPDATE ar_employee SET player_id = :player_id WHERE id = :employee_id";
                        $stmtPlayer = $db->prepare($updatePlayerSql);
                        $stmtPlayer->execute([
                            'player_id' => $player_id,
                            'employee_id' => $employee_id
                        ]);
                        $employeeData['player_id'] = $player_id;
                    }

                    $stmtPosition = $db->prepare("SELECT * FROM ar_position WHERE id = :position_id LIMIT 1");
                    $stmtPosition->execute(['position_id' => $employeeData['position_id']]);
                    $positionData = $stmtPosition->fetch(PDO::FETCH_ASSOC);

                    $stmtDept = $db->prepare("SELECT * FROM ar_department WHERE id = :dept_id LIMIT 1");
                    $stmtDept->execute(['dept_id' => $positionData['dept_id']]);
                    $departmentData = $stmtDept->fetch(PDO::FETCH_ASSOC);

                    $stmtCompany = $db->prepare("SELECT * FROM ar_company WHERE id = :company_id LIMIT 1");
                    $stmtCompany->execute(['company_id' => $employeeData['company_id']]);
                    $companyData = $stmtCompany->fetch(PDO::FETCH_ASSOC);

                    $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
                    $port = $request->getUri()->getPort();
                    if ($port) {
                        $baseUrl .= ":" . $port;
                    }

                    $photoUrl = !empty($employeeData['photo']) ? $baseUrl . "/" . $employeeData['photo'] : null;
                    $employeeData['photo'] = $photoUrl;

                    $employeeData['position'] = [
                        'id' => $positionData['id'],
                        'position_name' => $positionData['position_name'],
                        'department' => [
                            'id' => $departmentData['id'],
                            'dept_name' => $departmentData['dept_name']
                        ]
                    ];

                    $employeeData['company'] = [
                        'id' => $companyData['id'],
                        'company_name' => $companyData['company_name']
                    ];

                    $employeeData['status'] = $user['status'];
                    $employeeData['device_id'] = $user['device_id'];

                    unset($employeeData['position_id']);
                    unset($employeeData['company_id']);
                }
            }

            $data = [
                'status' => true,
                'data' => [
                    'employee' => $employeeData ?: null
                ]
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
