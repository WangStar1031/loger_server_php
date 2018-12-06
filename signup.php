<link rel="stylesheet" type="text/css" href="./assets/bootstrap/css/bootstrap.min.css">
<script src="./assets/jquery/jquery-3.2.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="./assets/admin_page.css?<?=time()?>">
<?php
$eMail = "";
if( isset($_POST['eMail'])) $eMail = $_POST['eMail'];
$password = "";
if( isset($_POST['password'])) $password = $_POST['password'];
require_once __DIR__ . "/library/dbManager.php";
$errorCode = "";
if( $eMail != ""){
	$errorCode = InsertUser( $eMail, $password);
	if( $errorCode === true){
		header("Location: index.php");
	}
}

?>
<div class="auth_main">
	<div class="auth_block">
		<h3 style="font-size: 1.75em;text-align: center;">Sign up</h3>
		<p <?php if($errorCode=="")echo "style='display:none;'" ?>><?= $errorCode?></p>
		<form method="post" onsubmit="return SubmitForm();">
			<table>
				<tr>
					<td><label for="email">Email</label></td>
					<td><input class="form_control" type="text" name="eMail"></td>
				</tr>
				<tr>
					<td><label for="password">Password</label></td>
					<td><input class="form_control" type="password" name="password"></td>
				</tr>
				<tr>
					<td><label for="password">Confirm Password</label></td>
					<td><input class="form_control" type="password" name="confirm_password"></td>
				</tr>
				<tr>
					<td><button>Sign up</button></td>
					<td><a href="index.php">Log in</a></td>
				</tr>
			</table>
		</form>
	</div>
</div>
<script type="text/javascript">
	function SubmitForm(){
		if( $("input[name=eMail]").val() == "")return false;
		if( $("input[name=password]").val() == "")return false;
		if( $("input[name=confirm_password]").val() == "")return false;
		if( $("input[name=password]").val() != $("input[name=confirm_password]").val())return false;
		return true;
	}
</script>