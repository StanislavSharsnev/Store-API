<?php

namespace app\controllers;

use yii\web\Controller;

require_once '/../db/db.php';
require_once '/../models/OrdersModel.php';

class OrdersController extends Controller
{
	public function actionGet(){
		$datefrom = $_GET["datefrom"];
		$dateto = $_GET["dateto"];

		$db = \DBConnection::getConnection();
    	
    	$stmt = $db->prepare("SELECT U.name, email, C.`date`, I.name, price, `count` FROM `cart` C INNER JOIN `users` U ON C.`id_user` = U.`id` INNER JOIN `items` I ON C.id_item = I.id WHERE UNIX_TIMESTAMP(C.`date`) >= :from AND UNIX_TIMESTAMP(C.`date`)<= :to ORDER BY email, C.`date`", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    	$stmt->bindParam(':from', $datefrom);
    	$stmt->bindParam(':to', $dateto);
	    $stmt->execute();

	    $orders = array();
	    $lastemail = null;
	    $lastdate = null;
	    $order = null;
	    while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
	    	if($lastemail != $row[1] || $lastdate != $row[2]) {
	    		if ($order != null)
	    			array_push($orders, $order);

	    		$order = new \Order();
	    		$order->name = $row[0];
	    		$order->email = $row[1];
	    		$order->date = $row[2];
	    		$order->items = array();
	    		$order->totalprice = 0;
	    		$lastemail = $row[1];
	    		$lastdate = $row[2];
	    	}

	    	$item = new \OrderItem();
	    	$item->name = $row[3];
	    	$item->count = $row[5];
	    	$item->price = $item->count * $row[4];
	    	$order->totalprice += $item->price;
	    	array_push($order->items, $item);
	    }
	    if ($order != null)
	    	array_push($orders, $order);

	    echo json_encode($orders);
	}
}