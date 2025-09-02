<?php 
include 'header.php';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_image'])) {
        $type = $_POST['type'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sort_order = (int)$_POST['sort_order'];
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $image = time() . '_' . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/site/' . $image);
                
                $stmt = $pdo->prepare("INSERT INTO site_images (type, image, title, description, sort_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$type, $image, $title, $description, $sort_order]);
                
                set_success_message('Image added successfully!');
            } else {
                set_error_message('Please upload a valid image file.');
            }
        } else {
            set_error_message('Please select an image file.');
        }
    }
    
    if (isset($_POST['update_image'])) {
        $id = (int)$_POST['image_id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $sort_order = (int)$_POST['sort_order'];
        $status = $_POST['status'];
        
        $image_update = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $image = time() . '_' . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/site/' . $image);
                $image_update = ", image = '$image'";
            }
        }
        
        $pdo->exec("UPDATE site_images SET title = '$title', description = '$description', sort_order = $sort_order, status = '$status' $image_update WHERE id = $id");
        
        set_success_message('Image updated successfully!');
    }
    
    if (isset($_POST['delete_image'])) {
        $id = (int)$_POST['image_id'];
        $pdo->exec("DELETE FROM site_images WHERE id = $id");
        set_success_message('Image deleted successfully!');
    }
    
    header('Location: site_images.php');
    exit();
}

// Get images by type
$image_types = ['slider', 'atmosphere', 'philosophy', 'cta', 'about'];
$images_by_type = [];

foreach ($image_types as $type) {
    $stmt = $pdo->prepare("SELECT * FROM site_images WHERE type = ? ORDER BY sort_order, created_at");
    $stmt->execute([$type]);
    $images_by_type[$type] = $stmt->fetchAll();
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-4 fw-bold">Site Images Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addImageModal">
                    <i class="fas fa-plus"></i> Add Image
                </button>
            </div>
            
            <?php display_success_message(); ?>
            <?php display_error_message(); ?>
            
            <!-- Image Type Tabs -->
            <ul class="nav nav-tabs mb-4" id="imageTypeTabs" role="tablist">
                <?php foreach ($image_types as $index => $type): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                            id="<?php echo $type; ?>-tab" data-bs-toggle="tab" 
                            data-bs-target="#<?php echo $type; ?>-pane" type="button" role="tab">
                        <?php echo ucfirst(str_replace('_', ' ', $type)); ?> Images
                        <span class="badge bg-secondary ms-1"><?php echo count($images_by_type[$type]); ?></span>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
            
            <!-- Tab Content -->
            <div class="tab-content" id="imageTypeTabsContent">
                <?php foreach ($image_types as $index => $type): ?>
                <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" 
                     id="<?php echo $type; ?>-pane" role="tabpanel">
                    
                    <div class="row">
                        <?php if (empty($images_by_type[$type])): ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No <?php echo $type; ?> images yet</h5>
                                <p class="text-muted">Add some images to get started.</p>
                            </div>
                        </div>
                        <?php else: ?>
                        
                        <?php foreach ($images_by_type[$type] as $image): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-img-top" style="height: 200px; overflow: hidden;">
                                    <img src="<?php echo image_or_placeholder($image['image'], 'site'); ?>" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                         class="img-fluid w-100 h-100" style="object-fit: cover;">
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h6>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($image['description'], 0, 100)); ?>
                                        <?php echo strlen($image['description']) > 100 ? '...' : ''; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">Order: <?php echo $image['sort_order']; ?></small>
                                        <span class="badge bg-<?php echo $image['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($image['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button class="btn btn-outline-primary btn-sm" 
                                            onclick="editImage(<?php echo htmlspecialchars(json_encode($image)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                        <button type="submit" name="delete_image" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Image Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Image Type *</label>
                            <select name="type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="slider">Homepage Slider</option>
                                <option value="atmosphere">Restaurant Atmosphere</option>
                                <option value="philosophy">Philosophy Background</option>
                                <option value="cta">Call to Action Background</option>
                                <option value="about">About Page Background</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Image title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Image description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image File *</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <div class="form-text">Recommended size: 1920x1080px for best quality</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_image" class="btn btn-primary">Add Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Image Modal -->
<div class="modal fade" id="editImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="image_id" id="edit_image_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">New Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div id="current_image_preview"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_image" class="btn btn-primary">Update Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editImage(image) {
    document.getElementById('edit_image_id').value = image.id;
    document.getElementById('edit_title').value = image.title;
    document.getElementById('edit_description').value = image.description;
    document.getElementById('edit_sort_order').value = image.sort_order;
    document.getElementById('edit_status').value = image.status;
    
    const currentImageDiv = document.getElementById('current_image_preview');
    if (image.image) {
        currentImageDiv.innerHTML = `
            <label class="form-label">Current Image:</label><br>
            <img src="../assets/images/site/${image.image}" alt="${image.title}" style="max-width: 300px; max-height: 200px;" class="rounded">
        `;
    } else {
        currentImageDiv.innerHTML = '<p class="text-muted">No current image</p>';
    }
    
    new bootstrap.Modal(document.getElementById('editImageModal')).show();
}
</script>

<?php include 'footer.php'; ?>