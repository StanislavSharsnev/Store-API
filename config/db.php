<?php

function getDBParams() {
	return [
	    'dsn' => 'mysql:host=localhost;dbname=store_db',
	    'username' => 'store_user',
	    'password' => '1234'
	];
}

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=store_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];