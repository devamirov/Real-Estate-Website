<?php
require_once 'config.php';
requireLogin();

$pdo = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get property to delete image
    $stmt = $pdo->prepare("SELECT image_path, slides FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch();
    
    if ($property) {
        // Delete main image
        if ($property['image_path'] && file_exists('../' . $property['image_path'])) {
            unlink('../' . $property['image_path']);
        }
        
        // Delete slide images
        if ($property['slides']) {
            $slides = json_decode($property['slides'], true);
            if (is_array($slides)) {
                foreach ($slides as $slide) {
                    if (file_exists('../' . $slide)) {
                        unlink('../' . $slide);
                    }
                }
            }
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: /admin?deleted=1');
        exit;
    }
}

// Get all properties
$properties = $pdo->query("SELECT * FROM properties ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Sheet Homes</title>
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/admin">
                <i class="bi bi-house-door"></i> Sheet Homes Admin
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="/" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> View Site
                </a>
                <a class="nav-link" href="/admin/logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building"></i> Properties Management</h2>
            <a href="/admin/property-form" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Property
            </a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Property deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                Property saved successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($properties)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No properties found. <a href="/admin/property-form">Add your first property</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card property-card">
                            <img src="../<?php echo htmlspecialchars($property['image_path']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($property['title']); ?>"
                                 onerror="this.src='../assets/img/properties/property-1.jpg'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                                    <?php if ($property['featured']): ?>
                                        <span class="badge bg-warning">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <p class="card-text text-muted small mb-2">
                                    <?php echo htmlspecialchars($property['subtitle']); ?>
                                </p>
                                <p class="card-text">
                                    <strong><?php echo htmlspecialchars($property['status']); ?></strong>
                                    <?php if ($property['price']): ?>
                                        | <?php echo htmlspecialchars($property['price']); ?>
                                    <?php endif; ?>
                                </p>
                                <div class="property-info mb-3">
                                    <small class="text-muted">
                                        <?php if ($property['area']): ?>
                                            <i class="bi bi-rulers"></i> <?php echo htmlspecialchars($property['area']); ?>
                                        <?php endif; ?>
                                        <?php if ($property['beds']): ?>
                                            <i class="bi bi-door-open ms-2"></i> <?php echo $property['beds']; ?> Beds
                                        <?php endif; ?>
                                        <?php if ($property['baths']): ?>
                                            <i class="bi bi-droplet ms-2"></i> <?php echo $property['baths']; ?> Baths
                                        <?php endif; ?>
                                    </small>
                                </div>
                                <div class="btn-group w-100" role="group">
                                    <a href="/admin/property-form?id=<?php echo $property['id']; ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="/admin?delete=<?php echo $property['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this property?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

