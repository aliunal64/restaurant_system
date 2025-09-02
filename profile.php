<?php
require_once 'functions.php'; 
require_login();

// Redirect admin users to admin profile
if (is_admin()) {
    header('Location: admin/profile.php');
    exit();
}

include 'header.php';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle account deletion
if ($_POST && isset($_POST['delete_account'])) {
    $user_id = $_SESSION['user_id'];
    
    try {
        // Delete user's orders first (due to foreign key constraints)
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = ?)");
        $stmt->execute([$user_id]);
        
        $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Delete user account
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Destroy session
        session_destroy();
        
        // Redirect to home page with success message
        header('Location: index.php?message=account_deleted');
        exit();
        
    } catch (Exception $e) {
        set_error_message('Error deleting account. Please try again or contact support.');
    }
}

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);
    
    $errors = [];
    
    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (!empty($phone) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number.';
    }
    
    // Check if email exists for other users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        $errors[] = 'Email address is already in use.';
    }
    
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $address, $password, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $address, $_SESSION['user_id']]);
            }
            
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_address'] = $address;
            
            set_success_message('Profile updated successfully!');
            header('Location: profile.php');
            exit();
            
        } catch (Exception $e) {
            set_error_message('Error updating profile. Please try again.');
        }
    } else {
        set_error_message(implode('<br>', $errors));
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-user me-2"></i>My Profile
                    </h3>
                </div>
                <div class="card-body">
                    <?php display_success_message(); ?>
                    <?php display_error_message(); ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                       placeholder="e.g., +1 (555) 123-4567">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Leave blank to keep current password">
                                <div class="form-text">Only fill this if you want to change your password</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Delivery Address</label>
                            <textarea name="address" class="form-control" rows="3" 
                                      placeholder="Enter your full address for deliveries"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Home
                            </a>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Account Information -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Account Type:</strong> 
                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Member Since:</strong> 
                                <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="order_detail.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-history"></i> View Order History
                        </a>
                        <?php if (is_admin()): ?>
                        <a href="admin/index.php" class="btn btn-outline-danger me-2">
                            <i class="fas fa-cog"></i> Admin Panel
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="fas fa-trash-alt"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center px-4 pb-4">
                <div class="mb-3">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h4 class="fw-bold mb-3">Delete Account?</h4>
                <p class="text-muted mb-4">
                    This action cannot be undone. All your data including order history will be permanently deleted.
                </p>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="delete_account" value="1">
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>