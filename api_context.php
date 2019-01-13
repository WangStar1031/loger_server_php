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
	case 'removeContent':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$topicName = "";
		if( isset($_GET['topicName'])) $topicName = $_GET['topicName'];
		if( isset($_POST['topicName'])) $topicName = $_POST['topicName'];
		$id = "";
		if( isset($_GET['id'])) $id = $_GET['id'];
		if( isset($_POST['id'])) $id = $_POST['id'];
		if( RemoveContent($token, $topicName, $id)) echo "Removed.";
		break;
	case 'removeUser':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		if( RemoveUser($token)) echo "Removed.";
		break;
	case 'getTopics':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		echo getTopics($token);
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
	case 'addTopic':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$topic = "";
		if( isset($_GET['topic'])) $topic = $_GET['topic'];
		if( isset($_POST['topic'])) $topic = $_POST['topic'];
		echo AddTopic($token, $topic);
		break;
	case 'addContents':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$contents = "";
		if( isset($_GET['contents'])) $contents = $_GET['contents'];
		if( isset($_POST['contents'])) $contents = $_POST['contents'];
		$topic = "";
		if( isset($_GET['topic'])) $topic = $_GET['topic'];
		if( isset($_POST['topic'])) $topic = $_POST['topic'];
		echo AddContents($token, $contents, $topic);
		break;
	case 'getAllUrls':
		$email = "";
		if( isset($_GET['email'])) $email = $_GET['email'];
		if( isset($_POST['email'])) $email = $_POST['email'];
		$pass = "";
		if( isset($_GET['pass'])) $pass = $_GET['pass'];
		if( isset($_POST['pass'])) $pass = $_POST['pass'];
		$token = VerifyUser($email, $pass);
		$contents = GetAllContentsFromToken($token);
		echo json_encode($contents);
		break;
	case 'removeTodo':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$topicName = "";
		if( isset($_GET['topicName'])) $topicName = $_GET['topicName'];
		if( isset($_POST['topicName'])) $topicName = $_POST['topicName'];
		$id = "";
		if( isset($_GET['id'])) $id = $_GET['id'];
		if( isset($_POST['id'])) $id = $_POST['id'];
		if( removeTodo($token, $topicName, $id))echo "OK";
		break;
	case 'insertTodo':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$topic = "";
		if( isset($_GET['topic'])) $topic = $_GET['topic'];
		if( isset($_POST['topic'])) $topic = $_POST['topic'];
		$todoText = "";
		if( isset($_GET['todoText'])) $todoText = $_GET['todoText'];
		if( isset($_POST['todoText'])) $todoText = $_POST['todoText'];
		echo( insertTodo($token, $topic, $todoText));
		break;
	case 'removeNote':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$topicName = "";
		if( isset($_GET['topicName'])) $topicName = $_GET['topicName'];
		if( isset($_POST['topicName'])) $topicName = $_POST['topicName'];
		$id = "";
		if( isset($_GET['id'])) $id = $_GET['id'];
		if( isset($_POST['id'])) $id = $_POST['id'];
		if( removeNote($token, $topicName, $id))echo "OK";
		break;
	case 'insertNote':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$topic = "";
		if( isset($_GET['topic'])) $topic = $_GET['topic'];
		if( isset($_POST['topic'])) $topic = $_POST['topic'];
		$NoteText = "";
		if( isset($_GET['NoteText'])) $NoteText = $_GET['NoteText'];
		if( isset($_POST['NoteText'])) $NoteText = $_POST['NoteText'];
		echo( insertNote($token, $topic, $NoteText));
		break;
	case 'getAllWithToken':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$contents = GetAllContentsFromToken($token);
		echo json_encode($contents);
		break;
	case 'getTopicInfo':
		$token = "";
		if( isset($_GET['token'])) $token = $_GET['token'];
		if( isset($_POST['token'])) $token = $_POST['token'];
		$topicName = "";
		if( isset($_GET['topicName'])) $topicName = $_GET['topicName'];
		if( isset($_POST['topicName'])) $topicName = $_POST['topicName'];
		echo json_encode(getTopicInfo($token, $topicName));
		break;
	default:
		break;
}
?>