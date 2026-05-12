<?php


return [
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASSWORD') ?: '',
    'db_name' => getenv('DB_NAME') ?: 'inventory_system',
];
