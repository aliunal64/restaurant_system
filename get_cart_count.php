<?php
require_once 'functions.php';

header('Content-Type: application/json');

if (is_logged_in()) {
    $count = get_cart_count();
    echo json_encode(['count' => $count]);
} else {
    echo json_encode(['count' => 0]);
}
?>