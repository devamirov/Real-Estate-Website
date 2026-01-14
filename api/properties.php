<?php
header('Content-Type: application/json');
require_once '../admin/config.php';

try {
    $pdo = getDB();
    
    // Get single property by ID
    if (isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
        $stmt->execute([$id]);
        $property = $stmt->fetch();
        
        if ($property) {
            // Decode slides JSON
            $property['slides'] = json_decode($property['slides'] ?? '[]', true);
            echo json_encode($property);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Property not found']);
        }
        exit;
    }
    
    // Get featured properties (for homepage)
    if (isset($_GET['featured']) && $_GET['featured'] == '1') {
        $properties = $pdo->query("SELECT * FROM properties WHERE featured = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
    } 
    // Get all properties
    else {
        $properties = $pdo->query("SELECT * FROM properties ORDER BY created_at DESC")->fetchAll();
    }
    
    // Decode slides JSON for each property
    foreach ($properties as &$property) {
        $property['slides'] = json_decode($property['slides'] ?? '[]', true);
    }
    
    echo json_encode($properties);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>

