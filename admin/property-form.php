<?php
require_once 'config.php';
requireLogin();

$pdo = getDB();
$property = null;
$isEdit = false;

// Load property if editing
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $property = $stmt->fetch();
    $isEdit = true;
    
    if (!$property) {
        header('Location: /admin');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $description1 = trim($_POST['description1'] ?? '');
    $description2 = trim($_POST['description2'] ?? '');
    $description3 = trim($_POST['description3'] ?? '');
    $description4 = trim($_POST['description4'] ?? '');
    $property_id = trim($_POST['property_id'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $property_type = trim($_POST['property_type'] ?? 'House');
    $status = trim($_POST['status'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $beds = !empty($_POST['beds']) ? (int)$_POST['beds'] : null;
    $baths = !empty($_POST['baths']) ? (int)$_POST['baths'] : null;
    $garages = !empty($_POST['garages']) ? (int)$_POST['garages'] : null;
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    $image_path = $property['image_path'] ?? '';
    $slides = $property['slides'] ?? '[]';
    
    // Handle main image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, ALLOWED_EXTENSIONS) && $file['size'] <= UPLOAD_MAX_SIZE) {
            // Delete old image if editing
            if ($isEdit && $image_path && file_exists('../' . $image_path)) {
                unlink('../' . $image_path);
            }
            
            $filename = 'property-' . time() . '-' . uniqid() . '.' . $ext;
            $target = UPLOAD_DIR . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $image_path = 'assets/img/properties/' . $filename;
            }
        }
    }
    
    // Handle slide images upload
    if (isset($_FILES['slides']) && !empty($_FILES['slides']['name'][0])) {
        $uploaded_slides = [];
        
        // Delete old slides if editing
        if ($isEdit && $slides) {
            $old_slides = json_decode($slides, true);
            if (is_array($old_slides)) {
                foreach ($old_slides as $old_slide) {
                    if (file_exists('../' . $old_slide)) {
                        unlink('../' . $old_slide);
                    }
                }
            }
        }
        
        foreach ($_FILES['slides']['name'] as $key => $name) {
            if ($_FILES['slides']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $name,
                    'type' => $_FILES['slides']['type'][$key],
                    'tmp_name' => $_FILES['slides']['tmp_name'][$key],
                    'size' => $_FILES['slides']['size'][$key]
                ];
                
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, ALLOWED_EXTENSIONS) && $file['size'] <= UPLOAD_MAX_SIZE) {
                    $filename = 'property-slide-' . time() . '-' . uniqid() . '-' . $key . '.' . $ext;
                    $target = UPLOAD_DIR . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        $uploaded_slides[] = 'assets/img/properties/' . $filename;
                    }
                }
            }
        }
        
        if (!empty($uploaded_slides)) {
            $slides = json_encode($uploaded_slides);
        }
    }
    
    if ($isEdit) {
        // Update existing property
        $stmt = $pdo->prepare("UPDATE properties SET 
            title = ?, subtitle = ?, description1 = ?, description2 = ?, 
            description3 = ?, description4 = ?, image_path = ?, slides = ?,
            property_id = ?, location = ?, property_type = ?, status = ?,
            price = ?, area = ?, beds = ?, baths = ?, garages = ?, featured = ?
            WHERE id = ?");
        
        $stmt->execute([
            $title, $subtitle, $description1, $description2,
            $description3, $description4, $image_path, $slides,
            $property_id, $location, $property_type, $status,
            $price, $area, $beds, $baths, $garages, $featured,
            $property['id']
        ]);
    } else {
        // Insert new property
        $stmt = $pdo->prepare("INSERT INTO properties 
            (title, subtitle, description1, description2, description3, description4,
             image_path, slides, property_id, location, property_type, status,
             price, area, beds, baths, garages, featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $title, $subtitle, $description1, $description2,
            $description3, $description4, $image_path, $slides,
            $property_id, $location, $property_type, $status,
            $price, $area, $beds, $baths, $garages, $featured
        ]);
    }
    
    header('Location: /admin?saved=1');
    exit;
}

