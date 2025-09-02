<?php
require_once 'functions.php'; 
require_login();

// Redirect admin users to admin panel
if (is_admin()) {
    set_error_message('Admin users cannot access cart functionality. Use the admin panel to manage the system.');
    header('Location: admin/index.php');
    exit();
}

include 'header.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clear_cart'])) {
        clear_cart();
        set_success_message('Cart cleared successfully!');
        header('Location: cart.php');
        exit();
    } elseif (isset($_POST['complete_order'])) {
        // Get user's delivery info from session/profile
        $delivery_address = $_SESSION['user_address'] ?? '';
        $phone = $_SESSION['user_phone'] ?? '';
        $payment_method = $_POST['payment_method'] ?? '';
        
        // Validate that user has profile information and payment method
        if (empty($delivery_address) || empty($phone)) {
            set_error_message('Please update your profile with delivery address and phone number before placing an order.');
        } elseif (empty($payment_method)) {
            set_error_message('Please select a payment method.');
        } else {
            // Additional checks before creating order
            $cart_items = get_cart_items();
            
            if (empty($cart_items)) {
                set_error_message('Your cart is empty. Please add items before placing an order.');
            } else {
                // Create order using profile data and payment method
                $order_id = create_order($delivery_address, $phone, $payment_method);
                if ($order_id) {
                    // Clear cart manually to ensure it's empty
                    $_SESSION['cart'] = [];
                    set_order_success_message('Order placed successfully! Order #' . $order_id);
                    header('Location: order_detail.php?order_id=' . $order_id);
                    exit();
                } else {
                    set_error_message('Failed to place order. Please check your information and try again.');
                }
            }
        }
        header('Location: cart.php');
        exit();
    }
}

