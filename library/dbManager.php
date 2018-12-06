<?php
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	ini_set('implicit_flush', 1);
	ob_implicit_flush(true);
	set_time_limit(0);

	define("DB_TYPE", "mysql");
	define("DB_HOST", "localhost");
	define("DB_NAME", "admin_graber");

	if(@file_get_contents(__DIR__."/localhost")){
		define("DB_USER", "root");
		define("DB_PASSWORD", "");
	}
	else{
		define("DB_USER", "admin_user");
		define("DB_PASSWORD", "graber");
	}

	require_once __DIR__ . "/mysql.php";

	$db = new Mysql();
	$db->exec("set names utf8");

	function makeEncryptKey($_keyword){
		if( $_keyword == "")return "";
		$_key1 = crypt(time(), "");
		$_key2 = crypt($_keyword, "");
		$key =  $_key1 . $_key2;
		$key = str_replace("$", "", $key);
		$key = str_replace(".", "", $key);
		$key = str_replace("/", "", $key);
		return $key;
	}
echo crypt("asdfasdf", "");
	function InsertUser($_email, $_pass){
		global $db;
		$sql = "INSERT INTO users(Email, Password, Token) VALUES ( ?, ?, ?)";
		$stmt = $db->prepare($sql);
		$_token = makeEncryptKey($_email);

		$stmt->execute([$_email, $_pass, $_token]);

	}
?>