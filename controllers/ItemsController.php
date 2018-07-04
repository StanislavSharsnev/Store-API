<?php

namespace app\controllers;

use yii\web\Controller;

require_once '/../db/db.php';
require_once '/../models/ItemModel.php';

class ItemsController extends Controller
{
    public function actionGet()
    {
    	$db = \DBConnection::getConnection();
    	
    	$stmt = $db->prepare("SELECT id, name, price, description FROM `items` I INNER JOIN `categories_items` CI ON I.`id` = CI.`id_item` WHERE `id_category` = :cid", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
    	$categoryId = $_GET['categoryId'];
    	$stmt->bindParam(':cid', $categoryId);
	    $stmt->execute();

	    $items = array();
	    $itemIds = "";
	    while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
	    	$item = new \Item();
	    	$item->id = $row[0];
	    	$item->name = $row[1];
	    	$item->price = $row[2];
	    	$item->description = $row[3];
	    	$item->photos = array();
	    	array_push($items, $item);

	    	$itemIds .= "$item->id,";
	    }
	    $itemIds = rtrim($itemIds, ",");

	    $stmt = $db->prepare("SELECT `id_item`, `photo` FROM `items_photos` WHERE `id_item` IN ($itemIds)", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
	    $stmt->execute();
	    while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
	    	$id = $row[0];
	    	$photo = $row[1];

	    	foreach($items as $item) {
	    		if ($id == $item->id) {
	    			array_push($item->photos, $photo);
	    			break;
	    		}
	    	}
	    }

	    echo json_encode($items);
    }

    public function actionEdit()
    {
    	$id = $_GET["id"];
    	$db = \DBConnection::getConnection();

    	if(isset($_FILES["file"]) ) {
	    	$filename = $_FILES['file']['name'];
	    	if (!file_exists(__DIR__."\..\content\items\\$id\\")) {
	    		mkdir(__DIR__."\..\content\items\\$id\\", 0777, true);
			}

			if (!move_uploaded_file($_FILES['file']['tmp_name'], __DIR__."\..\content\items\\$id\\$filename"))
				return;

			$serverpath = serverPath();
			$image = $serverpath."content/items/$id/$filename";
			$stmt = $db->prepare("INSERT INTO `items_photos`(`id_item`, `photo`) VALUES (:id, :photo)", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
		    $stmt->bindParam(':id', $id);
		    $stmt->bindParam(':photo', $image);
		    $stmt->execute();
		}

		if(isset($_POST['item'])){
			$item = json_decode($_POST['item']);
	    	$stmt = $db->prepare("UPDATE `items` set `name` = :name, `description` = :desc, `price` = :price WHERE `id` = :id", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
		    $stmt->bindParam(':name', $item->name);
		    $stmt->bindParam(':desc', $item->description);
		    $stmt->bindParam(':price', $item->price);
		    $stmt->bindParam(':id', $id);
		    $stmt->execute();

		    $categories = json_decode($_POST['categories']);
		    $stmt = $db->prepare("DELETE FROM `categories_items` WHERE `id_item` =:id", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
			    $stmt->bindParam(':id', $id);
			    $stmt->execute();

		    foreach($categories as $categoryId) {
		    	$stmt = $db->prepare("INSERT INTO `categories_items` (`id_item`, `id_category`) VALUES (:id_item, :id_category)", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
			    $stmt->bindParam(':id_item', $id);
			    $stmt->bindParam(':id_category', $categoryId);
			    $stmt->execute();
		    }
		}

	    echo true;
    } 
    public function actionDeletephoto(){
    	$id = $_GET["id"];
    	$url = file_get_contents("php://input");

    	$db = \DBConnection::getConnection();
		$stmt = $db->prepare("DELETE FROM `items_photos` WHERE `id_item`= :id AND `photo` = :photo ", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
	    $stmt->bindParam(':id', $id);
	    $stmt->bindParam(':photo', $url);
	    $stmt->execute();
	    echo true;
    }
    public function actionAdd(){
    	$db = \DBConnection::getConnection();

    	if(isset($_POST['item'])){
			$item = json_decode($_POST['item']);
	    	$stmt = $db->prepare("INSERT INTO `items` (`name`, `price`, `description`) VALUES (:name, :price, :desc)", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
		    $stmt->bindParam(':name', $item->name);
		    $stmt->bindParam(':desc', $item->description);
		    $stmt->bindParam(':price', $item->price);
		    $stmt->execute();

		    $id = $db->lastInsertId();

		    $categories = json_decode($_POST['categories']);
		    $stmt = $db->prepare("DELETE FROM `categories_items` WHERE `id_item` =:id", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
			    $stmt->bindParam(':id', $id);
			    $stmt->execute();

		    foreach($categories as $categoryId) {
		    	$stmt = $db->prepare("INSERT INTO `categories_items` (`id_item`, `id_category`) VALUES (:id_item, :id_category)", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
			    $stmt->bindParam(':id_item', $id);
			    $stmt->bindParam(':id_category', $categoryId);
			    $stmt->execute();
		    }
		}

		if(isset($_FILES["file"])){
			$stmt = $db->prepare("SELECT MAX(`id`) FROM `items`", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
			$stmt->execute();
			$row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT);
			$id = $row[0];
			if (!file_exists(__DIR__."\..\content\items\\$id\\")) {
	    		mkdir(__DIR__."\..\content\items\\$id\\", 0777, true);
			}
			$filename = $_FILES['file']['name'];
			if (!move_uploaded_file($_FILES['file']['tmp_name'], __DIR__."\..\content\items\\$id\\$filename"))
				return;

			$serverpath = serverPath();
			$image = $serverpath."content/items/$id/$filename";
			$stmt = $db->prepare("INSERT INTO `items_photos`(`id_item`, `photo`) VALUES (:id, :photo)", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
		    $stmt->bindParam(':id', $id);
		    $stmt->bindParam(':photo', $image);
		    $stmt->execute();
		}
		echo true;
    }
    public function actionGetcategories(){
    	$id = $_GET["id"];
    	$db = \DBConnection::getConnection();
    	$stmt = $db->prepare("SELECT `id_category` FROM `categories_items` WHERE `id_item` = :id", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		$ids = array();
		 while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
		 	array_push($ids, $row[0]);
		 }
		 echo json_encode($ids);
    }
    public function actionDelete(){
    	$id = $_GET["id"];
    	$db = \DBConnection::getConnection();
    	$stmt = $db->prepare("DELETE FROM `items` WHERE `id`=:id", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
		$stmt->bindParam(':id', $id);
		$stmt->execute();
    }
}