// Decode slides for display
$slides_array = [];
if ($property && $property['slides']) {
    $slides_array = json_decode($property['slides'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Property - Sheet Homes Admin</title>
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
                <a class="nav-link" href="/admin">
                    <i class="bi bi-arrow-left"></i> Back to Properties
                </a>
                <a class="nav-link" href="/admin/logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="bi bi-<?php echo $isEdit ? 'pencil' : 'plus-circle'; ?>"></i> 
            <?php echo $isEdit ? 'Edit' : 'Add New'; ?> Property</h2>
        
        <form method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Property Title *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       value="<?php echo htmlspecialchars($property['title'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subtitle" class="form-label">Subtitle/Location *</label>
                                <input type="text" class="form-control" id="subtitle" name="subtitle" 
                                       value="<?php echo htmlspecialchars($property['subtitle'] ?? ''); ?>" required>
                                <small class="text-muted">e.g., "Beirut, Lebanon"</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Rent" <?php echo ($property['status'] ?? '') === 'Rent' ? 'selected' : ''; ?>>Rent</option>
                                    <option value="Sale" <?php echo ($property['status'] ?? '') === 'Sale' ? 'selected' : ''; ?>>Sale</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="text" class="form-control" id="price" name="price" 
                                       value="<?php echo htmlspecialchars($property['price'] ?? ''); ?>"
                                       placeholder="e.g., $1200 or $350.000">
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>Property Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="property_id" class="form-label">Property ID</label>
                                    <input type="text" class="form-control" id="property_id" name="property_id" 
                                           value="<?php echo htmlspecialchars($property['property_id'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="location" class="form-label">Full Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo htmlspecialchars($property['location'] ?? ''); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="property_type" class="form-label">Property Type</label>
                                    <input type="text" class="form-control" id="property_type" name="property_type" 
                                           value="<?php echo htmlspecialchars($property['property_type'] ?? 'House'); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="area" class="form-label">Area</label>
                                    <input type="text" class="form-control" id="area" name="area" 
                                           value="<?php echo htmlspecialchars($property['area'] ?? ''); ?>"
                                           placeholder="e.g., 340m2">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="beds" class="form-label">Beds</label>
                                    <input type="number" class="form-control" id="beds" name="beds" 
                                           value="<?php echo $property['beds'] ?? ''; ?>" min="0">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="baths" class="form-label">Baths</label>
                                    <input type="number" class="form-control" id="baths" name="baths" 
                                           value="<?php echo $property['baths'] ?? ''; ?>" min="0">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="garages" class="form-label">Garages</label>
                                    <input type="number" class="form-control" id="garages" name="garages" 
                                           value="<?php echo $property['garages'] ?? ''; ?>" min="0">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                           <?php echo ($property['featured'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featured">
                                        Featured Property (show on homepage)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>Descriptions</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="description1" class="form-label">Description 1</label>
                                <textarea class="form-control" id="description1" name="description1" rows="3"><?php echo htmlspecialchars($property['description1'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description2" class="form-label">Description 2</label>
                                <textarea class="form-control" id="description2" name="description2" rows="3"><?php echo htmlspecialchars($property['description2'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description3" class="form-label">Description 3</label>
                                <textarea class="form-control" id="description3" name="description3" rows="3"><?php echo htmlspecialchars($property['description3'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description4" class="form-label">Description 4</label>
                                <textarea class="form-control" id="description4" name="description4" rows="3"><?php echo htmlspecialchars($property['description4'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>Main Image *</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($isEdit && $property['image_path']): ?>
                                <img src="../<?php echo htmlspecialchars($property['image_path']); ?>" 
                                     class="img-fluid mb-3" alt="Current image"
                                     onerror="this.style.display='none'">
                            <?php endif; ?>
                            
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/*" <?php echo !$isEdit ? 'required' : ''; ?>>
                            <small class="text-muted">Max 5MB. JPG, PNG, GIF, WEBP</small>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>Slide Images</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($slides_array)): ?>
                                <div class="mb-3">
                                    <strong>Current Slides:</strong>
                                    <?php foreach ($slides_array as $slide): ?>
                                        <div class="mb-2">
                                            <img src="../<?php echo htmlspecialchars($slide); ?>" 
                                                 class="img-thumbnail" style="max-width: 100px;"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <input type="file" class="form-control" id="slides" name="slides[]" 
                                   accept="image/*" multiple>
                            <small class="text-muted">Upload multiple images for property slideshow</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle"></i> Save Property
                </button>
                <a href="/admin" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
            </div>
        </form>
    </div>

    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>

