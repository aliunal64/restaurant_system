<?php
require_once '../functions.php'; 
require_admin();

include 'header.php';

// Get admin user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
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
                // Store password as plain text (not recommended for production)
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $password, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $_SESSION['user_id']]);
            }
            
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;
            
            set_success_message('Admin profile updated successfully!');
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

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-user-shield me-2"></i>Admin Profile Settings
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
                        
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                            <button type="submit" name="update_profile" class="btn btn-danger">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Admin Account Information -->
            <div class="card mt-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Administrator Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Account Type:</strong> 
                                <span class="badge bg-danger">
                                    <i class="fas fa-crown me-1"></i>Administrator
                                </span>
                            </p>
                            <p><strong>User ID:</strong> #<?php echo $user['id']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Admin Since:</strong> 
                                <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                            </p>
                            <p><strong>Last Login:</strong> 
                                <?php echo date('F j, Y g:i A'); ?>
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-shield-alt me-2"></i>
                        <strong>Security Note:</strong> As an administrator, you have full access to manage the restaurant system. 
                        Keep your credentials secure and update your password regularly.
                    </div>
                    
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="menu.php" class="btn btn-outline-success me-2">
                            <i class="fas fa-utensils"></i> Manage Menu
                        </a>
                        <a href="orders.php" class="btn btn-outline-warning me-2">
                            <i class="fas fa-shopping-bag"></i> View Orders
                        </a>
                        <a href="messages.php" class="btn btn-outline-info">
                            <i class="fas fa-envelope"></i> Messages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>