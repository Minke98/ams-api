<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get("/employee", function (Request $request, Response $response) {
        $params = $request->getQueryParams();
        $employee_id = $params["employee_id"] ?? null;

        if (!$employee_id) {
            $data = [
                "status" => false,
                "message" => "Parameter 'employee_id' diperlukan"
            ];
            return $response->withJson($data, 400);
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
                e.photo,
                p.id AS position_id,
                p.position_name,
                d.id AS department_id,
                d.dept_name,
                c.id AS company_id,
                c.company_name,
                u.status
            FROM ar_employee e
            LEFT JOIN ar_position p ON e.position_id = p.id
            LEFT JOIN ar_department d ON p.dept_id = d.id
            LEFT JOIN ar_company c ON e.company_id = c.id
            LEFT JOIN ar_users u ON u.employee_id = e.id
            WHERE e.id = :employee_id
        ";

        $db = $this->get('db_default');

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(["employee_id" => $employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employee) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Employee ID not found"
                ], 404);
            }

            // Base URL untuk foto
            $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            if ($port) {
                $baseUrl .= ":" . $port;
            }

            $photoUrl = $employee["photo"] ? $baseUrl . "/" . $employee["photo"] : null;

            $data = [
                "status" => true,
                "data" => [
                    "employee" => [
                        "id" => $employee["employee_id"],
                        "id_number" => $employee["id_number"],
                        "full_name" => $employee["full_name"],
                        "email" => $employee["email"],
                        "no_tlpn" => $employee["no_tlpn"],
                        "photo" => $photoUrl,
                        "is_claim" => $employee["is_claim"],
                        "is_exit" => $employee["is_exit"],
                        "status" => $employee["status"], // ← status user disatukan di employee
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
                        ]
                    ]
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


    $app->get("/employee/dropdown", function (Request $request, Response $response) {
        $db = $this->get('db_default');

        $type = strtolower($request->getQueryParams()['type'] ?? 'all'); // selalu lowercase

        try {
            $sql = "
                SELECT 
                    e.id AS employee_id,
                    e.full_name,
                    TRIM(d.id) AS dept_id
                FROM ar_employee e
                LEFT JOIN ar_position p ON e.position_id = p.id
                LEFT JOIN ar_department d ON p.dept_id = d.id
                ORDER BY e.full_name ASC
            ";

            $stmt = $db->query($sql);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result = [];

            foreach ($employees as $emp) {
                $deptId = $emp['dept_id'];

                if ($type === 'sales' && $deptId === 'DEP000003') {
                    $result[] = [
                        "id" => $emp["employee_id"],
                        "name" => $emp["full_name"]
                    ];
                } elseif ($type === 'technician' && $deptId === 'DEP000004') {
                    $result[] = [
                        "id" => $emp["employee_id"],
                        "name" => $emp["full_name"]
                    ];
                } elseif ($type === 'all') {
                    $result[] = [
                        "id" => $emp["employee_id"],
                        "name" => $emp["full_name"],
                        "dept" => $deptId
                    ];
                }
            }


            return $response->withJson([
                "status" => true,
                "data" => $result
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    $app->get("/employee/list", function (Request $request, Response $response) {
        $db = $this->get('db_default');

        $sql = "
            SELECT 
                e.id AS employee_id,
                e.id_number,
                e.full_name,
                e.email,
                e.no_tlpn,
                e.is_claim,
                e.is_exit,
                e.photo,
                p.id AS position_id,
                p.position_name,
                d.id AS department_id,
                d.dept_name,
                c.id AS company_id,
                c.company_name,
                u.status
            FROM ar_employee e
            LEFT JOIN ar_position p ON e.position_id = p.id
            LEFT JOIN ar_department d ON p.dept_id = d.id
            LEFT JOIN ar_company c ON e.company_id = c.id
            LEFT JOIN ar_users u ON u.employee_id = e.id
            ORDER BY e.full_name ASC
        ";

        try {
            $stmt = $db->query($sql);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            if ($port) {
                $baseUrl .= ":" . $port;
            }

            // Map data untuk menambahkan full URL photo
            $result = array_map(function($emp) use ($baseUrl) {
                $emp['photo'] = $emp['photo'] ? $baseUrl . '/' . $emp['photo'] : null;
                return [
                    "id" => $emp['employee_id'],
                    "id_number" => $emp['id_number'],
                    "full_name" => $emp['full_name'],
                    "email" => $emp['email'],
                    "no_tlpn" => $emp['no_tlpn'],
                    "photo" => $emp['photo'],
                    "is_claim" => $emp['is_claim'],
                    "is_exit" => $emp['is_exit'],
                    "status" => $emp['status'],
                    "position" => [
                        "id" => $emp['position_id'],
                        "position_name" => $emp['position_name'],
                        "department" => [
                            "id" => $emp['department_id'],
                            "dept_name" => $emp['dept_name']
                        ]
                    ],
                    "company" => [
                        "id" => $emp['company_id'],
                        "company_name" => $emp['company_name']
                    ]
                ];
            }, $employees);

            return $response->withJson([
                "status" => true,
                "data" => $result
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });



    $app->post("/employee/add", function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $body = $request->getParsedBody();

        // Ambil data dari request body
        $id_number   = $body['id_number'] ?? null;
        $full_name   = $body['full_name'] ?? null;
        $position_id = $body['position_id'] ?? null;
        $company_id  = $body['company_id'] ?? null;

        // Validasi field wajib
        if (!$id_number || !$full_name || !$position_id || !$company_id) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'id_number', 'full_name', 'position_id', dan 'company_id' diperlukan"
            ], 400);
        }

        // Ambil 3 digit pertama sebelum titik (contoh: 013.4.0266.21 -> EMP013)
        $parts = explode('.', $id_number);
        $firstPart = $parts[0] ?? $id_number;
        $employee_id = "EMP" . $firstPart;

        try {
            // Cek apakah ID sudah ada
            $checkStmt = $db->prepare("SELECT id FROM ar_employee WHERE id = :id");
            $checkStmt->execute(["id" => $employee_id]);
            if ($checkStmt->fetch()) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Employee dengan ID ini sudah ada."
                ], 409);
            }

            // Simpan data ke database
            $sql = "
                INSERT INTO ar_employee 
                (id, id_number, full_name, position_id, company_id, is_claim, is_exit)
                VALUES 
                (:id, :id_number, :full_name, :position_id, :company_id, 0, 0)
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                "id" => $employee_id,
                "id_number" => $id_number,
                "full_name" => $full_name,
                "position_id" => $position_id,
                "company_id" => $company_id,
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Employee berhasil ditambahkan",
                "data" => [
                    "employee_id" => $employee_id,
                    "is_claim" => 0
                ]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });

    $app->put("/employee/update", function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $body = $request->getParsedBody();

        // Ambil data dari request body
        $employee_id = $body['employee_id'] ?? null;
        $id_number   = $body['id_number'] ?? null;
        $full_name   = $body['full_name'] ?? null;
        $position_id = $body['position_id'] ?? null;
        $company_id  = $body['company_id'] ?? null;

        // Validasi field wajib
        if (!$employee_id || !$id_number || !$full_name || !$position_id || !$company_id) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'employee_id', 'id_number', 'full_name', 'position_id', dan 'company_id' diperlukan"
            ], 400);
        }

        try {
            // Cek apakah employee ada
            $checkStmt = $db->prepare("SELECT id FROM ar_employee WHERE id = :id");
            $checkStmt->execute(["id" => $employee_id]);
            if (!$checkStmt->fetch()) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Employee dengan ID ini tidak ditemukan."
                ], 404);
            }

            // Update data employee
            $sql = "
                UPDATE ar_employee SET
                    id_number = :id_number,
                    full_name = :full_name,
                    position_id = :position_id,
                    company_id = :company_id
                WHERE id = :id
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                "id" => $employee_id,
                "id_number" => $id_number,
                "full_name" => $full_name,
                "position_id" => $position_id,
                "company_id" => $company_id,
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Employee berhasil diupdate",
                "data" => [
                    "employee_id" => $employee_id
                ]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    $app->get("/employee/detail", function (Request $request, Response $response) {
        $db = $this->get('db_default');

        // Ambil dari query parameter employee_id
        $params = $request->getQueryParams();
        $employeeId = $params['employee_id'] ?? null;

        if (!$employeeId) {
            return $response->withJson([
                "status" => false,
                "message" => "Employee ID is required"
            ], 400);
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
                e.photo,
                p.id AS position_id,
                p.position_name,
                d.id AS department_id,
                d.dept_name,
                c.id AS company_id,
                c.company_name,
                u.status
            FROM ar_employee e
            LEFT JOIN ar_position p ON e.position_id = p.id
            LEFT JOIN ar_department d ON p.dept_id = d.id
            LEFT JOIN ar_company c ON e.company_id = c.id
            LEFT JOIN ar_users u ON u.employee_id = e.id
            WHERE e.id = :id
            LIMIT 1
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $employeeId]);
            $emp = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$emp) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Employee not found"
                ], 404);
            }

            $baseUrl = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
            $port = $request->getUri()->getPort();
            if ($port) $baseUrl .= ":" . $port;

            $emp['photo'] = $emp['photo'] ? $baseUrl . '/' . $emp['photo'] : null;

            $result = [
                "id" => $emp['employee_id'],
                "id_number" => $emp['id_number'],
                "full_name" => $emp['full_name'],
                "email" => $emp['email'],
                "no_tlpn" => $emp['no_tlpn'],
                "photo" => $emp['photo'],
                "is_claim" => $emp['is_claim'],
                "is_exit" => $emp['is_exit'],
                "status" => $emp['status'],
                "position" => [
                    "id" => $emp['position_id'],
                    "position_name" => $emp['position_name'],
                    "department" => [
                        "id" => $emp['department_id'],
                        "dept_name" => $emp['dept_name']
                    ]
                ],
                "company" => [
                    "id" => $emp['company_id'],
                    "company_name" => $emp['company_name']
                ]
            ];

            return $response->withJson([
                "status" => true,
                "data" => $result
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    $app->delete("/employee/delete", function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $body = $request->getParsedBody();

        // Ambil data dari request body
        $employee_id = $body['employee_id'] ?? null;

        // Validasi field wajib
        if (!$employee_id) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'employee_id' diperlukan"
            ], 400);
        }

        try {
            // Cek apakah data pegawai ada
            $checkStmt = $db->prepare("SELECT id FROM ar_employee WHERE id = :id");
            $checkStmt->execute(["id" => $employee_id]);
            $employee = $checkStmt->fetch();

            if (!$employee) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Employee tidak ditemukan"
                ], 404);
            }

            // Hapus data
            $deleteStmt = $db->prepare("DELETE FROM ar_employee WHERE id = :id");
            $deleteStmt->execute(["id" => $employee_id]);

            return $response->withJson([
                "status" => true,
                "message" => "Employee berhasil dihapus",
                "deleted_id" => $employee_id
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });



    $app->get("/employee/dropdown/position", function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $sql = "SELECT id, position_name FROM ar_position ORDER BY position_name ASC";
            $stmt = $db->query($sql);
            $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "data" => $positions
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });

    $app->get("/employee/department/dropdown", function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $sql = "SELECT id, dept_name FROM ar_department ORDER BY dept_name ASC";
            $stmt = $db->query($sql);
            $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "data" => $departments
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });



    $app->get("/employee/dropdown/company", function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $sql = "SELECT id, company_name FROM ar_company ORDER BY company_name ASC";
            $stmt = $db->query($sql);
            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "data" => $companies
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    $app->post("/department/add", function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $body = $request->getParsedBody();

        $dept_name = $body['dept_name'] ?? null;

        if (!$dept_name) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'dept_name' diperlukan"
            ], 400);
        }

        // Generate ID otomatis: DEP + 6 digit urut terakhir
        $lastIdStmt = $db->query("SELECT id FROM ar_department ORDER BY id DESC LIMIT 1");
        $lastDept = $lastIdStmt->fetch(PDO::FETCH_ASSOC);
        $lastNumber = $lastDept ? intval(substr($lastDept['id'], 3)) : 0;
        $newId = "DEP" . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

        try {
            $stmt = $db->prepare("INSERT INTO ar_department (id, dept_name) VALUES (:id, :dept_name)");
            $stmt->execute([
                "id" => $newId,
                "dept_name" => $dept_name
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Department berhasil ditambahkan",
                "data" => [
                    "id" => $newId,
                    "dept_name" => $dept_name
                ]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    $app->post("/position/add", function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $body = $request->getParsedBody();

        $position_name = $body['position_name'] ?? null;
        $dept_id       = $body['dept_id'] ?? null;

        if (!$position_name || !$dept_id) {
            return $response->withJson([
                "status" => false,
                "message" => "Parameter 'position_name' dan 'dept_id' diperlukan"
            ], 400);
        }

        // Generate ID otomatis: PO + 5 digit urut terakhir
        $lastIdStmt = $db->query("SELECT id FROM ar_position ORDER BY id DESC LIMIT 1");
        $lastPos = $lastIdStmt->fetch(PDO::FETCH_ASSOC);
        $lastNumber = $lastPos ? intval(substr($lastPos['id'], 2)) : 0;
        $newId = "PO" . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);

        try {
            $stmt = $db->prepare("INSERT INTO ar_position (id, position_name, dept_id) VALUES (:id, :position_name, :dept_id)");
            $stmt->execute([
                "id" => $newId,
                "position_name" => $position_name,
                "dept_id" => $dept_id
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Position berhasil ditambahkan",
                "data" => [
                    "id" => $newId,
                    "position_name" => $position_name,
                    "dept_id" => $dept_id
                ]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });

    
};