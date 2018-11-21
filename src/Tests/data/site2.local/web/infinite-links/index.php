<?php

$id = $_GET['id'] ?: 0;

$id++;

?>

<a href="/infinite-links/?id=<?= $id ?>">Link ID: <?= $id ?></a>
