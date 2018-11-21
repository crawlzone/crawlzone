<?php
session_name('ss');
session_start();

if (! empty($_SESSION['user'])) {
    header('Location: /admin/');
}


if (! empty($_POST['username']) && ! empty($_POST['password'])) {
    if ($_POST['username'] === 'test' && $_POST['password'] === 'password') {
        session_regenerate_id(true);
        $_SESSION['user'] = $_POST['username'];
        header('Location: /admin/');
        exit;
    } else {
        echo 'Invalid login or password<br>';
    }
}

?>

<h1>This is admin section</h1> <p>Please login</p>
<form action="login.php" method="post">
    <input type="text" placeholder="enter username" name="username">
    <input type="password" placeholder="enter password" name="password">
    <input type="submit" value="submit">
</form>
