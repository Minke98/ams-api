<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {

    $app->get('/version/check', function (Request $request, Response $response) {
        try {
            $platform = $request->getQueryParams()['platform'] ?? 'android';

            $db = $this->get('db_default');
            $stmt = $db->prepare("
                SELECT min_version 
                FROM ar_app_version 
                WHERE platform = :platform 
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute(['platform' => $platform]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new Exception("Version info not found");
            }

            return $response->withJson([
                'status' => true,
                'data' => [
                    'min_version' => $data['min_version']
                ]
            ], 200);

        } catch (Exception $e) {
            return $response->withJson([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    });

};
