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
		mkdir(__DIR__ . "/" . $dirName . "/" . "Default");
		$data = [];
		$_default = new \stdClass;
		$_default->topicName = "Default";
		$_default->createdTime = date("Y-F-d H:i a");
		$data[] = $_default;
		file_put_contents(__DIR__ . "/" . $dirName . "/". "topicInfo.dat", json_encode($data));
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
	function delTree($dir) { 
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file) { 
			(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
		} 
		return rmdir($dir); 
	} 
	function RemoveUser($_token){
		global $db;
		$userID = GetIdFromToken($_token);
		if( $userID == 0){
			echo "Invalid Token.";
			return false;
		}
		// rmdir(__DIR__ . "/" . $userID);
		if(file_exists(__DIR__ . "/" . $userID))
			delTree(__DIR__ . "/" . $userID);
		$sql = "DELETE from users WHERE Id='$userID'";
		$db->__exec__($sql);
		return true;
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
	function getTopics($token){
		$Id = GetIdFromToken($token);
		if( $Id == 0){
			return "";
		}
		$fileName = __DIR__ . "/" . $Id . "/" . "topicInfo.dat";
		$contents = file_get_contents( $fileName);
		$arrTopics = json_decode($contents);
		$arrTopicNames = [];
		foreach ($arrTopics as $value) {
			$arrTopicNames[] = $value->topicName;
		}
		return implode("%%", $arrTopicNames);
	}
	function AddTopic($token, $topic){
		$Id = GetIdFromToken($token);
		if( $Id == 0){
			return "Invalid token.";
		}
		$fileName = __DIR__ . "/" . $Id . "/" . "topicInfo.dat";
		$contents = file_get_contents( $fileName);
		$arrTopics = json_decode($contents);
		foreach ($arrTopics as $value) {
			if( strcasecmp($value->topicName, $topic) == 0){
				return "Existing topic.";
			}
		}
		$curTopic = new \stdClass;
		$curTopic->topicName = $topic;
		$curTopic->createdTime = date("Y-F-d H:i a");
		$arrTopics[] = $curTopic;
		file_put_contents($fileName, json_encode($arrTopics));
		mkdir(__DIR__ . "/" . $Id . "/" . $topic . "/");
		return "OK";
	}
	function getTopicInfo($token, $topicName){
		$Id = GetIdFromToken($token);
		if( $Id == 0){
			return "";
		}
		$dirName = __DIR__ . "/" . $Id . "/";
		$contents = file_get_contents($dirName . "topicInfo.dat");
		$arrTopics = json_decode($contents);
		$retVal = new \stdClass;
		foreach ($arrTopics as $value) {
			if( strcasecmp($topicName, $value->topicName) == 0){
				$retVal->topicName = $topicName;
				$retVal->createdTime = $value->createdTime;
				$retVal->viewedCount = 0;
				if( file_exists($dirName . $topicName . "/contents/contents.json")){
					$views = json_decode(file_get_contents($dirName . $topicName . "/contents/contents.json"));
					$retVal->viewedCount = count($views);
				}
				$retVal->photoTagged = 0;
				$retVal->photoUrls = [];
				if( file_exists($dirName . $topicName . "/photos/")){
					$photoDir = $dirName . $topicName . "/photos/";
					$photos = scandir($photoDir);
					foreach ($photos as $value) {
						if( $value == ".." || $value ==".")
							continue;
						$retVal->photoUrls[] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['REQUEST_URI']) . "/library/" . $Id . "/" . $topicName . "/" . "photos/" . $value;
					}
					$retVal->photoTagged = count($retVal->photoUrls);
					// $views = json_decode(file_get_contents($dirName . $topicName . "/photos/photos.json"));
					// $retVal->viewedCount = count($views);
				}
				$retVal->todolist = [];
				if( file_exists($dirName . $topicName . "/todolist.json")){
					$todolist = json_decode(file_get_contents($dirName . $topicName . "/todolist.json"));
					$retVal->todolist = $todolist;
				}
				$retVal->notelist = [];
				if( file_exists($dirName . $topicName . "/notelist.json")){
					$notelist = json_decode(file_get_contents($dirName . $topicName . "/notelist.json"));
					$retVal->notelist = $notelist;
				}
				$retVal->noteCount = count($retVal->notelist);
				$retVal->attachementsUrls = [];
				if( file_exists($dirName . $topicName . "/attachements/")){
					$photoDir = $dirName . $topicName . "/attachements/";
					$files = scandir($photoDir);
					foreach ($files as $value) {
						if( $value == ".." || $value ==".")
							continue;
						$retVal->attachementsUrls[] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['REQUEST_URI']) . "/library/" . $Id . "/" . $topicName . "/" . "attachements/" . $value;
					}
				}
			}
		}
		return $retVal;
	}
	function AddContents($_token, $_contents, $_topic){
		global $db;
		$Id = GetIdFromToken($_token);
		if( $Id == 0){
			return "Invalid token";
		}
		$_contents = urldecode($_contents);
		$arrContents = explode("|||||", $_contents);
		if( count($arrContents) < 2)return " less than 2 " . json_encode($arrContents);
		$_url = $arrContents[0];
		$texts = $arrContents[1];
		$title = "";
		if( count($arrContents) > 2)
			$title = $arrContents[2];
		$image_data = "";
		if( count($arrContents) > 3){
			$image = $arrContents[3];
			$data = explode(",", $image);
			$image_data = base64_decode($data[1]);
		}
		$texts = str_replace('href="/', 'href="' . getDomainUrl($_url) . "/", $texts);
		$texts = str_replace('src="/', 'src="' . getDomainUrl($_url) . "/", $texts);

		$time = time();
		if( !file_exists(__DIR__ . "/" . $Id . "/" . $_topic . "/")){
			mkdir(__DIR__ . "/" . $Id . "/" . $_topic . "/");
		}
		$content_dir = __DIR__ . "/" . $Id . "/" . $_topic . "/" . "contents/";
		if( !file_exists($content_dir)){
			mkdir($content_dir);
		}
		$buf = @file_get_contents($content_dir . "contents.json");
		$arrContents = [];
		if( $buf){
			$arrBuff = json_decode($buf);
			foreach ($arrBuff as $value) {
				$arrContents[] = $value;
			}
		}
		foreach ($arrContents as $value) {
			if( strcasecmp( $value->url, $_url) == 0){
				return "same url.";
			}
		}
		$curContent = new \stdClass;

		$curContent->id = $time;
		$curContent->time = date("F-d, Y H:i a", $time);
		$curContent->url = $_url;
		$curContent->title = $title;

		$arrContents[] = $curContent;
		file_put_contents($content_dir . "contents.json", json_encode($arrContents));
		file_put_contents($content_dir . $time . ".html", $texts);
		file_put_contents($content_dir . $time . ".jpg", $image_data);

		return $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['REQUEST_URI']) . "/library/" . $Id . "/" . $_topic . "/contents/" . $time . ".html";
	}
	function RemoveContent($token, $topicName, $id){
		$_Id = GetIdFromToken($token);
		if( $_Id == 0)
			return false;
		$dir = __DIR__ . "/" . $_Id . "/" . $topicName . "/contents/";
		if( file_exists( $dir . $id . ".html")){
			unlink($dir . $id . ".html");
		}
		if( file_exists( $dir . $id . ".jpg")){
			unlink($dir . $id . ".jpg");
		}
		$contents = file_get_contents($dir . "contents.json");
		if( !$contents)return false;
		$arrContents = json_decode($contents);

		for ($i = 0; $i < count($arrContents); $i++) { 
			if( $arrContents[$i]->id == $id){
				unset($arrContents[$i]);
				file_put_contents($dir . "contents.json", json_encode($arrContents));
				return true;
			}
		}
		return false;
	}
	function GetAllUrls($_Id){
		$urls = [];
		$dirName = __DIR__ . "/" . $_Id . "/";
		if(!file_exists($dirName))return [];
		$topicInfo = json_decode(file_get_contents(__DIR__ . "/" . $_Id . "/" . "topicInfo.dat"));
		foreach ($topicInfo as $value) {
			$topicName = $value->topicName;
			$createdTime = $value->createdTime;
			$topicObj = new \stdClass;
			$topicObj->topic = $topicName;
			$topicObj->createdTime = $createdTime;
			$topicObj->urls = [];
			$dir = __DIR__ . "/" . $_Id . "/" . $topicName;
			$contents = @file_get_contents($dir . "/contents/contents.json");
			if( !empty($contents)){
				$arrContents = json_decode($contents);
				foreach ($arrContents as $value_1) {
					$content = new \stdClass;
					$content->id = $value_1->id;
					$content->time = $value_1->time;
					$content->originalUrl = $value_1->url;
					$content->title = $value_1->title;
					$content->image = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['REQUEST_URI']) . "/library/" . $_Id . "/" . $topicName . "/" . "contents/" . $content->id . ".jpg";
					$content->url = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['REQUEST_URI']) . "/library/" . $_Id . "/" . $topicName . "/" . "contents/" . $content->id . ".html";
					$topicObj->urls[] = $content;
				}
			}
			$urls[] = $topicObj;
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
	function insertNote($token, $topic, $NoteText){
		$Id = GetIdFromToken($token);
		if( $Id == 0)
			return "";
		$fileName = __DIR__ . "/" . $Id . "/" . $topic . "/notelist.json";
		$fileContents = @file_get_contents($fileName);
		$arrTodos = [];
		if( $fileContents){
			$arrTodos = json_decode($fileContents);
		}
		$todo = new \stdClass;
		$todo->id = time();
		$todo->content = $NoteText;
		$arrTodos[] = $todo;
		file_put_contents($fileName, json_encode($arrTodos));
		return $todo->id;
	}
	function removeNote($token, $topic, $id){
		$Id = GetIdFromToken($token);
		if( $Id == 0)
			return false;
		$fileName = __DIR__ . "/" . $Id . "/" . $topic . "/notelist.json";
		$fileContents = @file_get_contents($fileName);
		// $arrTodos = [];
		if( $fileContents){
			$arrTodos = json_decode($fileContents);
			$arrResults = [];
			foreach ($arrTodos as $value) {
				if( $value->id != $id){
					$arrResults[] = $value;
				}
			}
			file_put_contents($fileName, json_encode($arrResults));
			return true;
			// for( $i = 0; $i < count($arrTodos); $i++){
			// 	if( $arrTodos[$i]->id == $id){
			// 		unset($arrTodos[$i]);
			// 		file_put_contents($fileName, json_encode($arrTodos));
			// 		return true;
			// 	}
			// }
		}  else{
			return false;
		}
		return false;
	}
	function insertTodo($token, $topic, $todoText){
		$Id = GetIdFromToken($token);
		if( $Id == 0)
			return "";
		$fileName = __DIR__ . "/" . $Id . "/" . $topic . "/todolist.json";
		$fileContents = @file_get_contents($fileName);
		$arrTodos = [];
		if( $fileContents){
			$arrTodos = json_decode($fileContents);
		}
		$todo = new \stdClass;
		$todo->id = time();
		$todo->content = $todoText;
		$arrTodos[] = $todo;
		file_put_contents($fileName, json_encode($arrTodos));
		return $todo->id;
	}
	function removeTodo($token, $topic, $id){
		$Id = GetIdFromToken($token);
		if( $Id == 0)
			return false;
		$fileName = __DIR__ . "/" . $Id . "/" . $topic . "/todolist.json";
		$fileContents = @file_get_contents($fileName);
		// $arrTodos = [];
		if( $fileContents){
			$arrTodos = json_decode($fileContents);
			$arrResults = [];
			foreach ($arrTodos as $value) {
				if( $value->id != $id){
					$arrResults[] = $value;
				}
			}
			file_put_contents($fileName, json_encode($arrResults));
			return true;
			// for( $i = 0; $i < count($arrTodos); $i++){
			// 	if( $arrTodos[$i]->id == $id){
			// 		unset($arrTodos[$i]);
			// 		file_put_contents($fileName, json_encode($arrTodos));
			// 		return true;
			// 	}
			// }
		}  else{
			return false;
		}
		return false;
	}
?>