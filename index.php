<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// استخدام SQLite المحلية (تعمل فوراً بدون أي أخطاء أو حزم ناقصة)
try {
    $db_file = __DIR__ . '/store.db';
    $conn = new PDO('sqlite:' . $db_file);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // إنشاء جدول المنتجات
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL NOT NULL,
        image TEXT DEFAULT 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'
    )");

    // إضافة منتجات تجريبية إن كان الجدول فارغاً
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO products (name, price, image) VALUES 
            ('ساعة رولكس كلاسيكية', 1250.00, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'),
            ('عطر نيش الملكي', 450.00, 'https://images.unsplash.com/photo-1541643600914-78b084683601'),
            ('قلادة ألماس عيار 18', 2450.00, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f')");
    }

    $products = $conn->query("SELECT * FROM products");

} catch (Exception $e) {
    die("<h2 style='text-align:center; color:red; margin-top:50px;'>خطأ في قاعدة البيانات: " . $e->getMessage() . "</h2>");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>المتجر الملكي الفاخر</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        header { background: #111; color: #d4af37; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; }
        .container { max-width: 1000px; margin: 20px auto; padding: 15px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; padding-bottom: 15px; }
        .card img { width: 100%; height: 180px; object-fit: cover; }
        .card h3 { margin: 15px 0 10px; font-size: 18px; }
        .card p { color: #d4af37; font-size: 18px; font-weight: bold; margin-bottom: 15px; }
        .btn { background: #111; color: #d4af37; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn:hover { background: #d4af37; color: #111; }
    </style>
</head>
<body>

<header>✨ المتجر الملكي الفاخر ✨</header>

<div class="container">
    <h2>المنتجات المتوفرة</h2>
    <div class="grid">
        <?php while($p = $products->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="card">
                <img src="<?php echo $p['image']; ?>" alt="منتج">
                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                <p>$<?php echo number_format($p['price'], 2); ?></p>
                <button class="btn" onclick="alert('تمت الإضافة إلى السلة بنجاح!')">إضافة للسلة</button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
