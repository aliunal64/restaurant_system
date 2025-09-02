<?php include 'header.php'; ?>

<?php
// Get about page background image
$stmt = $pdo->query("SELECT * FROM site_images WHERE type = 'about' AND status = 'active' LIMIT 1");
$about_bg = $stmt->fetch();
?>

<!-- Hero Section -->
<section class="about-hero" style="background-image: url('<?php echo $about_bg ? image_or_placeholder($about_bg['image'], 'site') : ''; ?>');">
    <div class="about-hero-overlay">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold text-white mb-4">About Our Restaurant</h1>
                    <p class="lead text-white">
                        Discover the story behind our passion for exceptional food and outstanding service
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="about-content">
                    <h2 class="display-5 fw-bold mb-4 text-center">Our Story</h2>
                    
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <p>
                                Founded in 2010, our restaurant has been serving the community with authentic, 
                                delicious meals made from the freshest ingredients. What started as a small family 
                                business has grown into a beloved local institution.
                            </p>
                            <p>
                                Our commitment to quality has never wavered. Every dish is prepared with care, 
                                using traditional recipes passed down through generations, combined with modern 
                                culinary techniques to create an unforgettable dining experience.
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                We believe that great food brings people together. Whether you're dining in our 
                                cozy restaurant or enjoying our convenient delivery service, we strive to make 
                                every meal special.
                            </p>
                            <p>
                                Our team of experienced chefs and friendly staff work tirelessly to ensure that 
                                every customer leaves satisfied. We're not just serving food; we're creating 
                                memories and building relationships within our community.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Values Section -->
                    <div class="row mb-5">
                        <div class="col-12">
                            <h3 class="fw-bold mb-4 text-center">Our Values</h3>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="value-item">
                                <i class="fas fa-leaf fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Fresh Ingredients</h5>
                                <p class="text-muted">
                                    We source only the freshest, highest-quality ingredients from local suppliers 
                                    to ensure every dish meets our standards.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="value-item">
                                <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Made with Love</h5>
                                <p class="text-muted">
                                    Every dish is prepared with passion and attention to detail, because we believe 
                                    you can taste the difference when food is made with love.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-4">
                            <div class="value-item">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h5 class="fw-bold">Community First</h5>
                                <p class="text-muted">
                                    We're proud to be part of this community and committed to giving back through 
                                    local partnerships and charitable initiatives.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="row text-center mb-5">
                        <div class="col-12">
                            <h3 class="fw-bold mb-4">By the Numbers</h3>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-item">
                                <h2 class="display-4 fw-bold text-primary">15</h2>
                                <p class="text-muted">Years of Excellence</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-item">
                                <h2 class="display-4 fw-bold text-primary">50+</h2>
                                <p class="text-muted">Menu Items</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-item">
                                <h2 class="display-4 fw-bold text-primary">1000+</h2>
                                <p class="text-muted">Happy Customers</p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="stat-item">
                                <h2 class="display-4 fw-bold text-primary">24/7</h2>
                                <p class="text-muted">Online Ordering</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Call to Action -->
                    <div class="text-center">
                        <h3 class="fw-bold mb-3">Ready to Experience Our Food?</h3>
                        <p class="lead text-muted mb-4">
                            Join thousands of satisfied customers who have made us their go-to restaurant
                        </p>
                        <a href="menu.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-utensils"></i> View Our Menu
                        </a>
                        <a href="contact.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-envelope"></i> Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>