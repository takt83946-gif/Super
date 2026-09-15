<?php
include 'config.php';
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>متجري الإلكتروني</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">متجرنا الشامل</a>
            <a href="admin/dashboard.php" class="btn btn-outline-light btn-sm">لوحة التحكم</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4 text-center">أحدث المنتجات</h2>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($product['image'])): ?>
                            <img src="uploads/<?php echo $product['image']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo mb_substr(htmlspecialchars($product['description']), 0, 80); ?>...</p>
                            <p class="card-text text-success fw-bold mt-auto"><?php echo $product['price']; ?> د.أ</p>
                            <a href="https://wa.me/968XXXXXXXX?text=أهلاً، أريد الاستفسار عن منتج: <?php echo urlencode($product['name']); ?>" target="_blank" class="btn btn-success mt-2">اطلب عبر واتساب</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
