<?php

use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    // multi database handler
    $dbSettings = $container->get('settings')['db'];
    foreach ($dbSettings as $key => $db) {
        $container['db_' . $key] = function ($c) use ($db) {
            if (isset($db['socket']) && !empty($db['socket'])) {
                $dsn = "mysql:unix_socket={$db['socket']};dbname={$db['dbname']}";
            } else {
                $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};port=3306";
            }

            $pdo = new PDO(
                $dsn,
                $db['user'],
                $db['pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };
    }
};
