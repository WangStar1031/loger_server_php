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
					return $value["Token"];
				}
			}
			return "";
		}
		return "";
	}
	function getDomainUrl($url){
		$_scheme = parse_url($url, PHP_URL_SCHEME);
		$_user = parse_url($url, PHP_URL_USER);
		$_pass = parse_url($url, PHP_URL_PASS);
		$_host = parse_url($url, PHP_URL_HOST);
		$_port = parse_url($url, PHP_URL_PORT);
		$_path = parse_url($url, PHP_URL_PATH);
		$_query = parse_url($url, PHP_URL_QUERY);
		$_fragment = parse_url($url, PHP_URL_FRAGMENT);
		$retVal = $_scheme . "://";
		if( $_user){
			$retVal .= $_user . ":";
			if( $_pass){
				$retVal .= $_pass . "@";
			}
		}
		if( $_host){
			$retVal .= $_host;
		}
		if( $_port){
			$retVal .= ":" . $_port;
		}
		return $retVal;
	}
	function AddTopic($token, $topic){
		global $db;
		$Id = GetIdFromToken($token);
		if( $Id == 0){
			return "Invalid token";
		}
		$arrTopics = explode("%%", $topic);
		foreach ($arrTopics as $value) {
			if( !file_exists(__DIR__ . "/" . $Id . "/" . $value . "/")){
				mkdir(__DIR__ . "/" . $Id . "/" . $value . "/");
			}
		}
		return "Done.";
	}
	function AddContents($_token, $_contents, $_topic){
		global $db;
		$Id = GetIdFromToken($_token);
		// echo($_token);
		if( $Id == 0){
			return "Invalid token";
		}
		$_contents = urldecode($_contents);
		$arrContents = explode("|||||", $_contents);
		if( count($arrContents) < 2)return " less than 2 " . json_encode($arrContents);
		$_url = $arrContents[0];
		$texts = $arrContents[1];
		$texts = str_replace('href="/', 'href="' . getDomainUrl($_url) . "/", $texts);
		$texts = str_replace('src="/', 'src="' . getDomainUrl($_url) . "/", $texts);

		$time = time();
		if( !file_exists(__DIR__ . "/" . $Id . "/" . $_topic . "/")){
			mkdir(__DIR__ . "/" . $Id . "/" . $_topic . "/");
		}
		$urls = @file_get_contents(__DIR__ . "/" . $Id . "/" . $_topic . ".json");
		$arrUrls = [];
		if( $urls ){
			$arrUrls = explode("\n", $urls);
		}
		foreach ($arrUrls as $url) {
			if( strcasecmp($url, $_url) == 0){
				return "same url";
			}
		}
		$arrUrls[] = $_url;
		$urls = implode("\n", $arrUrls);
		file_put_contents(__DIR__ . "/" . $Id . "/" . $_topic . ".json", $urls);
		file_put_contents(__DIR__ . "/" . $Id . "/" . $_topic . "/" . $time . ".html", $texts);
		return $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['REQUEST_URI']) . "/library/" . $Id . "/" . $_topic . "/" . $time . ".html";
	}
	function GetAllUrls($_Id){
		$dirName = __DIR__ . "/" . $_Id . "/";
		if(!file_exists($dirName))return [];
		$files = scandir($dirName);
		if( $files == FALSE)
			return [];
		$urls = [];
		foreach ($files as $value) {
			if( $value == "." || $value == ".."){
				continue;
			}
			if( is_dir($dirName . $value)){
				$topicObj = new \stdClass;
				$subDirName = $dirName . $value . "/";
				$subFiles = scandir($subDirName);
				$topicObj->topic = $value;
				$topicObj->urls = [];
				foreach ($subFiles as $subValue) {
					if( is_dir($subDirName . $subValue))continue;
					$url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['REQUEST_URI']) . "/library/" . $_Id . "/" . $value . "/" . $subValue;
					$topicObj->urls[] = $url;
				}
				$urls[] = $topicObj;
			}
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