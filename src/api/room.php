<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/room/list', function ($request, $response) {
        $db = $this->get('db_default');

        // Ambil prodi_id dari query param
        $params = $request->getQueryParams();
        $prodi_id = $params['prodi_id'] ?? null;

        if (!$prodi_id) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Parameter prodi_id diperlukan'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $stmt = $db->prepare("
                SELECT 
                    r.id,
                    r.prodi_id,
                    p.nama_prodi,    -- ← ambil nama prodi
                    r.kode_ruangan,
                    r.nama_ruangan,
                    r.kapasitas,
                    r.deskripsi,
                    r.foto,
                    r.created_at,
                    r.updated_at,
                    IFNULL(peng.status, 0) AS status_penggunaan
                FROM mr_ruangan r
                LEFT JOIN mr_prodi p 
                    ON r.prodi_id = p.id          -- ← join ke tabel prodi
                LEFT JOIN mr_penggunaan_ruangan peng
                    ON r.id = peng.ruangan_id
                WHERE r.prodi_id = :prodi_id
                ORDER BY r.nama_ruangan ASC
            ");

            $stmt->execute(['prodi_id' => $prodi_id]);
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode([
                'status' => true,
                'data' => $rooms
            ]));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

};
