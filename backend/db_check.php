<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=laravel;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query products and their images
    $stmt = $pdo->query("SELECT p.name, p.slug, pi.image_url FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
