<?php
require_once 'config.php';

// جلب المنتجات من قاعدة البيانات
try {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجري الإلكتروني - تسوق بأفضل الأسعار</title>
    <!-- Bootstrap 5 CSS RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/rtl.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
        }
        .hero-section {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: white;
            border-radius: 15px;
            padding: 40px 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .card-product {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }
        .card-product:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        .product-img-container {
            height: 180px;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .product-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .price-tag {
            color: #16a34a;
            font-weight: 700;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

    <!-- شريط التنقل العلوي -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3 mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="index.php">
                <i class="fa-solid fa-store text-warning"></i> متجري الإلكتروني
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-house"></i> الرئيسية</a>
                <a href="admin.php" class="btn btn-warning btn-sm fw-bold"><i class="fa-solid fa-gauge"></i> لوحة التحكم</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        
        <!-- قسم الترحيب البانر الاحترافي -->
        <div class="hero-section text-center">
            <h1 class="fw-black mb-2">أهلاً بك في متجرنا الرقمي</h1>
            <p class="text-white-50 mb-0">اكتشف أحدث المنتجات، واطلبها فوراً وبكل سهولة عبر الواتساب.</p>
        </div>

        <!-- عنوان القسم -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h3 class="fw-bold m-0"><i class="fa-solid fa-bag-shopping text-primary"></i> أحدث المنتجات المتوفرة</h3>
            <span class="badge bg-primary px-3 py-2 rounded-pill"><?= count($products); ?> منتج</span>
        </div>
        
        <!-- شبكة المنتجات -->
        <div class="row g-4">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                    <?php 
                        // تجهيز رسالة الواتساب الجاهزة
                        $whatsappMessage = "مرحباً، أود طلب المنتج التالي:\n📦 *{$row['name']}*\n💰 السعر: {$row['price']} {$row['currency']}\nالقسم: {$row['category']}";
                        $whatsappUrl = "https://wa.me/96181058043?text=" . urlencode($whatsappMessage);
                    ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card card-product h-100 shadow-sm">
                            <div class="product-img-container position-relative">
                                <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                    <img src="<?= $row['image']; ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                                <?php else: ?>
                                    <span class="text-muted"><i class="fa-solid fa-image fa-2x"></i></span>
                                <?php endif; ?>
                                <span class="badge bg-dark position-absolute top-0 start-0 m-2" style="font-size: 10px;"><?= htmlspecialchars($row['category']); ?></span>
                            </div>
                            
                            <div class="card-body d-flex flex-column p-3">
                                <h6 class="card-title fw-bold text-dark text-truncate mb-2"><?= htmlspecialchars($row['name']); ?></h6>
                                <div class="price-tag mb-3"><?= number_format($row['price'], 2) . ' ' . $row['currency']; ?></div>
                                
                                <a href="<?= $whatsappUrl; ?>" target="_blank" class="btn btn-success btn-sm mt-auto w-100 fw-bold py-2 shadow-sm">
                                    <i class="fa-brands fa-whatsapp fa-lg"></i> اطلب عبر الواتساب
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm p-5 bg-white rounded-4">
                        <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted fw-bold">لا توجد منتجات معروضة حالياً</h5>
                        <p class="text-muted small mb-3">قم بإضافة منتجات جديدة من خلال لوحة التحكم لتبدأ البيع.</p>
                        <a href="admin.php" class="btn btn-dark btn-sm w-25 mx-auto fw-bold">الذهاب للوحة التحكم</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- فوتر الموقع -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container small text-white-50">
            جميع الحقوق محفوظة &copy; <?= date('Y'); ?> | متجرك الإلكتروني
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
