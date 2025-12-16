<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/prodi/list', function ($request, $response) {
        $db = $this->get('db_default');

        $params = $request->getQueryParams();
        $role = isset($params['role']) ? (int)$params['role'] : null;
        $userId = $params['user_id'] ?? null;

        if ($role === null) {
            return $response->withJson([
                'status' => false,
                'message' => 'Parameter role wajib diisi'
            ], 400);
        }

        try {

            // ADMIN & DIREKTUR → akses semua prodi
            if (in_array($role, [0, 1, 3])) {

                $stmt = $db->query("
                    SELECT id, kode_prodi, nama_prodi, foto, created_at, updated_at
                    FROM mr_prodi
                    ORDER BY nama_prodi ASC
                ");
            }

            // KAPRODI (role 2) & DOSEN (role 4) → hanya prodi miliknya
            else if (in_array($role, [2, 4])) {

                if (empty($userId)) {
                    return $response->withJson([
                        'status' => false,
                        'message' => 'Parameter user_id wajib untuk role 2 dan 4'
                    ], 400);
                }

                // Relasi penting: mr_sdm → (prodi_id, user_id)
                $stmt = $db->prepare("
                    SELECT DISTINCT 
                        p.id, p.kode_prodi, p.nama_prodi, p.foto, p.created_at, p.updated_at
                    FROM mr_prodi p
                    INNER JOIN mr_sdm s ON p.id = s.prodi_id
                    WHERE s.user_id = :user_id
                    ORDER BY p.nama_prodi ASC
                ");

                $stmt->execute(['user_id' => $userId]);
            }

            // ROLE lain tidak boleh akses
            else {
                return $response->withJson([
                    'status' => false,
                    'message' => 'Role ini tidak memiliki akses data prodi'
                ], 403);
            }

            $prodiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $response->withJson([
                'status' => true,
                'data' => $prodiList,
            ], 200);

        } catch (PDOException $e) {
            return $response->withJson([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });

};