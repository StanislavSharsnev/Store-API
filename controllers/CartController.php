<?php

namespace app\controllers;

use yii\web\Controller;
use \Firebase\JWT\JWT;

require_once '/../db/db.php';
require_once '/../models/CartModel.php';
require_once '/../config/params.php';
require_once '/../libs/jwt/JWT.php';
require_once '/../libs/jwt/BeforeValidException.php';

class CartController extends Controller {
	
	public function actionMakeorder() {
		$input = file_get_contents("php://input");
		$cartItems = json_decode($input);

		$headers = apache_request_headers();
		$token = null;
		foreach ($headers as $header => $value) {
			if ($header == 'Authorization') {
				$token = $value;
				break;
			}
		}

		$key = jwtKey();
		JWT::$leeway = 86400;
		$decoded = (array)JWT::decode($token, $key, array('HS256'));

		$id = $decoded['id'];

		$db = \DBConnection::getConnection();

    	foreach ($cartItems as $cartItem) {
    		$stmt = $db->prepare("INSERT into `cart`(id_user,id_item,`count`,`date`) values (:id_user,:id_item,:count,now())", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
	    	$stmt->bindParam(':id_user', $id);
	    	$stmt->bindParam(':id_item', $cartItem->item->id);
	    	$stmt->bindParam(':count', $cartItem->count);
	    	$stmt->execute();
    	}

	    echo true;
	}

	public function beforeAction($action)
	{
		$this->enableCsrfValidation = false;
		return parent::beforeAction($action);
	}
}