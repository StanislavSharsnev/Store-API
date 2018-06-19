<?php

namespace app\controllers;

use yii\web\Controller;

require_once '/../db/db.php';
require_once '/../models/CategoryModel.php';
require_once '/../config/params.php';

class CategoriesController extends Controller
{
    public function actionGet()
    {
    	$db = \DBConnection::getConnection();
    	
    	$stmt = $db->prepare("SELECT * FROM `categories`", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
	    $stmt->execute();

	    $categories = array();
	    while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
	    	$category = new \Category();
	    	$category->id = $row[0];
	    	$category->name = $row[1];
	    	$category->description = $row[2];
	    	$category->image = $row[3];
	    	array_push($categories, $category);
	    }

	    echo json_encode($categories);
    }

    public function actionAdd()
    {
		$category = json_decode($_POST['category']);

    	$db = \DBConnection::getConnection();
    	
    	$stmt = $db->prepare("INSERT INTO `categories`(`name`,`description`,`image`) values(:name,:desc,null)", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
	    $stmt->bindParam(':name', $category->name);
	    $stmt->bindParam(':desc', $category->description);
	    $stmt->execute();

	    $id = $db->lastInsertId();

	    $filename = $_FILES['file']['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		$filename = "$id.$ext";
		if (!move_uploaded_file($_FILES['file']['tmp_name'], __DIR__."\..\content\categories\\$filename"))
			return;
		$serverpath = serverPath();
		$image = $serverpath."content/categories/$filename";
		$stmt = $db->prepare("UPDATE `categories` set `image`=:image WHERE `id`=:id", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
	    $stmt->bindParam(':image',$image );
	    $stmt->bindParam(':id', $id);
	    $stmt->execute();

	    echo true;
    }
    public function actionDelete()
    {
		$id = $_GET["id"];

    	$db = \DBConnection::getConnection();
    	
    	$stmt = $db->prepare("DELETE from `categories` WHERE id = :id ", array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
	    $stmt->bindParam(':id', $id);
	    $stmt->execute();
	    echo true;
	}    
}