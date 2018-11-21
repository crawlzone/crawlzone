<?php
session_name('ss');
session_start();

if ($_SESSION['user'] === null) {
    header('Location: login.php');
}

?>

<h1>This is restricted area to manage users</h1>

<a href="/admin/">Home</a><br>