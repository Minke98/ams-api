<?php

use Slim\Http\Request;
use Slim\Http\Response;

return function ($app) {

    $app->get('/', function ($request, $response) {
        return $this->renderer->render($response, 'index.phtml');
    });

    // Import modular route
    (require __DIR__ . '/api/claim.php')($app);
    (require __DIR__ . '/api/attendance.php')($app);
    (require __DIR__ . '/api/history_attendance.php')($app);
    (require __DIR__ . '/api/login.php')($app);
    (require __DIR__ . '/api/change_photo.php')($app);
    (require __DIR__ . '/api/employee.php')($app);
    (require __DIR__ . '/api/change_password.php')($app);
    (require __DIR__ . '/api/notification_setting.php')($app);
    (require __DIR__ . '/api/visit_report.php')($app); //harus di tambahkan
    (require __DIR__ . '/api/leave.php')($app);
    (require __DIR__ . '/api/client.php')($app);
    (require __DIR__ . '/api/absence.php')($app);
    (require __DIR__ . '/api/app_version.php')($app);
    (require __DIR__ . '/api/user_notif.php')($app);
    

    
};
