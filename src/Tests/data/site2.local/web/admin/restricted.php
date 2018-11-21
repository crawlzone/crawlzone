<?php
session_name('ss');
session_start();

if ($_SESSION['user'] === null) {
    header('Location: login.php');
    exit;
}

?>

<h1>This is restricted area</h1>

<a href="/admin/">Home</a><br>
<a href="logout.php">Logout</a><br>
