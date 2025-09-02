<?php 
include 'header.php';

// Get selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : '';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-4 fw-bold text-center mb-4">Our Menu</h1>
            
            <!-- Search Bar -->
            <div class="search-section mb-4">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">
                        <div class="search-box position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       id="searchInput" 
                                       class="form-control border-start-0" 
                                       placeholder="Type to search through our menu..."
                                       autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <!-- Dropdown Results -->
                            <div id="searchDropdown" class="search-dropdown position-absolute w-100" style="display: none; z-index: 1000;">
                                <div class="dropdown-content bg-white border rounded shadow-lg mt-1">
                                    <div id="searchResultsList"></div>
                                    <div id="noSearchResults" class="dropdown-item text-muted text-center py-3" style="display: none;">
                                        <i class="fas fa-search me-2"></i>No products found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Category Filter -->
            <div class="category-filter text-center mb-4">
                <a href="menu.php" class="btn <?php echo !$selected_category ? 'btn-primary' : 'btn-outline-primary'; ?> me-2 mb-2">
                    All Categories
                </a>
                
                <?php
                $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE status = 'active' ORDER BY category");
                while ($cat = $stmt->fetch()):
                ?>
                <a href="menu.php?category=<?php echo urlencode($cat['category']); ?>" 
                   class="btn <?php echo $selected_category === $cat['category'] ? 'btn-primary' : 'btn-outline-primary'; ?> me-2 mb-2">
                    <?php echo htmlspecialchars($cat['category']); ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <div id="productsContainer" class="row">
        <?php
        // Build query based on category filter
        $query = "SELECT * FROM products WHERE status = 'active'";
        $params = [];
        
        if ($selected_category) {
            $query .= " AND category = ?";
            $params[] = $selected_category;
        }
        
        $query .= " ORDER BY category, name";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0):
            while ($product = $stmt->fetch()):
        ?>
        <div class="col-lg-4 col-md-6 mb-4 product-item" 
             data-product-name="<?php echo strtolower(htmlspecialchars($product['name'])); ?>"
             data-product-description="<?php echo strtolower(htmlspecialchars($product['description'])); ?>"
             data-product-category="<?php echo strtolower(htmlspecialchars($product['category'])); ?>">
            <div class="product-card h-100">
                <div class="product-image">
                    <img src="<?php echo image_or_placeholder($product['image'], 'menu'); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="img-fluid">
                    <div class="product-category">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($product['category']); ?></span>
                    </div>
                </div>
                
                <div class="product-content p-4">
                    <h5 class="product-name fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="product-description text-muted mb-3">
                        <?php echo htmlspecialchars($product['description']); ?>
                    </p>
                    
                    <div class="product-footer d-flex justify-content-between align-items-center">
                        <div class="product-price">
                            <span class="h5 fw-bold text-primary mb-0">$<?php echo number_format($product['price'], 2); ?></span>
                        </div>
                        
                        <?php if (is_logged_in() && !is_admin()): ?>
                        <div class="add-to-cart-form d-flex align-items-center">
                            <input type="number" 
                                   id="quantity-<?php echo $product['id']; ?>" 
                                   value="1" 
                                   min="1" 
                                   max="10" 
                                   class="form-control form-control-sm me-2" 
                                   style="width: 60px;">
                            <button type="button" 
                                    class="btn btn-primary btn-sm add-to-cart-btn" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                        </div>
                        <?php elseif (is_admin()): ?>
                        <div class="admin-info text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Admin View - Use Admin Panel to manage menu
                            </small>
                        </div>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary btn-sm">
                            Login to Order
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endwhile;
        else:
        ?>
        <div class="col-12" id="noProductsMessage">
            <div class="text-center py-5">
                <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                <h3 class="text-muted">No products found</h3>
                <p class="text-muted">Try selecting a different category or check back later.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed end-0 p-3" style="top: 80px; z-index: 1050;">
    <div id="cartToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-check-circle me-2"></i>
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            Product added to cart successfully!
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality variables
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchResultsList = document.getElementById('searchResultsList');
    const noSearchResults = document.getElementById('noSearchResults');
    const searchResults = document.getElementById('searchResults');
    const productsContainer = document.getElementById('productsContainer');
    const noProductsMessage = document.getElementById('noProductsMessage');
    
    let searchTimeout;
    let allProducts = [];
    let selectedProductId = null;
    let currentHighlight = -1;
    
    // Initialize - collect all products data
    function initializeSearch() {
        const productItems = document.querySelectorAll('.product-item');
        allProducts = Array.from(productItems).map(item => {
            return {
                id: item.querySelector('.add-to-cart-btn')?.dataset.productId || 'unknown',
                name: item.dataset.productName,
                displayName: item.querySelector('.product-name').textContent,
                description: item.dataset.productDescription,
                category: item.dataset.productCategory,
                price: item.querySelector('.product-price .h5').textContent.trim(),
                element: item
            };
        });
    }
    
    // Real-time search with dropdown
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(this.value.trim());
        }, 200);
    });
    
    // Handle search input focus
    searchInput.addEventListener('focus', function() {
        if (this.value.trim()) {
            performSearch(this.value.trim());
        }
    });
    
    // Handle clicks outside dropdown to close it
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            hideDropdown();
        }
    });
    
    // Clear search functionality
    clearSearchBtn.addEventListener('click', function() {
        clearSearch();
    });
    
    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const dropdownItems = searchResultsList.querySelectorAll('.dropdown-item:not([style*="display: none"])');
        let currentIndex = -1;
        
        // Find currently highlighted item
        dropdownItems.forEach((item, index) => {
            if (item.classList.contains('active')) {
                currentIndex = index;
            }
        });
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentIndex = Math.min(currentIndex + 1, dropdownItems.length - 1);
            highlightItem(dropdownItems, currentIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentIndex = Math.max(currentIndex - 1, -1);
            highlightItem(dropdownItems, currentIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentIndex >= 0 && dropdownItems[currentIndex]) {
                dropdownItems[currentIndex].click();
            }
        } else if (e.key === 'Escape') {
            hideDropdown();
            this.blur();
        }
    });
    
    function highlightItem(items, index) {
        items.forEach(item => item.classList.remove('active'));
        if (index >= 0 && items[index]) {
            items[index].classList.add('active');
        }
    }
    
    function performSearch(searchTerm) {
        if (searchTerm === '') {
            hideDropdown();
            showAllProducts();
            updateSearchInfo('Type to search through our menu...');
            return;
        }
        
        const searchLower = searchTerm.toLowerCase();
        const matchingProducts = allProducts.filter(product => {
            // Split product name into words and check if any word starts with search term
            const words = product.name.split(' ');
            return words.some(word => word.toLowerCase().startsWith(searchLower));
        });
        
        if (matchingProducts.length > 0) {
            showDropdown(matchingProducts, searchTerm);
            updateSearchInfo(`Found ${matchingProducts.length} result${matchingProducts.length !== 1 ? 's' : ''}`);
        } else {
            showNoResults();
            updateSearchInfo('No products found');
        }
    }
    
    function showDropdown(products, searchTerm) {
        searchResultsList.innerHTML = '';
        noSearchResults.style.display = 'none';
        
        products.forEach(product => {
            const item = document.createElement('div');
            item.className = 'dropdown-item search-result-item';
            item.style.cursor = 'pointer';
            
            // Highlight matching text
            const highlightedName = highlightText(product.displayName, searchTerm);
            
            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-center py-2">
                    <div>
                        <div class="fw-bold text-dark">${highlightedName}</div>
                        <small class="text-muted">${product.category.charAt(0).toUpperCase() + product.category.slice(1)} • ${product.price}</small>
                    </div>
                    <i class="fas fa-arrow-right text-muted"></i>
                </div>
            `;
            
            item.addEventListener('click', function() {
                selectProduct(product);
            });
            
            item.addEventListener('mouseenter', function() {
                document.querySelectorAll('.dropdown-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
            
            searchResultsList.appendChild(item);
        });
        
        searchDropdown.style.display = 'block';
    }
    
    function showNoResults() {
        searchResultsList.innerHTML = '';
        noSearchResults.style.display = 'block';
        searchDropdown.style.display = 'block';
    }
    
    function hideDropdown() {
        searchDropdown.style.display = 'none';
    }
    
    function selectProduct(product) {
        // Hide all products
        allProducts.forEach(p => {
            p.element.style.display = 'none';
        });
        
        // Show only selected product
        product.element.style.display = 'block';
        
        // Update search input
        searchInput.value = product.displayName;
        selectedProductId = product.id;
        
        // Hide dropdown
        hideDropdown();
        
        // Update search info
        updateSearchInfo(`Showing: ${product.displayName}`);
        
        // Hide no products message
        if (noProductsMessage) {
            noProductsMessage.style.display = 'none';
        }
    }
    
    function showAllProducts() {
        allProducts.forEach(product => {
            product.element.style.display = 'block';
        });
        selectedProductId = null;
        
        // Show/hide original no products message if needed
        if (noProductsMessage && allProducts.length === 0) {
            noProductsMessage.style.display = 'block';
        }
    }
    
    function clearSearch() {
        searchInput.value = '';
        hideDropdown();
        showAllProducts();
        updateSearchInfo('Type to search through our menu...');
        searchInput.focus();
    }
    
    function updateSearchInfo(message) {
        searchResults.innerHTML = `<small class="text-muted">${message}</small>`;
    }
    
    function highlightText(text, searchTerm) {
        if (!searchTerm) return text;
        
        const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
        return text.replace(regex, '<mark class="bg-warning">$1</mark>');
    }
    
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    // Initialize search
    initializeSearch();
    
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const quantityInput = document.getElementById('quantity-' + productId);
            const quantity = quantityInput.value;
            
            // Disable button and show loading
            this.disabled = true;
            const originalHTML = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            // Send AJAX request
            fetch('ajax_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=add&product_id=' + productId + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable button
                this.disabled = false;
                this.innerHTML = originalHTML;
                
                if (data.success) {
                    // Update cart count
                    const cartBadge = document.querySelector('.navbar .badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    
                    // Show success toast
                    const toastBody = document.querySelector('#cartToast .toast-body');
                    toastBody.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-check-circle me-2"></i>${productName} added to cart!</span>
                            <a href="cart.php" class="btn btn-sm btn-outline-primary ms-2">View Cart</a>
                        </div>
                    `;
                    
                    const toast = new bootstrap.Toast(document.getElementById('cartToast'));
                    toast.show();
                    
                    // Reset quantity
                    quantityInput.value = 1;
                } else {
                    alert(data.message || 'Failed to add product to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.innerHTML = originalHTML;
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>