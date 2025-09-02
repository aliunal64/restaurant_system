<?php
require_once 'config.php';

// Delivery Fee Configuration
define('DELIVERY_FEE', 2.00);
define('FREE_DELIVERY_THRESHOLD', 30.00);

// Get delivery fee based on cart total
function get_delivery_fee($cart_total = 0) {
    if ($cart_total >= FREE_DELIVERY_THRESHOLD) {
        return 0;
    }
    return DELIVERY_FEE;
}

// Asset URL helper
function asset($path) {
    return BASE_URL . 'assets/' . $path;
}

// Image or placeholder helper
function image_or_placeholder($img, $type = 'menu') {
    if ($img) {
        // Check if we're in admin directory or root directory
        $image_path_root = "assets/images/{$type}/" . $img;
        $image_path_admin = "../assets/images/{$type}/" . $img;
        
        // Try root directory first, then admin directory
        if (file_exists($image_path_root)) {
            return BASE_URL . "assets/images/{$type}/" . $img;
        } elseif (file_exists($image_path_admin)) {
            return BASE_URL . "assets/images/{$type}/" . $img;
        }
    }
    
    // Return placeholder or default food image
    if (file_exists("assets/images/placeholder.jpg") || file_exists("../assets/images/placeholder.jpg")) {
        return BASE_URL . "assets/images/placeholder.jpg";
    }
    
    // Fallback to a data URL for a simple gray placeholder
    return "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f8f9fa'/%3E%3Ctext x='50' y='50' text-anchor='middle' dy='.3em' fill='%236c757d' font-family='Arial, sans-serif' font-size='12'%3ENo Image%3C/text%3E%3C/svg%3E";
}

// Sepete ürün ekleme
function add_to_cart($product_id, $quantity) {
    // Prevent admin from using cart
    if (is_admin()) {
        return false;
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    return true;
}

// Sepetteki ürün sayısını getir
function get_cart_count() {
    // Admin users should not have cart functionality
    if (is_admin()) {
        return 0;
    }
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    return array_sum($_SESSION['cart']);
}

// Sepetteki ürünleri getir
function get_cart_items() {
    global $pdo;
    
    // Admin users should not have cart functionality
    if (is_admin()) {
        return array();
    }
    
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return array();
    }
    
    $items = array();
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            $product['quantity'] = $quantity;
            $product['subtotal'] = $product['price'] * $quantity;
            $items[] = $product;
        }
    }
    
    return $items;
}

// Sepet toplamını hesapla
function get_cart_total() {
    $items = get_cart_items();
    $total = 0;
    
    foreach ($items as $item) {
        $total += $item['subtotal'];
    }
    
    return $total;
}

// Sepet toplamını kargo ücreti ile birlikte hesapla
function get_cart_total_with_delivery() {
    $cart_total = get_cart_total();
    return $cart_total + get_delivery_fee($cart_total);
}

// Sepetten ürün çıkar
function remove_from_cart($product_id) {
    // Prevent admin from using cart
    if (is_admin()) {
        return false;
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    return false;
}

// Sepeti temizle
function clear_cart() {
    // Prevent admin from using cart
    if (is_admin()) {
        return false;
    }
    
    $_SESSION['cart'] = array();
    return true;
}

// Sepetteki ürün miktarını güncelle
function update_cart_quantity($product_id, $quantity) {
    // Prevent admin from using cart
    if (is_admin()) {
        return false;
    }
    
    if ($quantity <= 0) {
        return remove_from_cart($product_id);
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
        return true;
    }
    
    return false;
}

function create_order($delivery_address, $phone, $payment_method) {
    global $pdo;
    
    // Prevent admin from creating orders
    if (is_admin()) {
        return false;
    }
    
    $cart_items = get_cart_items();
    if (empty($cart_items)) {
        return false;
    }
    
    // Validate required session data
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Add delivery fee based on cart total
    $total += get_delivery_fee($total);
    
    try {
        $pdo->beginTransaction();
        
        // Create order with payment method
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, delivery_address, phone, payment_method) VALUES (?, ?, 'pending', ?, ?, ?)");
        $result = $stmt->execute([$_SESSION['user_id'], $total, $delivery_address, $phone, $payment_method]);
        
        if (!$result) {
            $pdo->rollBack();
            return false;
        }
        
        $order_id = $pdo->lastInsertId();
        
        // Add order items
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            
            if (!$result) {
                $pdo->rollBack();
                return false;
            }
        }
        
        $pdo->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        return $order_id;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

// Success message helper
function set_success_message($message) {
    $_SESSION['success_message'] = $message;
}

// Success message helper for order-related messages
function set_order_success_message($message) {
    $_SESSION['order_success_message'] = $message;
}

// Error message helper
function set_error_message($message) {
    $_SESSION['error_message'] = $message;
}

// Display success message
function display_success_message() {
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo $_SESSION['success_message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['success_message']);
    }
}

// Display order success message (only on cart and order pages)
function display_order_success_message() {
    if (isset($_SESSION['order_success_message'])) {
        $current_page = basename($_SERVER['PHP_SELF']);
        // Only show order messages on cart, order_detail, or index pages
        if (in_array($current_page, ['cart.php', 'order_detail.php', 'index.php'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
            echo $_SESSION['order_success_message'];
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            echo '</div>';
        }
        unset($_SESSION['order_success_message']);
    }
}

// Display error message
function display_error_message() {
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error_message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['error_message']);
    }
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not admin
function require_admin() {
    if (!is_admin()) {
        header('Location: index.php');
        exit();
    }
}

// Redirect admin users away from customer features
function redirect_admin_from_customer_features() {
    if (is_admin()) {
        header('Location: admin/index.php');
        exit();
    }
}

// Check if admin should have access to customer cart features
function allow_cart_access() {
    return is_logged_in() && !is_admin();
}
?>
