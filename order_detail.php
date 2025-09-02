<?php
require_once 'functions.php'; 
require_login();

// Redirect admin users to admin panel
if (is_admin()) {
    set_error_message('Admin users cannot access customer orders. Use the admin panel to manage all orders.');
    header('Location: admin/orders.php');
    exit();
}

include 'header.php';

// Get user orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           GROUP_CONCAT(CONCAT(p.name, ' (', oi.quantity, ')') SEPARATOR ', ') as items_summary
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Get specific order details if requested
$order_details = null;
if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    
    // Verify order belongs to user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if ($order) {
        // Get order items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name, p.image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_details = [
            'order' => $order,
            'items' => $stmt->fetchAll()
        ];
    }
}

// Status badge helper
function get_status_badge($status) {
    $badges = [
        'pending' => 'bg-warning text-dark',
        'preparing' => 'bg-info',
        'on_the_way' => 'bg-primary',
        'delivered' => 'bg-success'
    ];
    
    return $badges[$status] ?? 'bg-secondary';
}

// Status progress helper
function get_status_progress($status) {
    $progress = [
        'pending' => 25,
        'preparing' => 50,
        'on_the_way' => 75,
        'delivered' => 100
    ];
    
    return $progress[$status] ?? 0;
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 fw-bold mb-4">My Orders</h1>
            
            <?php display_order_success_message(); ?>
            <?php display_success_message(); ?>
            <?php display_error_message(); ?>
            
            <?php if ($order_details): ?>
            <!-- Order Detail View -->
            <div class="mb-4">
                <a href="order_detail.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Order #<?php echo $order_details['order']['id']; ?></h5>
                            <small class="text-muted">
                                Placed on <?php echo date('F j, Y \a\t g:i A', strtotime($order_details['order']['created_at'])); ?>
                            </small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="badge <?php echo get_status_badge($order_details['order']['status']); ?> fs-6">
                                <?php echo ucwords(str_replace('_', ' ', $order_details['order']['status'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Status Progress -->
                    <div class="mb-4">
                        <h6 class="mb-3">Delivery Status</h6>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo get_status_progress($order_details['order']['status']); ?>%"></div>
                        </div>
                        <div class="row text-center small">
                            <div class="col-3">
                                <i class="fas fa-clock <?php echo in_array($order_details['order']['status'], ['pending', 'preparing', 'on_the_way', 'delivered']) ? 'text-primary' : 'text-muted'; ?>"></i>
                                <div>Pending</div>
                            </div>
                            <div class="col-3">
                                <i class="fas fa-utensils <?php echo in_array($order_details['order']['status'], ['preparing', 'on_the_way', 'delivered']) ? 'text-primary' : 'text-muted'; ?>"></i>
                                <div>Preparing</div>
                            </div>
                            <div class="col-3">
                                <i class="fas fa-truck <?php echo in_array($order_details['order']['status'], ['on_the_way', 'delivered']) ? 'text-primary' : 'text-muted'; ?>"></i>
                                <div>On the Way</div>
                            </div>
                            <div class="col-3">
                                <i class="fas fa-check-circle <?php echo $order_details['order']['status'] === 'delivered' ? 'text-success' : 'text-muted'; ?>"></i>
                                <div>Delivered</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <h6 class="mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_details['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo image_or_placeholder($item['image'], 'menu'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <?php 
                                // Calculate subtotal (items total without delivery fee)
                                $items_subtotal = 0;
                                foreach ($order_details['items'] as $item) {
                                    $items_subtotal += $item['price'] * $item['quantity'];
                                }
                                ?>
                                <tr>
                                    <th colspan="3">Items Subtotal</th>
                                    <th>$<?php echo number_format($items_subtotal, 2); ?></th>
                                </tr>
                                <tr>
                                    <th colspan="3">Delivery Fee</th>
                                    <th>
                                        <?php if (get_delivery_fee($items_subtotal) == 0): ?>
                                            <span class="text-success">FREE</span>
                                        <?php else: ?>
                                            $<?php echo number_format(get_delivery_fee($items_subtotal), 2); ?>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                                <tr class="table-dark">
                                    <th colspan="3">Total</th>
                                    <th>$<?php echo number_format($order_details['order']['total_amount'], 2); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Delivery Information -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h6>Delivery Address</h6>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($order_details['order']['delivery_address'])); ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6>Contact Phone</h6>
                            <p class="text-muted"><?php echo htmlspecialchars($order_details['order']['phone']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6>Payment Method</h6>
                            <p class="text-muted">
                                <?php 
                                $payment_method = $order_details['order']['payment_method'] ?? 'cash_on_delivery';
                                if ($payment_method === 'cash_on_delivery'): 
                                ?>
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>Cash on Delivery
                                <?php else: ?>
                                    <i class="fas fa-credit-card me-2 text-primary"></i>Card on Delivery
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Orders List View -->
            <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h3 class="text-muted">No orders yet</h3>
                <p class="text-muted mb-4">You haven't placed any orders yet. Start exploring our delicious menu!</p>
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            </div>
            
            <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Order #<?php echo $order['id']; ?></h6>
                            <span class="badge <?php echo get_status_badge($order['status']); ?>">
                                <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo date('M j, Y \a\t g:i A', strtotime($order['created_at'])); ?>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Items:</strong>
                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($order['items_summary']); ?></p>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="h6 text-primary">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                    <small class="text-muted d-block"><?php echo $order['item_count']; ?> item(s)</small>
                                </div>
                                <a href="order_detail.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    View Details
                                </a>
                            </div>
                            
                            <!-- Mini Progress Bar -->
                            <div class="mt-3">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: <?php echo get_status_progress($order['status']); ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>