$cart_items = get_cart_items();
$cart_total = get_cart_total();
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="display-4 fw-bold text-center mb-4">Shopping Cart</h1>
            
            <?php display_success_message(); ?>
            <?php display_order_success_message(); ?>
            <?php display_error_message(); ?>
            
            <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h3 class="text-muted">Your cart is empty</h3>
                <p class="text-muted mb-4">Add some delicious items from our menu!</p>
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            </div>
            <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th style="width: 150px;">Quantity</th>
                                    <th>Subtotal</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                <tr class="cart-item" data-product-id="<?php echo $item['id']; ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo image_or_placeholder($item['image'], 'menu'); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 class="rounded me-3" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($item['category']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <input type="number" 
                                               value="<?php echo $item['quantity']; ?>"
                                               min="1" 
                                               max="10"
                                               class="form-control quantity-input"
                                               data-product-id="<?php echo $item['id']; ?>"
                                               data-price="<?php echo $item['price']; ?>">
                                    </td>
                                    <td class="subtotal">$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger remove-item" 
                                                data-product-id="<?php echo $item['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Subtotal:</th>
                                    <th colspan="2" class="h6 mb-0">
                                        $<span id="cartTotal"><?php echo number_format($cart_total, 2); ?></span>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Delivery Fee:</th>
                                    <th colspan="2" class="h6 mb-0">
                                        <?php if (get_delivery_fee($cart_total) == 0): ?>
                                            <span class="text-success">FREE</span>
                                        <?php else: ?>
                                            $<?php echo number_format(get_delivery_fee($cart_total), 2); ?>
                                        <?php endif; ?>
                                    </th>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th colspan="2" class="h5 mb-0">
                                        $<span id="finalTotal"><?php echo number_format(get_cart_total_with_delivery(), 2); ?></span>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Order Information -->
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <h5 class="mb-3">Delivery Information</h5>
                            
                            <?php if (empty($_SESSION['user_address']) || empty($_SESSION['user_phone'])): ?>
                            <!-- Missing profile information -->
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Profile Information Required</h6>
                                <p class="mb-2">Please update your profile with delivery address and phone number to place an order.</p>
                                <a href="profile.php" class="btn btn-warning btn-sm">
                                    <i class="fas fa-user-edit me-2"></i>Update Profile
                                </a>
                            </div>
                            <?php else: ?>
                            <!-- Show user's delivery information -->
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-map-marker-alt me-2"></i>Delivery Address</h6>
                                            <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($_SESSION['user_address'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-phone me-2"></i>Contact Phone</h6>
                                            <p class="text-muted mb-0"><?php echo htmlspecialchars($_SESSION['user_phone']); ?></p>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Need to change delivery details? <a href="profile.php">Update your profile</a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST" id="checkoutForm">
                                <!-- Payment Method Selection -->
                                <div class="mt-3 p-3 bg-light rounded border">
                                    <h6 class="text-primary mb-3"><i class="fas fa-credit-card me-2"></i>Payment Method</h6>
                                    <select name="payment_method" class="form-select">
                                        <option value="">Choose payment method...</option>
                                        <option value="cash_on_delivery">
                                            Cash on Delivery
                                        </option>
                                        <option value="card_on_delivery">
                                            Card on Delivery
                                        </option>
                                    </select>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-info-circle me-1"></i>
                                        You can pay when your order is delivered to your doorstep.
                                    </small>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4">
                                    <div>
                                        <button type="submit" name="clear_cart" class="btn btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to clear the cart?')">
                                            <i class="fas fa-trash-alt me-2"></i>Clear Cart
                                        </button>
                                        <a href="menu.php" class="btn btn-outline-secondary ms-2">
                                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                                        </a>
                                    </div>
                                    <div>
                                        <button type="submit" name="complete_order" class="btn btn-success btn-lg">
                                            <i class="fas fa-check-circle me-2"></i>Complete Order
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Order Summary</h6>
                                    <div class="d-flex justify-content-between">
                                        <span id="itemCountText">Items (<?php echo get_cart_count(); ?>):</span>
                                        <span>$<span id="summarySubtotal"><?php echo number_format($cart_total, 2); ?></span></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Delivery Fee:</span>
                                        <span id="deliveryFeeText">
                                            <?php if (get_delivery_fee($cart_total) == 0): ?>
                                                <span class="text-success">FREE</span>
                                            <?php else: ?>
                                                $<?php echo number_format(get_delivery_fee($cart_total), 2); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-bold">
                                        <span>Total:</span>
                                        <span>$<span id="summaryTotal"><?php echo number_format(get_cart_total_with_delivery(), 2); ?></span></span>
                                    </div>
                                    
                                    <!-- Payment method moved to the left side form -->
                                    
                                    <div class="mt-3">
                                        <?php if (get_delivery_fee($cart_total) == 0): ?>
                                            <small class="text-success" id="deliveryMessage">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Free delivery! You saved $<?php echo number_format(DELIVERY_FEE, 2); ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted" id="deliveryMessage">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Add $<?php echo number_format(FREE_DELIVERY_THRESHOLD - $cart_total, 2); ?> more for free delivery!
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Toast for notifications -->
<div class="toast-container position-fixed end-0 p-3" style="top: 80px; z-index: 1050;">
    <div id="cartUpdateToast" class="toast" role="alert">
        <div class="toast-header">
            <strong class="me-auto">Cart Update</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove item from cart
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            if (confirm('Are you sure you want to remove this item?')) {
                fetch('ajax_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=remove&product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove row
                        this.closest('tr').remove();
                        
                        // Update totals
                        updateTotals();
                        
                        // Update cart count
                        const cartBadge = document.querySelector('.navbar .badge');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                        }
                        
                        // Show toast
                        showToast('Item removed from cart');
                        
                        // If cart is empty, reload page
                        if (data.cart_count === 0) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });
    
    // Update quantity dynamically
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const quantity = this.value;
            const price = parseFloat(this.dataset.price);
            
            // Update subtotal
            const subtotalCell = this.closest('tr').querySelector('.subtotal');
            const newSubtotal = (price * quantity).toFixed(2);
            subtotalCell.textContent = '$' + newSubtotal;
            
            // Update all totals
            updateTotals();
            
            // Send AJAX request to update session
            fetch('ajax_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update&product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    const cartBadge = document.querySelector('.navbar .badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    showToast('Cart updated');
                }
            });
        });
    });
    
    // Function to update all totals
    function updateTotals() {
        let subtotal = 0;
        let itemCount = 0;
        
        document.querySelectorAll('.cart-item').forEach(row => {
            const qty = parseInt(row.querySelector('.quantity-input').value);
            const price = parseFloat(row.querySelector('.quantity-input').dataset.price);
            subtotal += qty * price;
            itemCount += qty;
        });
        
        // Calculate delivery fee based on subtotal
        const freeDeliveryThreshold = <?php echo FREE_DELIVERY_THRESHOLD; ?>;
        const deliveryFee = subtotal >= freeDeliveryThreshold ? 0 : <?php echo DELIVERY_FEE; ?>;
        const total = subtotal + deliveryFee;
        
        // Update cart table totals
        document.getElementById('cartTotal').textContent = subtotal.toFixed(2);
        document.getElementById('finalTotal').textContent = total.toFixed(2);
        
        // Update delivery fee display in cart table
        const cartDeliveryRow = document.querySelector('tfoot tr:nth-child(2) th:last-child');
        if (cartDeliveryRow) {
            if (deliveryFee === 0) {
                cartDeliveryRow.innerHTML = '<span class="text-success">FREE</span>';
            } else {
                cartDeliveryRow.textContent = '$' + deliveryFee.toFixed(2);
            }
        }
        
        // Update summary totals
        document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
        document.getElementById('summaryTotal').textContent = total.toFixed(2);
        
        // Update delivery fee in summary sidebar
        const deliveryFeeElement = document.getElementById('deliveryFeeText');
        if (deliveryFeeElement) {
            if (deliveryFee === 0) {
                deliveryFeeElement.innerHTML = '<span class="text-success">FREE</span>';
            } else {
                deliveryFeeElement.textContent = '$' + deliveryFee.toFixed(2);
            }
        }
        
        // Update delivery message
        const deliveryMessage = document.getElementById('deliveryMessage');
        if (deliveryMessage) {
            if (deliveryFee === 0) {
                deliveryMessage.innerHTML = '<i class="fas fa-check-circle me-1"></i>Free delivery! You saved $<?php echo number_format(DELIVERY_FEE, 2); ?>';
                deliveryMessage.className = 'text-success';
            } else {
                const needed = freeDeliveryThreshold - subtotal;
                deliveryMessage.innerHTML = '<i class="fas fa-info-circle me-1"></i>Add $' + needed.toFixed(2) + ' more for free delivery!';
                deliveryMessage.className = 'text-muted';
            }
        }
        
        // Update item count in summary
        const itemCountElement = document.getElementById('itemCountText');
        if (itemCountElement) {
            itemCountElement.textContent = `Items (${itemCount}):`;
        }
    }
    
    // Form submission handling for Complete Order
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // Only handle complete_order submissions
            if (e.submitter && e.submitter.name === 'complete_order') {
                // Check if payment method is selected
                const paymentMethod = document.querySelector('select[name="payment_method"]');
                if (paymentMethod && !paymentMethod.value) {
                    e.preventDefault();
                    alert('Please select a payment method.');
                    paymentMethod.focus();
                    return false;
                }
                
                // Show loading state but don't prevent submission
                setTimeout(() => {
                    e.submitter.disabled = true;
                    e.submitter.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                }, 100);
            }
            // For clear_cart or other submissions, let the form submit normally without validation
        });
    }
    
    function showToast(message) {
        const toastEl = document.getElementById('cartUpdateToast');
        toastEl.querySelector('.toast-body').textContent = message;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
});
</script>

<?php include 'footer.php'; ?>