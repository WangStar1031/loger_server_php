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
	function InsertUser($_email, $_pass){
		global $db;
		$pass = base64_encode( $_pass);
		$_token = makeEncryptKey($_pass);
		$sql = "SELECT * FROM users WHERE Email='$_email'";
		$record = $db->select($sql);
		if( $record ){
			return false;
		}
		$sql = "INSERT INTO users(Email, Password, Token) VALUES ( ?, ?, ?)";
		$stmt = $db->prepare($sql);
		$retVal = $stmt->execute([$_email, $pass, $_token]);

		$sql = "SELECT * FROM users WHERE Email='$_email'";
		$record = $db->select($sql);
		$dirName = $record[0]["Id"];
		mkdir(__DIR__ . "/" . $dirName);

		return true;
	}
	function VerifyUser($_email, $_pass){
		global $db;
		$sql = "SELECT * from users WHERE Email='$_email'";
		$record = $db->select($sql);
		if( $record ){
			foreach ($record as $value) {
				$pass = base64_decode($value["Password"]);
				if( strcasecmp( $pass, $_pass) == 0){
					// echo($value["Token"]);
					return $value["Token"];
				}
			}
			return "";
		}
		return "";
	}
	function AddContents($_token, $_contents){
		global $db;
		$Id = GetIdFromToken($_token);
		if( $Id == 0){
			return "";
		}
		// echo $Id;
		// $Id = $user["Id"];
		$time = time();
		// $
		file_put_contents(__DIR__ . "/" . $Id . "/" . $time . ".html", $_contents);
		return $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] .dirname( $_SERVER['REQUEST_URI']) . "/library/" . $Id . "/" . $time . ".html";
	}
	function GetAllUrls($_Id){
		$dirName = __DIR__ . "/" . $_Id . "/";
		if(!file_exists($dirName))return [];
		$files = scandir($dirName);
		if( $files == FALSE)
			return [];
		$urls = [];
		foreach ($files as $value) {
			if( is_dir($value))
				continue;
			$urls[] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] .dirname( $_SERVER['REQUEST_URI']) . "/library/" . $_Id . "/" . $value;
		}
		return $urls;
	}
	function GetIdFromEmail($_email){
		global $db;
		$sql = "SELECT Id FROM users WHERE Email='$_email'";
		$record = $db->select($sql);
		if( $record){
			return $record[0]['Id'];
		}
		return 0;
	}
	function GetIdFromToken($_token){
		global $db;
		$sql = "SELECT Id FROM users WHERE Token='$_token';";
		$record = $db->select($sql);
		if( $record){
			return $record[0]["Id"];
		}
		return 0;
	}
	function GetAllContentsFromEmail($_email){
		$Id = GetIdFromEmail($_email);
		if( $Id == 0)
			return [];
		return GetAllUrls($Id);
	}
	function GetAllContentsFromToken($_token){
		$Id = GetIdFromToken($_token);
		if( $Id == 0)
			return [];
		return GetAllUrls($Id);
	}

?>