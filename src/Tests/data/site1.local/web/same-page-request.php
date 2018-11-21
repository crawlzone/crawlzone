<a href="?productId=1">product 1</a>

<?php

if (! empty($_GET['productId'])) {
    echo "Product 1 ID: " . $_GET['productId'];
}
