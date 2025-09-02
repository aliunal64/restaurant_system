<?php 
include 'header.php';

// Redirect admins away from contact form - only regular users should be able to send messages
if (is_logged_in() && is_admin()) {
    header('Location: admin/index.php');
    exit();
}

// Handle contact form submission
if ($_POST && isset($_POST['send_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
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
    
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            
            set_success_message('Thank you for your message! We will get back to you soon.');
            header('Location: contact.php');
            exit();
            
        } catch (Exception $e) {
            set_error_message('Error sending message. Please try again.');
        }
    } else {
        set_error_message(implode('<br>', $errors));
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold">Contact Us</h1>
            <p class="lead text-muted">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </div>
    
    <div class="row">
        <!-- Contact Form -->
        <div class="col-lg-8 mb-5">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-envelope me-2"></i>Send us a Message
                    </h4>
                </div>
                <div class="card-body">
                    <?php display_success_message(); ?>
                    <?php display_error_message(); ?>
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                       placeholder="Enter your full name" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       placeholder="Enter your email address" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject *</label>
                            <input type="text" name="subject" class="form-control" 
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" 
                                   placeholder="What is this message about?" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="6" 
                                      placeholder="Write your message here..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" name="send_message" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Get in Touch
                    </h4>
                </div>
                <div class="card-body">
                    <div class="contact-info">
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-map-marker-alt fa-lg text-primary me-3 mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Address</h6>
                                    <p class="text-muted mb-0">
                                        123 Main Street<br>
                                        New York, NY 10001<br>
                                        United States
                                    </p>
                                    <a href="https://goo.gl/maps/5Z8X9Y7W6V5Q2A3B8" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                        <i class="fas fa-map-marked-alt"></i> View on Google Maps
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-phone fa-lg text-primary me-3 mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Phone</h6>
                                    <p class="text-muted mb-0">
                                        <a href="tel:+15551234567" class="text-decoration-none">+1 (555) 123-4567</a><br>
                                        <small>Available 24/7 for orders</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-envelope fa-lg text-primary me-3 mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Email</h6>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:info@restaurant.com" class="text-decoration-none">info@restaurant.com</a><br>
                                        <small>We'll respond within 24 hours</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fab fa-whatsapp fa-lg text-primary me-3 mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">WhatsApp</h6>
                                    <p class="text-muted mb-0">
                                        <a href="https://wa.me/15551234567" target="_blank" class="text-decoration-none">+1 (555) 123-4567</a><br>
                                        <small>Quick support and instant orders</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-clock fa-lg text-primary me-3 mt-1"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Business Hours</h6>
                                    <p class="text-muted mb-0 small">
                                        <strong>Monday - Friday:</strong> 11:00 AM - 11:00 PM<br>
                                        <strong>Saturday:</strong> 10:00 AM - 12:00 AM<br>
                                        <strong>Sunday:</strong> 10:00 AM - 10:00 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <h6 class="fw-bold mb-3">Follow Us</h6>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-primary btn-sm me-2 mb-2">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm me-2 mb-2">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="btn btn-outline-primary btn-sm me-2 mb-2">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Google Maps Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold mb-4 text-center">Find Us on the Map</h3>
            <div class="card">
                <div class="card-body p-0">
                    <div class="map-container" style="height: 400px; position: relative; overflow: hidden;">
                        <iframe 
                            src="https://maps.google.com/maps?q=40.7505,-73.9934&hl=en&z=15&output=embed"
                            width="100%" 
                            height="400" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <div class="map-overlay position-absolute top-0 end-0 m-3">
                            <a href="https://goo.gl/maps/5Z8X9Y7W6V5Q2A3B8" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fas fa-external-link-alt"></i> Open in Google Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="fw-bold mb-4 text-center">Frequently Asked Questions</h3>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h6 class="fw-bold">What are your delivery hours?</h6>
                        <p class="text-muted">We offer delivery service during all our business hours. Orders can be placed online 24/7.</p>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h6 class="fw-bold">What payment methods do you accept?</h6>
                        <p class="text-muted">We accept both cash on delivery and card on delivery. You can choose to pay in cash or with your credit/debit card when your order is delivered to your doorstep.</p>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h6 class="fw-bold">What is your delivery fee?</h6>
                        <p class="text-muted">Our standard delivery fee is $<?php echo number_format(DELIVERY_FEE, 2); ?>. Free delivery on orders over $<?php echo number_format(FREE_DELIVERY_THRESHOLD, 2); ?>.</p>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="faq-item">
                        <h6 class="fw-bold">Can I modify my order after placing it?</h6>
                        <p class="text-muted">Please call us immediately at +1 (555) 123-4567 if you need to modify your order.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
