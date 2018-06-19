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
}