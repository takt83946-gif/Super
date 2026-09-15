<?php
require_once 'config.php';

// جلب المنتجات والأقسام
try {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $categories = $conn->query("SELECT DISTINCT category FROM products WHERE category != ''")->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) {
    $products = [];
    $categories = [];
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
            background-color: #f8f9fa;
            color: #111;
            padding-bottom: 75px; /* مساحة للشريط السفلي للجوال */
        }
        .main-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 1020;
        }
        .logo-text {
            font-weight: 900;
            font-size: 1.4rem;
            color: #000;
            text-decoration: none;
        }
        .logo-text span {
            color: #4f46e5;
        }
        .search-container {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 8px 15px;
            border: 1px solid #e2e8f0;
        }
        .search-container input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 0.9rem;
        }
        .product-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
            height: 100%;
        }
        .product-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transform: translateY(-3px);
        }
        .product-img-box {
            height: 160px;
            background: #f8fafc;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .product-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .wish-btn {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #fff;
            border: none;
            width: 30px;
            height: 30px;
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
            margin-bottom: 4px;
        }
        .product-price {
            font-size: 1.1rem;
            font-weight: 800;
            color: #059669;
            margin-bottom: 10px;
        }
        .btn-order {
            background: #0f172a;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px;
            width: 100%;
            border: none;
            transition: background 0.2s;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        .btn-order:hover {
            background: #25d366; /* يتحول لأخضر الواتساب عند اللمس */
            color: #fff;
        }
        /* القائمة الجانبية */
        .offcanvas-header-custom {
            background: #0f172a;
            color: #fff;
        }
        .menu-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            color: #334155;
            text-decoration: none;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
        }
        .menu-link:hover {
            background: #f8fafc;
            color: #4f46e5;
        }
        /* شريط التنقل السفلي الثابت */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-around;
            padding: 8px 0;
            z-index: 1030;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.03);
        }
        .bottom-nav-item {
            color: #64748b;
            text-decoration: none;
            text-align: center;
            font-size: 0.7rem;
            font-weight: 600;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .bottom-nav-item i {
            font-size: 1.2rem;
            margin-bottom: 2px;
        }
        .bottom-nav-item.active, .bottom-nav-item:hover {
            color: #4f46e5;
        }
    </style>
</head>
<body>

    <header class="main-header py-2">
        <div class="container d-flex align-items-center justify-content-between">
            <button class="btn border-0 p-0 fs-4 text-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuSidebar">
                <i class="fa-solid fa-bars"></i>
            </button>

            <a href="index.php" class="logo-text">
                Alind<span>Store</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <a href="admin.php" class="text-dark fs-5"><i class="fa-solid fa-gauge"></i></a>
            </div>
        </div>
    </header>

    <div class="container mt-3">
        <div class="search-container d-flex align-items-center">
            <i class="fa-solid fa-magnifying-glass text-muted me-2"></i>
            <input type="text" id="searchInput" placeholder="ابحث عن المنتجات...">
        </div>
    </div>

    <div class="container my-4">
        <h4 class="fw-bold mb-3" id="pageTitle">أحدث المنتجات</h4>

        <div class="row g-3" id="productsGrid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                    <?php 
                        $whatsappMessage = "مرحباً Alind Store، أود طلب المنتج: *{$row['name']}* بسعر: {$row['price']} {$row['currency']}";
                        $whatsappUrl = "https://wa.me/96181058043?text=" . urlencode($whatsappMessage);
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 product-item" data-name="<?= htmlspecialchars($row['name']); ?>" data-category="<?= htmlspecialchars($row['category']); ?>">
                        <div class="product-card d-flex flex-column p-2">
                            <div class="product-img-box rounded">
                                <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                    <img src="<?= $row['image']; ?>" alt="product">
                                <?php else: ?>
                                    <i class="fa-solid fa-box fa-2x text-muted opacity-25"></i>
                                <?php endif; ?>
                                <button class="wish-btn" onclick="alert('تمت الإضافة للمفضلة')"><i class="fa-regular fa-heart"></i></button>
                            </div>

                            <div class="card-body p-2 d-flex flex-column flex-grow-1">
                                <span class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($row['category']); ?></span>
                                <h6 class="product-title text-truncate"><?= htmlspecialchars($row['name']); ?></h6>
                                <div class="product-price mt-auto"><?= $row['price'] . ' ' . $row['currency']; ?></div>
                                
                                <a href="<?= $whatsappUrl; ?>" target="_blank" class="btn-order">
                                    <i class="fa-brands fa-whatsapp"></i> اطلب الآن
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">لا توجد منتجات مضافة حالياً. قم بإضافتها من <a href="admin.php">لوحة التحكم</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="menuSidebar">
        <div class="offcanvas-header offcanvas-header-custom">
            <h5 class="offcanvas-title fw-bold"><i class="fa-solid fa-bars me-2"></i> القائمة</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <a href="index.php" class="menu-link">
                <span><i class="fa-solid fa-house me-2 text-primary"></i> الرئيسية</span>
                <i class="fa-solid fa-chevron-left text-muted small"></i>
            </a>
            <a href="admin.php" class="menu-link">
                <span><i class="fa-solid fa-gauge me-2 text-warning"></i> لوحة التحكم والإدارة</span>
                <i class="fa-solid fa-chevron-left text-muted small"></i>
            </a>
            <hr class="my-2 text-muted">
            <div class="px-3 py-2 text-muted fw-bold" style="font-size: 0.85rem;">الأقسام المتوفرة:</div>
            <?php if (!empty($categories)): ?>
                <?php foreach($categories as $cat): ?>
                    <a href="#" class="menu-link category-filter" data-category="<?= htmlspecialchars($cat); ?>" data-bs-dismiss="offcanvas">
                        <span><i class="fa-solid fa-tag me-2 text-secondary"></i> <?= htmlspecialchars($cat); ?></span>
                        <i class="fa-solid fa-chevron-left text-muted small"></i>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="px-3 text-muted small">لا توجد أقسام حالياً</div>
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
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.product-item');
            items.forEach(function(item) {
                let name = item.getAttribute('data-name').toLowerCase();
                item.style.display = name.includes(filter) ? "" : "none";
            });
        });

        document.querySelectorAll('.category-filter').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let selectedCat = this.getAttribute('data-category');
                document.getElementById('pageTitle').innerText = "قسم: " + selectedCat;
                let items = document.querySelectorAll('.product-item');
                items.forEach(function(item) {
                    let cat = item.getAttribute('data-category');
                    item.style.display = (cat === selectedCat) ? "" : "none";
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
