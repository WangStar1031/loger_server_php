<script src="./assets/jquery/jquery-3.2.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="./assets/admin_page.css?<?=time()?>">
<link rel="stylesheet" href="./assets/bootstrap/css/bootstrap.min.css">

<?php
session_start();

require_once __DIR__ . "/library/dbManager.php";
// print_r($_SERVER['REQUEST_URI']);
if( isset($_POST['userName'])){
	$userName = $_POST['userName'];
	$userPass = "";
	if( isset($_POST)){
		$userPass = $_POST['userPass'];
	}
	$_token = VerifyUser($userName, $userPass);
	if( $_token != ""){
		$_SESSION['token'] = $_token;
	}
}
$token = "";
if( isset($_SESSION['token'])) $token = $_SESSION['token'];
if( $token != ""){
	$myData = GetAllContentsFromToken($token);
?>
<style type="text/css">
	li{
		list-style: none;
	}
	li.active{
		background-color: red;
	}
	.topics{
		float: left;
	}
	.HideItem{
		display: none;
	}
</style>
<div class="col-lg-12">
	<div style="float: right;">
		<a href="logout.php">Logout</a>
	</div>
	<br>
	<h2>My contents</h2>
	<p>There are <?=count($myData)?> topics.</p>
	<div class="topic_list button-group">
		<ul>
			<?php
				$_i = 0;
				foreach ($myData as $value) {
					$_i++;
					// print_r($value);
			?>
			<li class="topics" id="topics<?=$_i?>"><div class="btn <?=$_i==1?'btn-primary':'btn-secondary'?>" onclick="btnClicked(<?=$_i?>)"><?=$value->topic?></div></li>
			<?php
				}
			?>
		</ul>
		<div style="clear: both;"></div>
	</div>
	<div class="contents_list">
		<?php
		$_i = 0;
		foreach ($myData as $value) {
			$_i++;
		?>
		<ul class="contents <?=$_i==1?'':'HideItem'?>" id="contents<?=$_i?>">
			<?php
			foreach ($value->urls as $url) {
			?>
			<li><a href="<?=$url?>" target='_blank'><?=basename($url)?></a></li>
			<?php
			}
			?>
		</ul>
		<?php
		}
		?>
	</div>
</div>
<script type="text/javascript">
	function btnClicked(_i){
		$(".btn-primary").removeClass("btn-primary").addClass("btn-secondary");
		$("#topics" + _i).find(".btn").removeClass("btn-secondary").addClass("btn-primary");
		$(".contents").addClass('HideItem');
		$("#contents" + _i).removeClass('HideItem');
	}
</script>
<?php
} else{
?>
<div class="auth_main">
	<div class="auth_block">
		<h3 style="font-size: 1.75em;text-align: center;">Sign in to Key manager</h3>
		<form method="post" class="login">
			<table>
				<tr>
					<td><label for="userName">User Name</label></td>
					<td><input class="form_control" type="text" name="userName"></td>
				</tr>
				<tr>
					<td><label for="userPass">Password</label></td>
					<td><input class="form_control" type="password" name="userPass"></td>
				</tr>
				<tr>
					<td><button>Log in</button></td>
					<td><a href="signup.php">Sign up</a></td>
				</tr>
			</table>
		</form>
	</div>
</div>
<?php
}
?>