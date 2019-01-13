<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

require_once __DIR__ . "/library/dbManager.php";

if( isset($_POST['token'])){
	$token = $_POST['token'];
	$topicName = $_POST['topicName'];
	$type = $_POST['type'];

	$Id = GetIdFromToken($token);
	if( $Id == 0){
		echo "Invalid Token.";
		exit();
	}
	$destination_path = __DIR__ . "/library/" . $Id . "/" . $topicName . "/" . $type . "/";
	if( !file_exists($destination_path)){
		mkdir($destination_path, 0777);
	}

	$target_path = $destination_path . time() . "_" . basename( $_FILES['uploadFile']['name']);

	if(@move_uploaded_file($_FILES['uploadFile']['tmp_name'], $target_path)) {
		echo "uploaded";
	}
}

?>