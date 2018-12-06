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
</style>
<div class="col-lg-12">
	<div style="float: right;">
		<a href="logout.php">Logout</a>
	</div>
	<br>
	<h2>My contents</h2>
	<p>There are <?=count($myData)?> records.</p>
	<div class="contents">
		<ul>
			<?php
				$_i = 0;
				foreach ($myData as $value) {
					$_i++;
			?>
			<li><a href="<?=$value?>" target="_blank"><?=basename($value)?></a></li>
			<?php
				}
			?>
		</ul>
	</div>
</div>
<style type="text/css">
	.purchaseModal{
		width: 400px !important;
	}
	.purchaseModal table label{
		color: black;
	}
	#payment-form{
		margin-bottom: 0px;
	}
	.purchaseModal .modal-title{
		color: #0079cb;
	}
</style>

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