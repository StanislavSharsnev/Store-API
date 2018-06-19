<?php

namespace app\controllers;

use yii\web\Controller;
use \Firebase\JWT\JWT;

require_once '/../db/db.php';
require_once '/../models/CartModel.php';
require_once '/../libs/jwt/JWT.php';
require_once '/../config/params.php';
require_once '/../models/ErrorModel.php';
require_once '/../models/DataModel.php';

class AuthController extends Controller {
	
	public function actionLogin() {
		$input = file_get_contents("php://input");
		$login = json_decode($input);

		$db = \DBConnection::getConnection();
    	
    	$stmt = $db->prepare("SELECT id, password, role FROM `users` WHERE `email` = :log", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    	$stmt->bindParam(':log', $login->login);
	    $stmt->execute();
	    $user = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT);
	    if($user==null) {
	    	$error = new \Error();
	    	$error->error = "Неверный логин или пароль";
	    	echo json_encode($error);
	    	return;
	    }

	    if(!password_verify($login->password, $user[1]) ){
	    	$error = new \Error();
	    	$error->error = "Неверный логин или пароль";
	    	echo json_encode($error);
	    	return;
	    }


	    $as = $_GET["as"];
	    if($as!=$user[2]){
	    	return;
	    }

		$key = jwtKey();

		$token = array(

		    "iat" => time(),
		    "nbf" => time() + 86400,
		    
		);
		if($user[2]=="admin"){
			$token["id_admin"]=$user[0];
		}
		elseif($user[2]=="user"){
			$token["id"]=$user[0];
		}
		$jwt = JWT::encode($token, $key);
		$data = new \Data();
		$data->data = $jwt;
		echo json_encode($data);
	}

	public function actionReg(){
		$input = file_get_contents("php://input");
		$reg = json_decode($input);

		$db = \DBConnection::getConnection();
    	
    	$stmt = $db->prepare("INSERT into users(email, password, name) values (:email, :pass, :name) ", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    	$stmt->bindParam(':email', $reg->login);
    	$reg->password = password_hash($reg->password, PASSWORD_DEFAULT);
    	$stmt->bindParam(':pass', $reg->password);
    	$stmt->bindParam(':name', $reg->name);
	    $stmt->execute();

	    $id = $db->lastInsertId();

	    $key = jwtKey();
		$token = array(
		    "iat" => time(),
		    "nbf" => time() + 86400,
		    "id" => $id
		);
		$jwt = JWT::encode($token, $key);
		$data = new \Data();
		$data->data = $jwt;
		echo json_encode($data);
	}
}