<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function ($app) {

    // ==========================================================
    // 🔹 GET: List Client
    // ==========================================================
    $app->get('/ar_client/list', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $sql = "SELECT c.*, e.full_name AS created_by_name
                    FROM ar_client c
                    LEFT JOIN ar_employee e ON c.created_by = e.id
                    ORDER BY c.client_name ASC";
            $stmt = $db->query($sql);
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson(["status" => true, "data" => $clients], 200);
        } catch (PDOException $e) {
            return $response->withJson(["status" => false, "message" => $e->getMessage()], 500);
        }
    });


    // ==========================================================
    // 🔹 POST: Tambah Client
    // ==========================================================
    $app->post('/ar_client/add', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        $clientName = $data['client_name'] ?? null;
        $createdBy  = $data['created_by'] ?? 'SYSTEM';

        if (!$clientName) {
            return $response->withJson(["status" => false, "message" => "client_name is required"], 400);
        }

        $clientId = generateClientId($db);

        try {
            $stmt = $db->prepare("
                INSERT INTO ar_client (id, client_name, created_by, created_at)
                VALUES (:id, :client_name, :created_by, NOW())
            ");
            $stmt->execute([
                "id"          => $clientId,
                "client_name" => $clientName,
                "created_by"  => $createdBy
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Client berhasil ditambahkan",
                "data" => [
                    "id" => $clientId,
                    "client_name" => $clientName
                ]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson(["status" => false, "message" => $e->getMessage()], 500);
        }
    });

    $app->put('/ar_client/update', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        $clientId   = $data['client_id'] ?? null;
        $clientName = $data['client_name'] ?? null;
        $updatedBy  = $data['updated_by'] ?? 'SYSTEM';

        if (!$clientId || !$clientName) {
            return $response->withJson(["status" => false, "message" => "client_id and client_name are required"], 400);
        }

        try {
            $stmt = $db->prepare("
                UPDATE ar_client 
                SET client_name = :client_name, updated_by = :updated_by, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                "id"          => $clientId,
                "client_name" => $clientName,
                "updated_by"  => $updatedBy,
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Client berhasil diupdate",
                "data" => [
                    "id" => $clientId,
                    "client_name" => $clientName
                ]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson(["status" => false, "message" => $e->getMessage()], 500);
        }
    });

    

    $app->get('/ar_client/dropdown', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $stmt = $db->prepare("SELECT id, client_name FROM ar_client ORDER BY client_name ASC");
            $stmt->execute();
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "data" => $clients
            ]);
        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => $e->getMessage()
            ], 500);
        }
    });


    // ==========================================================
    // 🔹 GET: List Project per Client
    // ==========================================================
    $app->get('/ar_client/projects', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $params = $request->getQueryParams();
        $clientId = $params['client_id'] ?? null;

        if (!$clientId) {
            return $response->withJson(["status" => false, "message" => "client_id query parameter is required"], 400);
        }

        try {
            $sql = "SELECT p.*, e.full_name AS created_by_name
                    FROM ar_client_project p
                    LEFT JOIN ar_employee e ON p.created_by = e.id
                    WHERE p.client_id = :client_id
                    ORDER BY p.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute(["client_id" => $clientId]);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson(["status" => true, "data" => $projects], 200);
        } catch (PDOException $e) {
            return $response->withJson(["status" => false, "message" => $e->getMessage()], 500);
        }
    });


    // ==========================================================
    // 🔹 POST: Tambah Project
    // ==========================================================
    $app->post('/ar_client/projects/add', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        $clientId    = $data['client_id'] ?? null;
        $projectName = $data['project_name'] ?? null;
        $createdBy   = $data['created_by'] ?? 'SYSTEM';

        if (!$clientId || !$projectName) {
            return $response->withJson(["status" => false, "message" => "client_id and project_name are required"], 400);
        }

        $projectId = generateProjectId($db);

        try {
            $stmt = $db->prepare("
                INSERT INTO ar_client_project (id, client_id, project_name, created_by, created_at)
                VALUES (:id, :client_id, :project_name, :created_by, NOW())
            ");
            $stmt->execute([
                "id"           => $projectId,
                "client_id"    => $clientId,
                "project_name" => $projectName,
                "created_by"   => $createdBy
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Project berhasil ditambahkan",
                "data" => [
                    "id" => $projectId,
                    "client_id" => $clientId,
                    "project_name" => $projectName
                ]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson(["status" => false, "message" => $e->getMessage()], 500);
        }
    });


    $app->put('/ar_client/projects/update', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        $projectId   = $data['project_id'] ?? null;
        $clientId    = $data['client_id'] ?? null;
        $projectName = $data['project_name'] ?? null;
        $updatedBy   = $data['updated_by'] ?? 'SYSTEM';

        if (!$projectId || !$clientId || !$projectName) {
            return $response->withJson([
                "status" => false,
                "message" => "project_id, client_id, and project_name are required"
            ], 400);
        }

        try {
            $stmt = $db->prepare("
                UPDATE ar_client_project
                SET client_id = :client_id, project_name = :project_name, updated_by = :updated_by, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([
                "id"           => $projectId,
                "client_id"    => $clientId,
                "project_name" => $projectName,
                "updated_by"   => $updatedBy,
            ]);

            return $response->withJson([
                "status" => true,
                "message" => "Project berhasil diupdate",
                "data" => [
                    "id" => $projectId,
                    "client_id" => $clientId,
                    "project_name" => $projectName
                ]
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson(["status" => false, "message" => $e->getMessage()], 500);
        }
    });



    // ==========================================================
    // 🔹 GET: List Activity per Project
    // ==========================================================
    $app->get('/ar_client/project/activities', function ($request, $response) {
        $db = $this->get('db_default');
        $params = $request->getQueryParams();
        $projectId = $params['project_id'] ?? null;

        if (!$projectId) {
            return $response->withJson([
                "status" => false,
                "message" => "project_id is required"
            ], 400);
        }

        try {
            $sql = "SELECT 
                        a.*, 
                        p.project_name,
                        c.id AS client_id,
                        c.client_name,
                        ep.full_name AS pic_name,
                        en.full_name AS next_pic_name,
                        e.full_name AS created_by_name,
                        TRIM(dn.id) AS next_pic_dept_id,
                        CASE 
                            WHEN dn.id = 'DEP000003' THEN 'sales'
                            WHEN dn.id = 'DEP000004' THEN 'technician'
                            ELSE 'all'
                        END AS next_pic_type
                    FROM ar_client_project_activity a
                    LEFT JOIN ar_client_project p ON a.project_id = p.id
                    LEFT JOIN ar_client c ON p.client_id = c.id
                    LEFT JOIN ar_employee ep ON a.pic = ep.id
                    LEFT JOIN ar_employee en ON a.next_pic = en.id
                    LEFT JOIN ar_position pn ON en.position_id = pn.id
                    LEFT JOIN ar_department dn ON pn.dept_id = dn.id
                    LEFT JOIN ar_employee e ON a.created_by = e.id
                    WHERE a.project_id = :project_id
                    ORDER BY a.log_date DESC, a.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute(["project_id" => $projectId]);
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson(["status" => true, "data" => $activities], 200);

        } catch (PDOException $e) {
            return $response->withJson(["status" => false, "message" => $e->getMessage()], 500);
        }
    });


    // ===================================================
    // POST: Add Activity
    // ===================================================
    $app->post('/ar_client/project/activities/add', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        if (empty($data['log_date']) || empty($data['status']) || empty($data['created_by'])) {
            return $response->withJson([
                "status" => false,
                "message" => "Required fields: log_date, status, created_by"
            ], 400);
        }

        $id         = generateActivityId($db);
        $projectId  = $data['project_id'] ?? null;
        $note       = $data['note'] ?? null;
        $trouble    = $data['trouble'] ?? null;
        $status     = strtolower(trim($data['status']));
        $reason     = $data['reason'] ?? null;
        $maintenanceNo = $status === 'closed' ? ($data['maintenance_no'] ?? null) : null;
        $createdBy  = $data['created_by'];

        // Ambil activity terakhir untuk project ini
        $stmtLast = $db->prepare("
            SELECT next_pic 
            FROM ar_client_project_activity 
            WHERE project_id = :project_id 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmtLast->execute(["project_id" => $projectId]);
        $lastActivity = $stmtLast->fetch(PDO::FETCH_ASSOC);

        // Tentukan pic & next_pic
        if ($status === 'closed') {
            $pic = ($lastActivity && !empty($lastActivity['next_pic'])) ? $lastActivity['next_pic'] : $createdBy;
            $nextPic = null;
        } else {
            $pic = ($lastActivity && !empty($lastActivity['next_pic'])) ? $lastActivity['next_pic'] : $createdBy;
            $nextPic = $data['next_pic'] ?? null;
        }

        try {
            // Simpan activity
            $sql = "INSERT INTO ar_client_project_activity 
                    (id, project_id, log_date, note, trouble, pic, next_pic, status, reason, maintenance_no, created_by, created_at)
                    VALUES 
                    (:id, :project_id, :log_date, :note, :trouble, :pic, :next_pic, :status, :reason, :maintenance_no, :created_by, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                "id" => $id,
                "project_id" => $projectId,
                "log_date" => $data['log_date'],
                "note" => $note,
                "trouble" => $trouble,
                "pic" => $pic,
                "next_pic" => $nextPic,
                "status" => $status,
                "reason" => $reason,
                "maintenance_no" => $maintenanceNo,
                "created_by" => $createdBy
            ]);

            // Ambil project info
            $projectName = $clientName = null;
            if (!empty($projectId)) {
                $stmtProject = $db->prepare("
                    SELECT p.project_name, c.client_name
                    FROM ar_client_project p
                    JOIN ar_client c ON p.client_id = c.id
                    WHERE p.id = :project_id LIMIT 1
                ");
                $stmtProject->execute(["project_id" => $projectId]);
                $project = $stmtProject->fetch(PDO::FETCH_ASSOC);
                if ($project) {
                    $projectName = $project['project_name'];
                    $clientName  = $project['client_name'];
                }
            }

            // 🔔 Notifikasi
            $title = "Update Aktivitas Proyek";
            $activityDateText = date("d M Y", strtotime($data["log_date"]));
            $noteText = $note ? " Catatan: $note" : "";
            $statusText = $status === "closed" ? "Aktivitas telah ditutup." : "Ada aktivitas baru yang ditambahkan.";
            $msg = "Hai! $statusText Tanggal aktivitas: $activityDateText.$noteText";

            $targetPositions = [
                'PO00005', // Head Sales Dept
                'PO00007', // Sales Representative (contoh: pastikan kode-nya benar di DB kamu)
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];

            $positionPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);
            $nextPicPlayerIds  = !empty($nextPic) ? EmployeeHelper::getPlayerIdsByEmployee($db, $nextPic) : [];
            $allPlayerIds = array_unique(array_merge($positionPlayerIds, $nextPicPlayerIds));
            $creatorPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $createdBy);
            $allPlayerIds = array_diff($allPlayerIds, $creatorPlayerIds);


            // ✅ Kirim ke OneSignal
            if (!empty($allPlayerIds) && $projectId) {
                OneSignalHelper::sendNotification($allPlayerIds, $msg, $title, [
                    "type" => "activityAdd",
                    "project_id" => $projectId,
                    "project_name" => $projectName,
                    "client_name" => $clientName
                ]);
            }

            // ✅ Simpan ke ar_user_notifications
            if (!empty($allPlayerIds)) {
                foreach ($allPlayerIds as $playerId) {
                    $employeeId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                    if (!$employeeId) continue; // skip kalau tidak ditemukan

                    $notifId = generateCustomId();
                    $stmtNotif = $db->prepare("
                        INSERT INTO ar_user_notifications (id, user_id, type, created_at)
                        VALUES (:id, :user_id, :type, NOW())
                    ");
                    $stmtNotif->execute([
                        "id" => $notifId,
                        "user_id" => $employeeId,
                        "type" => "activityAdd"
                    ]);
                }
            }


            return $response->withJson([
                "status" => true,
                "message" => "Activity berhasil ditambahkan dan notifikasi dikirim",
                "data" => ["id" => $id]
            ], 201);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });


    // ===================================================
    // PUT: Update Activity
    // ===================================================
    $app->put('/ar_client/project/activities/update', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $data = $request->getParsedBody();

        if (empty($data['id'])) {
            return $response->withJson([
                "status" => false,
                "message" => "Activity id is required"
            ], 400);
        }

        try {
            $status = strtolower(trim($data['status'] ?? ''));

            // Ambil data lama
            $stmtOld = $db->prepare("SELECT pic, next_pic, note, log_date, project_id FROM ar_client_project_activity WHERE id = :id");
            $stmtOld->execute(["id" => $data['id']]);
            $old = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if (!$old) {
                return $response->withJson([
                    "status" => false,
                    "message" => "Data activity tidak ditemukan"
                ], 404);
            }

            // Ambil activity terakhir selain ini untuk project
            $stmtLast = $db->prepare("
                SELECT next_pic 
                FROM ar_client_project_activity 
                WHERE project_id = :project_id AND id != :current_id
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmtLast->execute([
                "project_id" => $old['project_id'],
                "current_id" => $data['id']
            ]);
            $lastActivity = $stmtLast->fetch(PDO::FETCH_ASSOC);

            // Tentukan pic & next_pic
            if ($status === 'closed') {
                $newPic = ($lastActivity && !empty($lastActivity['next_pic'])) ? $lastActivity['next_pic'] : ($old['pic'] ?? null);
                $newNextPic = null;
            } else {
                $newPic = $data['pic'] ?? (($lastActivity && !empty($lastActivity['next_pic'])) ? $lastActivity['next_pic'] : $old['pic']);
                $newNextPic = $data['next_pic'] ?? $old['next_pic'];
            }

            $maintenanceNo = $status === 'closed' ? ($data['maintenance_no'] ?? null) : null;

            // Update activity
            $sql = "UPDATE ar_client_project_activity 
                    SET project_id = :project_id, log_date = :log_date, note = :note,
                        trouble = :trouble, pic = :pic, next_pic = :next_pic, status = :status,
                        reason = :reason, maintenance_no = :maintenance_no, updated_by = :updated_by, updated_at = NOW()
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                "id" => $data['id'],
                "project_id" => $data['project_id'] ?? $old['project_id'],
                "log_date" => $data['log_date'] ?? $old['log_date'],
                "note" => $data['note'] ?? $old['note'],
                "trouble" => $data['trouble'] ?? null,
                "pic" => $newPic,
                "next_pic" => $newNextPic,
                "status" => $status,
                "reason" => $data['reason'] ?? null,
                "maintenance_no" => $maintenanceNo,
                "updated_by" => $data['updated_by'] ?? null,
            ]);

            // Ambil project info
            $projectId = $data['project_id'] ?? $old['project_id'];
            $projectName = $clientName = null;
            if (!empty($projectId)) {
                $stmtProject = $db->prepare("
                    SELECT p.project_name, c.client_name
                    FROM ar_client_project p
                    JOIN ar_client c ON p.client_id = c.id
                    WHERE p.id = :project_id LIMIT 1
                ");
                $stmtProject->execute(["project_id" => $projectId]);
                $project = $stmtProject->fetch(PDO::FETCH_ASSOC);
                if ($project) {
                    $projectName = $project['project_name'];
                    $clientName  = $project['client_name'];
                }
            }

            // 🔔 Notifikasi
            $title = "Update Aktivitas Proyek";
            $activityDateText = date("d M Y", strtotime($data["log_date"] ?? $old['log_date']));
            $noteText = isset($data["note"]) ? " Catatan: {$data['note']}" : "";
            $statusText = $status === "closed" ? "Aktivitas proyek telah ditutup." : "Ada pembaruan aktivitas proyek.";
            $msg = "Hai! $statusText Tanggal aktivitas: $activityDateText.$noteText";

            $targetPositions = [
                'PO00005', // Head Sales Dept
                'PO00019', // Sales Representative
                'PO00001', // President Director
                'PO00013', // Corporate Secretary
                'PO00006', // Head Project and Customer Service Dept
                'PO00003', // Advisory Product Dev.
                'PO00014'  // Operational Manager
            ];


            $positionPlayerIds = EmployeeHelper::getPlayerIdsByPositions($db, $targetPositions);
            $nextPicPlayerIds = !empty($newNextPic) ? EmployeeHelper::getPlayerIdsByEmployee($db, $newNextPic) : [];
            $allPlayerIds = array_unique(array_merge($positionPlayerIds, $nextPicPlayerIds));

            // 🔹 Hapus updated_by dari list notifikasi
            $updaterPlayerIds = EmployeeHelper::getPlayerIdsByEmployee($db, $data["updated_by"]);
            $allPlayerIds = array_diff($allPlayerIds, $updaterPlayerIds);


            // ✅ Kirim ke OneSignal
            if (!empty($allPlayerIds) && $projectId) {
                OneSignalHelper::sendNotification($allPlayerIds, $msg, $title, [
                    "type" => "activityUpdate",
                    "project_id" => $projectId,
                    "project_name" => $projectName,
                    "client_name" => $clientName
                ]);
            }

            // ✅ Simpan ke ar_user_notifications
            if (!empty($allPlayerIds)) {
                foreach ($allPlayerIds as $playerId) {
                    $employeeId = EmployeeHelper::getUserIdByPlayerId($db, $playerId);
                    if (!$employeeId) continue;

                    $notifId = generateCustomId();
                    $stmtNotif = $db->prepare("
                        INSERT INTO ar_user_notifications (id, user_id, type, created_at)
                        VALUES (:id, :user_id, :type, NOW())
                    ");
                    $stmtNotif->execute([
                        "id" => $notifId,
                        "user_id" => $employeeId,
                        "type" => "activityUpdate"
                    ]);
                }
            }


            return $response->withJson([
                "status" => true,
                "message" => "Activity berhasil diperbarui dan notifikasi dikirim"
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });




    $app->get('/ar_client/project/activities/pending', function ($request, $response) {
        $db = $this->get('db_default');
        $params = $request->getQueryParams();
        $employeeId = $params['employee_id'] ?? null;

        if (!$employeeId) {
            return $response->withJson([
                "status" => false,
                "message" => "employee_id is required"
            ], 400);
        }

        try {
            $sql = "
                SELECT 
                    a.*, 
                    p.project_name,
                    p.client_id,
                    ep.full_name AS pic_name,
                    en.full_name AS next_pic_name,
                    e.full_name AS created_by_name
                FROM ar_client_project_activity a
                LEFT JOIN ar_client_project p ON a.project_id = p.id
                LEFT JOIN ar_employee ep ON a.pic = ep.id
                LEFT JOIN ar_employee en ON a.next_pic = en.id
                LEFT JOIN ar_employee e ON a.created_by = e.id
                WHERE 
                    a.next_pic = :employee_id
                    AND a.status = 'pending'
                    AND DATEDIFF(CURDATE(), a.log_date) >= 60
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM ar_client_project_activity newer
                        WHERE newer.project_id = a.project_id
                        AND newer.log_date > a.log_date
                    )
                ORDER BY a.log_date DESC, a.created_at DESC
                LIMIT 1
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute(["employee_id" => $employeeId]);
            $activity = $stmt->fetch(PDO::FETCH_ASSOC);

            return $response->withJson([
                "status" => true,
                "data" => $activity ? [$activity] : []
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                "status" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    });

};
