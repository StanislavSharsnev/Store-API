<?php

require_once '/../config/db.php';

class DBConnection {
	private static $connection = null;

	public static function getConnection() {
		if (DBConnection::$connection == null) {
			$dbParams = getDBParams();

			DBConnection::$connection = new PDO($dbParams['dsn'], $dbParams['username'], $dbParams['password']);
			DBConnection::$connection->exec("set names utf8");
		}

		return DBConnection::$connection;
	}
}