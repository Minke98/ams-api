<?php

use Slim\Http\Request;
use Slim\Http\Response;

return function ($app) {

    $app->get('/', function ($request, $response) {
        return $this->renderer->render($response, 'index.phtml');
    });

    // Import modular route
    (require __DIR__ . '/api/claim.php')($app);
    (require __DIR__ . '/api/login.php')($app);
    (require __DIR__ . '/api/change_photo.php')($app);
    (require __DIR__ . '/api/employee.php')($app);
    (require __DIR__ . '/api/change_password.php')($app);
    (require __DIR__ . '/api/notification_setting.php')($app);
    (require __DIR__ . '/api/app_version.php')($app);
    (require __DIR__ . '/api/user_notif.php')($app);
    (require __DIR__ . '/api/dashboard.php')($app);
    (require __DIR__ . '/api/prodi.php')($app);
    (require __DIR__ . '/api/room.php')($app);
    (require __DIR__ . '/api/equipment.php')($app);
    (require __DIR__ . '/api/maintenance_history.php')($app);
    (require __DIR__ . '/api/maintenance_schedule.php')($app);
    (require __DIR__ . '/api/report_damage.php')($app);
    (require __DIR__ . '/api/sdm.php')($app);
    (require __DIR__ . '/api/certificate.php')($app);
    (require __DIR__ . '/api/report.php')($app);
    (require __DIR__ . '/api/reminder.php')($app);
    

    
};
