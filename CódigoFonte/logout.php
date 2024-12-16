<?php
session_start();
//DESLOGAR DA TELA3.PHP
$_SESSION = array();

session_destroy();

header("Location: tela1.php");
exit();
?>
