<?php
use Slim\Http\Request;
use Slim\Http\Response;

return function (\Slim\App $app) {
    $app->get('/notification-setting', function (Request $request, Response $response) {
        $db = $this->get('db_default');

        try {
            $sql = "SELECT notify_times FROM ar_notification_setting LIMIT 1";
            $stmt = $db->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $response->getBody()->write(json_encode([
                    'status' => true,
                    'data' => $result
                ]));
            } else {
                $response->getBody()->write(json_encode([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]));
            }

            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                'status' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};

