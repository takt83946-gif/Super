<?php
require_once 'config.php';

// إنشاء جدول المنتجات تلقائياً إذا لم يكن موجوداً لضمان عدم حدوث أخطاء
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // إضافة منتج افتراضي إن كان الجدول فارغاً
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO products (name, price) VALUES ('حليب الساحة الطازج', 1.50), ('خبز عربي فريش', 0.50), ('جبنة بيضاء', 3.25)");
    }

    // جلب المنتجات من قاعدة البيانات
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
    <title>سوبرماركت الساحة</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- شريط العرض العلوي -->
    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">🛒 سوبرماركت الساحة</a>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <div class="container">
        <div class="row text-center mb-4">
            <div class="col-12">
                <h1 class="h3 fw-bold text-dark">قائمة المنتجات المتوفرة</h1>
                <p class="text-muted small">تم الاتصال بقاعدة البيانات السحابية بنجاح</p>
            </div>
        </div>

        <!-- شبكة المنتجات -->
        <div class="row row-cols-1 row-cols-md-3 g-3">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="card-title fw-bold text-dark"><?= htmlspecialchars($row['name']); ?></h5>
                                    <p class="card-text text-success fw-bold fs-5"><?= number_format($row['price'], 2); ?> د.أ</p>
                                </div>
                                <a href="https://wa.me/?text=مرحباً، أريـد طلب: <?= urlencode($row['name']); ?>" target="_blank" class="btn btn-success btn-sm w-100 mt-3">طلب عبر واتساب 📱</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
