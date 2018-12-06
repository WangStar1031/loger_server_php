<?php

session_start();
$_SESSION['token'] = "";
header('Location: index.php');
?>