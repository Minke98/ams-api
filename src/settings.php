<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,

        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],

        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        'db' => [
            'default' => [
                'socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock', // pakai socket
                'host' => 'localhost', // kalau nanti prod ganti host ini
                'user' => 'root',
                'pass' => 'P@ssw0rd',
                'dbname' => 'monitoring_report',
                'driver' => 'mysql',
            ],
            'second' => [
                'socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock', // pakai socket juga
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'P@ssw0rd',
                'dbname' => 'museum_geologi', // database kedua kamu
                'driver' => 'mysql',
            ],
            'coba' => [
                'socket' => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock', // pakai socket juga
                'host' => 'localhost',
                'user' => 'root',
                'pass' => 'P@ssw0rd',
                'dbname' => 'coba', // database kedua kamu
                'driver' => 'mysql',
            ],
        ],
    ],
];
