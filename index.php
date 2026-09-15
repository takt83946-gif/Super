<?php
// استدعاء ملف الاتصال بقاعدة البيانات
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>متجر سوبرماركت الساحة</title>
    <!-- استدعاء Bootstrap للتصميم الجميل -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- شريط العرض العلوي -->
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">🛒 سوبرماركت الساحة</a>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <div class="container py-5">
        <div class="row text-center mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold text-dark">أهلاً بك في متجرنا!</h1>
                <p class="text-muted">تم ربط قاعدة البيانات وتشغيل الموقع بنجاح تام على السيرفر.</p>
            </div>
        </div>

        <!-- قسم المنتجات -->
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <div class="col">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold">تجربة منتج</h5>
                        <p class="card-text text-success fw-bold">1,000 د.أ</p>
                        <a href="#" class="btn btn-primary w-100">أضف إلى السلة</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
