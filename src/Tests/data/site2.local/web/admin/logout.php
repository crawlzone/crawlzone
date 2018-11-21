<?php
session_name('ss');
session_start();

if (! empty($_SESSION['user'])) {
    unset($_SESSION['user']);
    session_regenerate_id();
}

header('Location: /admin/');
