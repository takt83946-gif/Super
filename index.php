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
    <title>Alind Store</title>
    <!-- Bootstrap 5 CSS RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/rtl.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #fafafa;
            color: #111;
        }
        /* الهيدر النظيف الفخم */
        .store-header {
            background: #ffffff;
            border-bottom: 1px solid #eaeaea;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .store-logo {
            font-weight: 800;
            font-size: 1.4rem;
            color: #000;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .store-logo span {
            color: #4f46e5;
        }
        /* بطاقة المنتج الأنيقة والبسيطة */
        .product-card {
            background: #ffffff;
            border: 1px solid #eaeaea;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .product-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 25px rgba(0,0,0,0.04);
            transform: translateY(-3px);
        }
        .product-img {
            height: 170px;
            background: #f1f5f9;
            position: relative;
            overflow: hidden;
        }
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .badge-cat {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }
        .product-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 5px;
        }
        .product-price {
            font-size: 1.15rem;
            font-weight: 800;
            color: #059669;
        }
        .btn-buy {
            background: #000;
            color: #fff;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px;
            transition: background 0.2s;
            border: none;
        }
        .btn-buy:hover {
            background: #25d366; /* يتحول للأخضر لون الواتساب عند اللمس */
            color: #fff;
        }
        /* زر لوحة التحكم المصغر في الأعلى */
        .btn-admin {
            background: #f1f5f9;
            color: #334155;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 12px;
            text-decoration: none;
            border: 1px solid #e2e8f0;
        }
        .btn-admin:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
    </style>
</head>
<body>

    <!-- هيدر نظيف وبسيط بدون حشو كلام -->
    <header class="store-header">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="index.php" class="store-logo">
                Alind<span>Store</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="admin.php" class="btn-admin">
                    <i class="fa-solid fa-gauge-high"></i> لوحة التحكم
                </a>
            </div>
        </div>
    </header>

    <div class="container py-4">
        
        <!-- شريط علوي صغير يوضح عدد المنتجات فقط -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="text-muted fw-bold" style="font-size: 0.9rem;">المنتجات المتاحة (<?= count($products); ?>)</span>
            <input type="text" id="quickSearch" class="form-control form-control-sm w-50 rounded-pill px-3" placeholder="بحث سريع..." style="border-color: #cbd5e1;">
        </div>

        <!-- شبكة المنتجات -->
        <div class="row g-3" id="productsGrid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                    <?php 
                        $whatsappMessage = "مرحباً، أود طلب المنتج: *{$row['name']}* بسعر: {$row['price']} {$row['currency']}";
                        $whatsappUrl = "https://wa.me/96181058043?text=" . urlencode($whatsappMessage);
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 product-item" data-name="<?= htmlspecialchars($row['name']); ?>">
                        <div class="product-card h-100 d-flex flex-column">
                            <div class="product-img">
                                <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                    <img src="<?= $row['image']; ?>" alt="product">
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                        <i class="fa-solid fa-box fa-lg opacity-25"></i>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($row['category'])): ?>
                                    <span class="badge-cat"><?= htmlspecialchars($row['category']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <h6 class="product-title text-truncate"><?= htmlspecialchars($row['name']); ?></h6>
                                <div class="product-price mb-3"><?= number_format($row['price'], 2) . ' ' . $row['currency']; ?></div>
                                
                                <a href="<?= $whatsappUrl; ?>" target="_blank" class="btn btn-buy mt-auto w-100 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa-brands fa-whatsapp fs-5"></i> اطلب الآن
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted small">لا توجد منتجات مضافة حالياً.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- سكريبت البحث الفوري البسيط -->
    <script>
        document.getElementById('quickSearch').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.product-item');
            items.forEach(function(item) {
                let name = item.getAttribute('data-name').toLowerCase();
                item.style.display = name.includes(filter) ? "" : "none";
            });
        });
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
