<?php
require_once 'config.php';

// إنشاء جدول المنتجات وتعبئته بمنتجات هواتف وإكسسوارات افتراضية إن كان فارغاً
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        category VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO products (name, price, category) VALUES 
            ('آيفون 15 برو ماكس - 256 جيجابايت', 1199.00, 'هواتف ذكية'),
            ('سامسونج جالاكسي S24 ألترا', 1099.00, 'هواتف ذكية'),
            ('شاومي ريدمي نوت 13 برو', 299.00, 'هواتف ذكية'),
            ('سماعة أبل AirPods Pro الجيل الثاني', 249.00, 'إكسسوارات'),
            ('شاحن سريع أصلي 25 واط', 25.00, 'إكسسوارات'),
            ('كفر حماية سيليكون أنيق', 15.00, 'إكسسوارات')
        ");
    }

    $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجر الهواتف والإكسسوارات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <!-- شريط العرض العلوي -->
    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-mobile-screen-button"></i> عالم الهواتف والإكسسوارات</a>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <div class="container">
        <div class="row text-center mb-4">
            <div class="col-12">
                <h1 class="h3 fw-bold text-dark">أحدث الهواتف والإكسسوارات الأصلية</h1>
                <p class="text-muted small">اطلب منتجك المفضل مباشرة عبر الواتساب بكل سهولة</p>
            </div>
        </div>

        <!-- شبكة المنتجات -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-secondary mb-2"><?= htmlspecialchars($row['category']); ?></span>
                                    <h5 class="card-title fw-bold text-dark mb-3"><?= htmlspecialchars($row['name']); ?></h5>
                                    <p class="card-text text-primary fw-bold fs-4"><?= number_format($row['price'], 2); ?> $</p>
                                </div>
                                <a href="https://wa.me/?text=مرحباً، أريـد طلب هذا المنتج: <?= urlencode($row['name']); ?> - السعر: <?= $row['price']; ?>$" target="_blank" class="btn btn-success w-100 mt-3">
                                    <i class="fa-brands fa-whatsapp"></i> اطلب عبر واتساب
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">لا توجد منتجات مضافة حالياً.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center text-muted py-4 mt-5 border-top">
        <p class="small mb-0">جميع الحقوق محفوظة &copy; 2026</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
