<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

$action = "";
if( isset($_GET['action'])) $action = $_GET['action'];
if( isset($_POST['action'])) $action = $_POST['action'];

require_once __DIR__ . "/library/dbManager.php";

switch ($action) {
	case 'addUser':
		$email = "";
		if( isset($_GET['email'])) $email = $_GET['email'];
		if( isset($_POST['email'])) $email = $_POST['email'];
		$pass = "";
		if( isset($_GET['pass'])) $pass = $_GET['pass'];
		if( isset($_POST['pass'])) $pass = $_POST['pass'];
		if( InsertUser($email, $pass)){
			echo "Inserted.";
		}
		break;
	case 'verifyUser':
		$email = "";
		if( isset($_GET['email'])) $email = $_GET['email'];
		if( isset($_POST['email'])) $email = $_POST['email'];
		$pass = "";
		if( isset($_GET['pass'])) $pass = $_GET['pass'];
		if( isset($_POST['pass'])) $pass = $_POST['pass'];
		echo VerifyUser($email, $pass);
		break;
	case 'addContents':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$contents = "";
		if( isset($_GET['contents'])) $contents = $_GET['contents'];
		if( isset($_POST['contents'])) $contents = $_POST['contents'];
		// echo $token . ":" . $contents;
		echo AddContents($token, $contents);
		break;
	default:
		break;
}
?>