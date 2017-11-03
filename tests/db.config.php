<?php
/**
 * Dida Framework --Powered by Zeupin LLC
 * http://dida.zeupin.com
 */
return [
    'db.dsn'            => 'mysql:host=localhost;port=3306;dbname=zeupin',
    'db.username'       => 'zeupin',
    'db.password'       => 'zeupin',
    'db.options'        => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT         => false
    ],
    'db.schemamap_dir' => __DIR__ . '/cache',
    'db.name'           => 'zeupin',
    'db.type'           => 'mysql',
    'db.prefix'         => 'zp_',
    'db.swap_prefix'    => '###_',
];
