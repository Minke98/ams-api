<?php
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
require_once __DIR__ . '/../helpers/IdHelper.php';

return function (App $app) {

    $app->get('/user/notifications', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $userId = $request->getQueryParams()['user_id'] ?? null;

        if (!$userId) {
            return $response->withJson(['error' => 'Missing user_id'], 400);
        }

        $stmt = $db->prepare("
            SELECT type, read_status 
            FROM ar_user_notifications
            WHERE user_id = :user_id AND read_status = 0
        ");
        $stmt->execute([':user_id' => $userId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $flags = [
            'missing_attendance' => false,
            'absence' => false,
            'activity' => false,
            'leave' => false,
            'visit' => false,
            'visitLog' => false,
        ];

        $typeMapping = [
            'activityAdd' => 'activity',
            'activityUpdate' => 'activity',
            'leaveAdd' => 'leave',
            'leaveUpdate' => 'leave',
            'visitAdd' => 'visit',
            'visitUpdate' => 'visit',
            'visitLogAdd' => 'visitLog',
            'visitLogUpdate' => 'visitLog',
        ];

        foreach ($data as $row) {
            $type = $row['type'];
            if (isset($typeMapping[$type])) $type = $typeMapping[$type];
            if (isset($flags[$type])) $flags[$type] = true;
        }

        return $response->withJson($flags);
    });

    $app->post('/user/notifications/read', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $params = $request->getParsedBody();
        $userId = $params['user_id'] ?? null;
        $type = $params['type'] ?? null;

        if (!$userId || !$type) {
            return $response->withJson(['error' => 'Missing parameters'], 400);
        }

        $typeMapping = [
            'activity' => ['activity', 'activityAdd', 'activityUpdate'],
            'leave' => ['leave', 'leaveAdd', 'leaveUpdate'],
            'visit' => ['visit', 'visitAdd', 'visitUpdate'],
            'visitLog' => ['visitLog', 'visitLogAdd', 'visitLogUpdate'],
            'missing_attendance' => ['missing_attendance'],
            'absence' => ['absence'],
        ];

        $typesToUpdate = $typeMapping[$type] ?? [$type];
        $inQuery = implode(',', array_fill(0, count($typesToUpdate), '?'));

        $stmt = $db->prepare("
            UPDATE ar_user_notifications
            SET read_status = 1
            WHERE user_id = ? AND type IN ($inQuery)
        ");
        $stmt->execute(array_merge([$userId], $typesToUpdate));

        return $response->withJson(['success' => true]);
    });

    $app->post('/user/notifications/add', function (Request $request, Response $response) {
        $db = $this->get('db_default');
        $params = $request->getParsedBody();
        $userId = $params['user_id'] ?? null;
        $type = $params['type'] ?? null;

        if (!$userId || !$type) {
            return $response->withJson(['error' => 'Missing parameters'], 400);
        }

        $id = generateCustomId();
        $stmt = $db->prepare("
            INSERT INTO ar_user_notifications (id, user_id, type, read_status, created_at)
            VALUES (:id, :user_id, :type, 0, NOW())
        ");
        $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':type' => $type,
        ]);

        return $response->withJson(['success' => true, 'id' => $id]);
    });
};
