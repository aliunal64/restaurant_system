<?php
require_once 'functions.php';

header('Content-Type: application/json');

$response = array('success' => false, 'message' => '', 'cart_count' => 0);

if (!is_logged_in()) {
    $response['message'] = 'Please login to add items to cart.';
    echo json_encode($response);
    exit();
}

// Prevent admin from using cart features
if (is_admin()) {
    $response['message'] = 'Admin users cannot use cart functionality. Please use the admin panel to manage the system.';
    echo json_encode($response);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($product_id > 0 && $quantity > 0) {
            add_to_cart($product_id, $quantity);
            $response['success'] = true;
            $response['message'] = 'Product added to cart successfully!';
            $response['cart_count'] = get_cart_count();
        } else {
            $response['message'] = 'Invalid product or quantity.';
        }
        break;
        
    case 'update':
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if (update_cart_quantity($product_id, $quantity)) {
            $response['success'] = true;
            $response['message'] = 'Cart updated successfully!';
            $response['cart_count'] = get_cart_count();
            $response['total'] = number_format(get_cart_total(), 2);
            $response['delivery_fee'] = number_format(get_delivery_fee(get_cart_total()), 2);
            $response['final_total'] = number_format(get_cart_total_with_delivery(), 2);
        } else {
            $response['message'] = 'Failed to update cart.';
        }
        break;
        
    case 'remove':
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if (remove_from_cart($product_id)) {
            $response['success'] = true;
            $response['message'] = 'Product removed from cart!';
            $response['cart_count'] = get_cart_count();
            $response['total'] = number_format(get_cart_total(), 2);
            $response['delivery_fee'] = number_format(get_delivery_fee(get_cart_total()), 2);
            $response['final_total'] = number_format(get_cart_total_with_delivery(), 2);
        } else {
            $response['message'] = 'Failed to remove product.';
        }
        break;
        
    default:
        $response['message'] = 'Invalid action.';
}

echo json_encode($response);
?>