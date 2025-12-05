<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/prodi/list', function ($request, $response) {
        $db = $this->get('db_default');

        try {
            $stmt = $db->query("SELECT id, kode_prodi, nama_prodi, foto, created_at, updated_at FROM mr_prodi ORDER BY nama_prodi ASC");
            $prodiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode([
                'status' => true,
                'data' => $prodiList
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
