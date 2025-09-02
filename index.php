<?php include 'header.php'; ?>

<!-- Display success message for account deletion -->
<?php if (isset($_GET['message']) && $_GET['message'] === 'account_deleted'): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    Your account has been successfully deleted. Thank you for using our service.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Hero Slider -->
<section class="hero-slider">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $stmt = $pdo->query("SELECT * FROM site_images WHERE type = 'slider' AND status = 'active' ORDER BY sort_order");
            $slides = $stmt->fetchAll();
            $first = true;
            
            foreach ($slides as $slide):
            ?>
            <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                <div class="hero-slide" style="background-image: url('<?php echo image_or_placeholder($slide['image'], 'site'); ?>');">
                    <div class="hero-overlay">
                        <div class="container">
                            <div class="row justify-content-center text-center">
                                <div class="col-lg-8">
                                    <h1 class="display-4 fw-bold text-white mb-4">
                                        <?php echo htmlspecialchars($slide['title']); ?>
                                    </h1>
                                    <p class="lead text-white mb-4">
                                        <?php echo htmlspecialchars($slide['description']); ?>
                                    </p>
                                    <a href="menu.php" class="btn btn-primary btn-lg">Order Now</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
            $first = false;
            endforeach; 
            ?>
        </div>
        
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

<!-- Restaurant Atmosphere -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Restaurant</h2>
                <p class="lead text-muted">Experience the perfect dining atmosphere</p>
            </div>
        </div>
        
        <div class="row g-4">
            <?php
            $stmt = $pdo->query("SELECT * FROM site_images WHERE type = 'atmosphere' AND status = 'active' ORDER BY sort_order LIMIT 3");
            $atmosphere_images = $stmt->fetchAll();
            
            foreach ($atmosphere_images as $image):
            ?>
            <div class="col-md-4">
                <div class="atmosphere-card">
                    <div class="atmosphere-image">
                        <img src="<?php echo image_or_placeholder($image['image'], 'site'); ?>" 
                             alt="<?php echo htmlspecialchars($image['title']); ?>" 
                             class="img-fluid rounded">
                    </div>
                    <div class="atmosphere-content p-3">
                        <h5 class="fw-bold"><?php echo htmlspecialchars($image['title']); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($image['description']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Menu Showcase -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="display-5 fw-bold mb-3">Our Menu</h2>
                <p class="lead text-muted">Discover our delicious categories</p>
            </div>
        </div>
        
        <div class="menu-categories">
            <div class="row g-4">
                <?php
                $stmt = $pdo->query("SELECT category, COUNT(*) as count, MIN(image) as sample_image FROM products WHERE status = 'active' GROUP BY category");
                $categories = $stmt->fetchAll();
                
                foreach ($categories as $category):
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="category-card h-100">
                        <div class="category-image">
                            <img src="<?php echo image_or_placeholder($category['sample_image'], 'menu'); ?>" 
                                 alt="<?php echo htmlspecialchars($category['category']); ?>" 
                                 class="img-fluid">
                        </div>
                        <div class="category-content p-4 text-center">
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($category['category']); ?></h5>
                            <p class="text-muted mb-3"><?php echo $category['count']; ?> items available</p>
                            <a href="menu.php?category=<?php echo urlencode($category['category']); ?>" 
                               class="btn btn-outline-primary">View Menu</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Restaurant Philosophy -->
<section class="py-5">
    <div class="container">
        <?php
        $stmt = $pdo->query("SELECT * FROM site_images WHERE type = 'philosophy' AND status = 'active' LIMIT 1");
        $philosophy = $stmt->fetch();
        ?>
        
        <div class="philosophy-section" style="background-image: url('<?php echo $philosophy ? image_or_placeholder($philosophy['image'], 'site') : ''; ?>');">
            <div class="philosophy-overlay">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center">
                        <h2 class="display-5 fw-bold text-white mb-4">Our Philosophy</h2>
                        <p class="lead text-white mb-0">
                            We believe in using only the freshest ingredients, prepared with passion and served with love. 
                            Every dish tells a story of tradition, quality, and culinary excellence that has been passed down through generations.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5">
    <?php
    $stmt = $pdo->query("SELECT * FROM site_images WHERE type = 'cta' AND status = 'active' LIMIT 1");
    $cta = $stmt->fetch();
    ?>
    
    <div class="cta-bg" style="background-image: url('<?php echo $cta ? image_or_placeholder($cta['image'], 'site') : ''; ?>');">
        <div class="cta-overlay">
            <div class="container">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-6">
                        <h2 class="display-4 fw-bold text-white mb-4">Ready to Order?</h2>
                        <p class="lead text-white mb-4">
                            Experience our delicious food delivered fresh to your doorstep
                        </p>
                        <a href="menu.php" class="btn btn-primary btn-lg me-3">Order Now</a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced smooth scrolling for index page
    
    // Add smooth scrolling to all internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add scroll spy for navbar highlighting (if applicable)
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.getBoundingClientRect().top;
            const sectionHeight = section.offsetHeight;
            if (sectionTop <= 100 && sectionTop + sectionHeight > 100) {
                current = section.getAttribute('id');
            }
        });
    });
    
    // Add smooth scroll behavior to Order Now buttons
    document.querySelectorAll('a[href="menu.php"]').forEach(button => {
        button.addEventListener('click', function(e) {
            // Add a gentle fade effect before navigation
            this.style.transition = 'all 0.3s ease';
            this.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                window.location.href = 'menu.php';
            }, 150);
            
            e.preventDefault();
        });
    });
    
    // Add smooth scrolling to Contact Us buttons
    document.querySelectorAll('a[href="contact.php"]').forEach(button => {
        button.addEventListener('click', function(e) {
            // Add a gentle fade effect before navigation
            this.style.transition = 'all 0.3s ease';
            this.style.transform = 'scale(0.95)';
            
            setTimeout(() => {
                window.location.href = 'contact.php';
            }, 150);
            
            e.preventDefault();
        });
    });
    
    // Add smooth reveal animation for sections on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Apply smooth reveal to all sections
    sections.forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(section);
    });
    
    // Add smooth animations for atmosphere cards
    const atmosphereCards = document.querySelectorAll('.atmosphere-card');
    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) scale(1)';
                }, index * 200); // Staggered animation with 200ms delay
            }
        });
    }, {
        threshold: 0.2,
        rootMargin: '0px 0px -30px 0px'
    });
    
    // Initialize atmosphere cards with hidden state
    atmosphereCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px) scale(0.95)';
        card.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        cardObserver.observe(card);
    });
    
    // Add hover enhancement for atmosphere cards
    atmosphereCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.boxShadow = '0 15px 35px rgba(0, 0, 0, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
        });
    });
    
    // Add smooth animation for category cards in menu section
    const categoryCards = document.querySelectorAll('.category-card');
    const categoryObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) rotateY(0deg)';
                }, index * 150); // Staggered animation
            }
        });
    }, {
        threshold: 0.2
    });
    
    // Initialize category cards
    categoryCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(40px) rotateY(15deg)';
        card.style.transition = 'all 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        categoryObserver.observe(card);
        
        // Add hover effects to category cards (no background change)
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px) rotateY(0deg)'; // Only lift up
            this.style.transition = 'all 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) rotateY(0deg)'; // Back to normal position
            this.style.transition = 'all 0.3s ease';
        });
    });
    
    // Smooth carousel transitions
    const carousel = document.getElementById('heroCarousel');
    if (carousel) {
        carousel.addEventListener('slide.bs.carousel', function (e) {
            // Add custom transition effects if needed
        });
    }
});
</script>

<?php include 'footer.php'; ?>