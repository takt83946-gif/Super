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
    <title>Alind Store | المتجر الأقوى والأحدث</title>
    <!-- Bootstrap 5 CSS RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/rtl.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            --dark-bg: #090d16;
            --card-bg: #ffffff;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        /* شريط الإعلانات العلوي */
        .top-announcement {
            background: linear-gradient(90deg, #4f46e5, #9333ea);
            color: white;
            font-size: 13px;
            font-weight: 600;
            padding: 8px 0;
            text-align: center;
        }
        /* ناف بار عصري */
        .navbar-custom {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        /* الهيرو بانر الأسطوري */
        .hero-banner {
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a);
            color: white;
            border-radius: 24px;
            padding: 50px 30px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(168, 85, 247, 0.2);
            filter: blur(60px);
            border-radius: 50%;
        }
        /* بطاقات المنتجات الرهيبة */
        .product-card {
            border: none;
            border-radius: 18px;
            background: var(--card-bg);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .img-wrapper {
            height: 200px;
            background: #f8fafc;
            position: relative;
            overflow: hidden;
        }
        .img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .product-card:hover .img-wrapper img {
            transform: scale(1.08);
        }
        .category-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            color: white;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 30px;
            font-weight: 600;
        }
        .price-text {
            font-size: 1.25rem;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .btn-whatsapp {
            background: #25d366;
            color: white;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }
        .btn-whatsapp:hover {
            background: #22bf5b;
            color: white;
            transform: scale(1.02);
            box-shadow: 0 6px 15px rgba(37, 211, 102, 0.4);
        }
        .search-input {
            border-radius: 50px;
            padding: 12px 20px;
            border: 2px solid #e5e7eb;
            box-shadow: none;
            transition: border-color 0.2s;
        }
        .search-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
    </style>
</head>
<body>

    <!-- شريط إعلاني متحرك -->
    <div class="top-announcement">
        <i class="fa-solid fa-fire text-warning"></i> أهلاً بك في Alind Store - اطلب الآن ويوصلك الطلب بأسرع وقت! <i class="fa-solid fa-truck-fast"></i>
    </div>

    <!-- شريط التنقل العلوي -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-3">
        <div class="container">
            <a class="navbar-brand fw-black fs-3 d-flex align-items-center gap-2" href="index.php">
                <div class="bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 40px; height: 40px; background: linear-gradient(135deg, #6366f1, #ec4899);">
                    <i class="fa-solid fa-store fs-5"></i>
                </div>
                <span>Alind<span class="text-primary">Store</span></span>
            </a>
            
            <div class="d-flex align-items-center gap-2">
                <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3"><i class="fa-solid fa-house"></i> الرئيسية</a>
                <a href="admin.php" class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-dark"><i class="fa-solid fa-gauge text-purple"></i> لوحة التحكم</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        
        <!-- بانر الترحيب الأسطوري -->
        <div class="hero-banner text-center text-lg-start position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-purple bg-opacity-25 text-white mb-3 px-3 py-2 rounded-pill border border-light border-opacity-10" style="background: rgba(168, 85, 247, 0.3);">✨ المنصة الأسرع والأفضل للتسوق</span>
                    <h1 class="display-5 fw-black mb-3">اكتشف روعة التسوق مع Alind Store</h1>
                    <p class="text-white-50 lead mb-0">أحدث المنتجات المميزة، أصلية وذات جودة عالية. اختر منتجك واطلب بضغطة زر واحدة عبر الواتساب.</p>
                </div>
                <div class="col-lg-4 text-center mt-4 mt-lg-0">
                    <div class="p-3 bg-white bg-opacity-10 rounded-4 backdrop-blur border border-white border-opacity-10">
                        <h3 class="fw-bold text-warning mb-1"><?= count($products); ?></h3>
                        <p class="text-white-50 small mb-0">منتج متوفر في المتجر حالياً</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- شريط البحث السريع وتصفية العنوان -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-6">
                <h3 class="fw-black m-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-bag-shopping text-indigo"></i> أحدث المنتجات المعروضة
                </h3>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchBox" class="form-control search-input" placeholder="ابحث عن أي منتج...">
                    <span class="input-group-text bg-white border-2 border-start-0 rounded-start-0 rounded-pill px-4 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- شبكة المنتجات الاحترافية -->
        <div class="row g-4" id="productsGrid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                    <?php 
                        $whatsappMessage = "مرحباً Alind Store 🌟\nأود طلب المنتج التالي:\n\n📦 *{$row['name']}*\n💰 السعر: {$row['price']} {$row['currency']}\n🏷️ القسم: {$row['category']}\n\nيرجى تأكيد الطلب.";
                        $whatsappUrl = "https://wa.me/96181058043?text=" . urlencode($whatsappMessage);
                    ?>
                    <div class="col-6 col-md-4 col-lg-3 product-item" data-name="<?= htmlspecialchars($row['name']); ?>">
                        <div class="card product-card h-100">
                            <div class="img-wrapper">
                                <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                    <img src="<?= $row['image']; ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted bg-light">
                                        <i class="fa-solid fa-image fa-2x opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="category-badge"><i class="fa-solid fa-tag me-1"></i><?= htmlspecialchars($row['category']); ?></span>
                            </div>
                            
                            <div class="card-body d-flex flex-column p-3">
                                <h6 class="card-title fw-bold text-dark text-truncate mb-2" title="<?= htmlspecialchars($row['name']); ?>"><?= htmlspecialchars($row['name']); ?></h6>
                                <div class="price-text mb-3"><?= number_format($row['price'], 2) . ' ' . $row['currency']; ?></div>
                                
                                <a href="<?= $whatsappUrl; ?>" target="_blank" class="btn btn-whatsapp btn-sm mt-auto w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa-brands fa-whatsapp fs-5"></i> اطلب عبر الواتساب
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <div class="card border-0 shadow-sm p-5 bg-white rounded-4">
                        <i class="fa-solid fa-box-open fa-4x text-muted mb-3 opacity-50"></i>
                        <h4 class="text-dark fw-bold">لا توجد منتجات معروضة حالياً</h4>
                        <p class="text-muted small mb-4">قم بإضافة أول منتج لك من لوحة التحكم ليبدأ المتجر بالعمل.</p>
                        <a href="admin.php" class="btn btn-dark w-25 mx-auto fw-bold rounded-pill py-2">لوحة التحكم</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- فوتر الموقع الفاخر -->
    <footer class="bg-dark text-white text-center py-4 mt-5 border-top border-secondary border-opacity-25">
        <div class="container">
            <h5 class="fw-bold mb-2">Alind Store</h5>
            <p class="text-white-50 small mb-3">وجهتك الأولى لتسوق أروع المنتجات بكل سهولة وأمان.</p>
            <div class="small text-muted">
                جميع الحقوق محفوظة &copy; <?= date('Y'); ?> | تم التطوير بحرفية عالية 🚀
            </div>
        </div>
    </footer>

    <!-- سكريبت البحث الفوري -->
    <script>
        document.getElementById('searchBox').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let items = document.querySelectorAll('.product-item');
            
            items.forEach(function(item) {
                let name = item.getAttribute('data-name').toLowerCase();
                if (name.includes(filter)) {
                    item.style.display = "";
                } else {
                    item.style.display = "none";
                }
            });
        });
    </script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
