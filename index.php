<?php
require_once 'config.php';

// جلب الأقسام والمنتجات
try {
    $products =$conn->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    // جلب الأقسام الفريدة للمتجر
    $categories =$conn->query("SELECT DISTINCT category FROM products WHERE category != ''")->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $products = [];$categories = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alind Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f7f7f7;
            color: #111;
            padding-bottom: 70px; /* مساحة للشريط السفلي للجوال */
        }
        /* الهيدر العلوي */
        .main-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .logo-text {
            font-weight: 900;
            font-size: 1.5rem;
            color: #000;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .logo-text span {
            color: #2563eb;
        }
        /* شريط البحث */
        .search-box {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 15px;
        }
        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 0.9rem;
        }
        /* القائمة الجانبية الأنيقة Offcanvas */
        .offcanvas-header {
            background: #111;
            color: #fff;
        }
        .offcanvas-body {
            background: #fff;
            padding: 0;
        }
        .menu-item-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            color: #334155;
            text-decoration: none;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            transition: background 0.2s;
        }
        .menu-item-link:hover {
            background: #f8fafc;
            color: #2563eb;
        }
        /* بطاقات المنتجات */
        .product-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
        .product-img-wrap {
            height: 160px;
            background: #f9fafb;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .product-img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .wishlist-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #fff;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            color: #64748b;
        }
        .product-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .product-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: #059669;
            margin-bottom: 12px;
        }
        .btn-select-options {
            background: #1e293b;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px;
            width: 100%;
            border: none;
            transition: background 0.2s;
        }
        .btn-select-options:hover {
            background: #25d366; /* يتحول لأخضر الواتساب عند اللمس */
            color: #fff;
        }
        /* شريط التنقل السفلي للجوال (Bottom Navigation Bar) مطابق للفيديو */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 1030;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.03);
        }
        .bottom-nav-item {
            color: #64748b;
            text-decoration: none;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .bottom-nav-item i {
            font-size: 1.25rem;
            margin-bottom: 3px;
        }
        .bottom-nav-item.active, .bottom-nav-item:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>

    <header class="main-header py-2">
        <div class="container d-flex align-items-center justify-content-between">
            <button class="btn border-0 p-0 fs-4 text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainMenu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <a href="index.php" class="logo-text">
                Alind<span>Store</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <a href="admin.php" class="text-dark fs-5" title="لوحة التحكم"><i class="fa-solid fa-gauge"></i></a>
            </div>
        </div>
    </header>

    <div class="container mt-3">
        <div class="search-box d-flex align-items-center">
            <i class="fa-solid fa-magnifying-glass text-muted me-2"></i>
            <input type="text" id="searchInput" placeholder="ابحث عن المنتجات...">
        </div>
    </div>

    <div class="container my-4">
        <h4 class="fw-bold mb-3" id="sectionTitle">أحدث المنتجات</h4>

        <div class="row g-3" id="productsGrid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as$row): ?>
                    <?php 
                        $whatsappMessage = "مرحباً Alind Store، أريد طلب المنتج: *{$row['name']}* بسعر: {$row['price']} {$row['currency']}";
                        $whatsappUrl = "https://wa.me/96181058043?text=" . urlencode($whatsappMessage);
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 product-item" data-name="<?= htmlspecialchars($row['name']); ?>" data-category="<?= htmlspecialchars($row['category']); ?>">
                        <div class="product-card d-flex flex-column p-2">
                            <div class="product-img-wrap rounded">
                                <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                    <img src="<?= $row['image']; ?>" alt="product">
                                <?php else: ?>
                                    <i class="fa-solid fa-box fa-2x text-muted opacity-25"></i>
                                <?php endif; ?>
                                <button class="wishlist-btn"><i class="fa-regular fa-heart"></i></button>
                            </div>

                            <div class="card-body p-2 d-flex flex-column flex-grow-1">
                                <span class="text-muted small mb-1"><?= htmlspecialchars($row['category']); ?></span>
                                <h6 class="product-title text-truncate"><?= htmlspecialchars($row['name']); ?></h6>
                                <div class="product-price mt-auto"><?= number_format($row['price'], 2) . ' ' .$row['currency']; ?></div>
                                
                                <a href="<?= $whatsappUrl; ?>" target="_blank" class="btn-select-options text-center text-decoration-none">
                                    <i class="fa-brands fa-whatsapp me-1"></i> اطلب الآن
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">لا توجد منتجات مضافة حالياً. أضفها من <a href="admin.php">لوحة التحكم</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mainMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-bars me-2"></i> القائمة</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <a href="index.php" class="menu-item-link">
                <span><i class="fa-solid fa-house me-2 text-primary"></i> الرئيسية</span>
                <i class="fa-solid fa-chevron-left text-muted small"></i>
            </a>
            <a href="admin.php" class="menu-item-link">
                <span><i class="fa-solid fa-gauge me-2 text-warning"></i> لوحة التحكم</span>
                <i class="fa-solid fa-chevron-left text-muted small"></i>
            </a>
            <hr class="text-muted my-2">
            <div class="px-3 py-2 text-muted fw-bold small">الأقسام المتوفرة:</div>
            <?php if (!empty($categories)): ?>
                <?php foreach($categories as$cat): ?>
                    <a href="#" class="menu-item-link category-filter" data-category="<?= htmlspecialchars($cat); ?>" data-bs-dismiss="offcanvas">
                        <span><i class="fa-solid fa-tag me-2 text-secondary"></i> <?= htmlspecialchars($cat); ?></span>
                        <i class="fa-solid fa-chevron-left text-muted small"></i>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-3 text-muted small">لا توجد أقسام مضافة بعد</div>
            <?php endif; ?>
        </div>
    </div>

    <nav class="bottom-nav">
        <a href="index.php" class="bottom-nav-item active">
            <i class="fa-solid fa-house"></i>
            <span>الرئيسية</span>
        </a>
        <a href="#" class="bottom-nav-item" onclick="alert('المفضلة فارغة'); return false;">
            <i class="fa-regular fa-heart"></i>
            <span>المفضلة</span>
        </a>
        <a href="admin.php" class="bottom-nav-item">
            <i class="fa-regular fa-user"></i>
            <span>الإدارة</span>
        </a>
        <a href="https://wa.me/96181058043" target="_blank" class="bottom-nav-item">
            <i class="fa-regular fa-comment-dots"></i>
            <span>الدعم</span>
        </a>
        <a href="index.php" class="bottom-nav-item">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>المتجر</span>
        </a>
    </nav>

    <script>
        // البحث الفوري
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.product-item');
            items.forEach(function(item) {
                let name = item.getAttribute('data-name').toLowerCase();
                item.style.display = name.includes(filter) ? "" : "none";
            });
        });

        // تصفية حسب القسم عند النقر في القائمة الجانبية
        document.querySelectorAll('.category-filter').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let selectedCat = this.getAttribute('data-category');
                document.getElementById('sectionTitle').innerText = "قسم: " +
