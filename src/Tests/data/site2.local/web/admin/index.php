<?php
session_name('ss');
session_start();

if ($_SESSION['user'] === null) {
    header('Location: login.php');
    exit;
}

var_dump(session_id());
?>

<h1>Welcome <?= $_SESSION['user'] ?></h1>

<a href="restricted.php">Restricted area</a><br>


