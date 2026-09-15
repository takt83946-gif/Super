<?php
require_once 'config.php';

// معالجة تسجيل الطلب عند ضغط زر الواتساب
if (isset($_GET['buy']) && isset($_GET['pname']) && isset($_GET['pprice'])) {
    $pname = $_GET['pname'];
    $pprice = $_GET['pprice'];
    try {
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, product_name, total_price) VALUES (?, ?, ?)");
        $stmt->execute(['زبون واتساب', $pname, $pprice]);
    } catch(PDOException $e) {}
}

try {
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

    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-mobile-screen-button"></i> عالم الهواتف والإكسسوارات</a>
        </div>
    </nav>

    <div class="container">
        <div class="row text-center mb-4">
            <div class="col-12">
                <h1 class="h3 fw-bold text-dark">أحدث الهواتف والإكسسوارات</h1>
                <p class="text-muted small">اختر منتجك واطلبه فوراً عبر الواتساب</p>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): 
                    $priceFormatted = number_format($row['price'], 2) . ' ' . $row['currency'];
                    $waText = "مرحباً، أرغب في طلب هذا المنتج:\n" . $row['name'] . "\nالسعر: " . $priceFormatted;
                    $waLink = "https://wa.me/?text=" . urlencode($waText);
                    $trackLink = "index.php?buy=1&pname=" . urlencode($row['name']) . "&pprice=" . urlencode($priceFormatted);
                ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                <img src="<?= $row['image']; ?>" class="card-img-top" alt="product" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fa-solid fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-secondary mb-2"><?= htmlspecialchars($row['category']); ?></span>
                                    <h5 class="card-title fw-bold text-dark mb-2"><?= htmlspecialchars($row['name']); ?></h5>
                                    <p class="card-text text-success fw-bold fs-4"><?= $priceFormatted; ?></p>
                                </div>
                                <a href="<?= $waLink; ?>&redirect=<?= urlencode($trackLink); ?>" onclick="setTimeout(function(){ window.location.href='<?= $trackLink; ?>'; }, 1000);" target="_blank" class="btn btn-success w-100 mt-3">
                                    <i class="fa-brands fa-whatsapp"></i> اطلب عبر واتساب
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">لا توجد منتجات مضافة حالياً. تفضل بزيارة لوحة التحكم لإضافة أول منتج!</p>
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
