<?php 
include 'header.php';

// Handle status updates
if ($_POST && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    
    set_success_message('Order status updated successfully!');
    header('Location: orders.php');
    exit();
}

// Get orders with customer info
$stmt = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email as customer_email,
           COUNT(oi.id) as item_count
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

// Get specific order details if requested
$order_details = null;
if (isset($_GET['view'])) {
    $order_id = (int)$_GET['view'];
    
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    
    if ($order) {
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

function get_status_badge($status) {
    $badges = [
        'pending' => 'bg-warning text-dark',
        'preparing' => 'bg-info',
        'on_the_way' => 'bg-primary',
        'delivered' => 'bg-success'
    ];
    return $badges[$status] ?? 'bg-secondary';
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 fw-bold mb-4">Orders Management</h1>
            
            <?php display_success_message(); ?>
            <?php display_error_message(); ?>
            
            <?php if ($order_details): ?>
            <!-- Order Detail View -->
            <div class="mb-4">
                <a href="orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Orders
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Order #<?php echo $order_details['order']['id']; ?></h5>
                            <small class="text-muted">
                                Customer: <?php echo htmlspecialchars($order_details['order']['customer_name']); ?>
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
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order_details['order']['customer_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order_details['order']['customer_email']); ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order_details['order']['phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('M j, Y \a\t g:i A', strtotime($order_details['order']['created_at'])); ?></p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge <?php echo get_status_badge($order_details['order']['status']); ?>">
                                    <?php echo ucwords(str_replace('_', ' ', $order_details['order']['status'])); ?>
                                </span>
                            </p>
                            <p class="mb-1"><strong>Total:</strong> $<?php echo number_format($order_details['order']['total_amount'], 2); ?></p>
                            <p class="mb-1"><strong>Payment:</strong> 
                                <?php 
                                $payment_method = $order_details['order']['payment_method'] ?? 'cash_on_delivery';
                                echo $payment_method === 'cash_on_delivery' ? 
                                    '<span class="text-success"><i class="fas fa-money-bill-wave me-1"></i>Cash on Delivery</span>' : 
                                    '<span class="text-primary"><i class="fas fa-credit-card me-1"></i>Card on Delivery</span>';
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <h6>Delivery Address</h6>
                    <p class="text-muted mb-4"><?php echo nl2br(htmlspecialchars($order_details['order']['delivery_address'])); ?></p>
                    
                    <h6>Order Items</h6>
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
                </div>
            </div>
            
            <?php else: ?>
            <!-- Orders List View -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> item(s)</td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge <?php echo get_status_badge($order['status']); ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <div class="btn-group">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                Status
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <form method="POST" class="dropdown-item-form">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="pending">
                                                        <button type="submit" name="update_status" class="dropdown-item">Pending</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="dropdown-item-form">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="preparing">
                                                        <button type="submit" name="update_status" class="dropdown-item">Preparing</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="dropdown-item-form">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="on_the_way">
                                                        <button type="submit" name="update_status" class="dropdown-item">On the Way</button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="dropdown-item-form">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <input type="hidden" name="status" value="delivered">
                                                        <button type="submit" name="update_status" class="dropdown-item">Delivered</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>