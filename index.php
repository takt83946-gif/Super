<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// الاتصال بقاعدة البيانات مع حماية ضد الأخطاء
$host = getenv('MYSQLHOST') ?: 'localhost';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'elite_boutique';
$port = (int)(getenv('MYSQLPORT') ?: 3306);

$conn = @new mysqli($host, $username, $password, "", $port);

if ($conn->connect_error) {
    die("<h2 style='text-align:center; color:red; margin-top:50px;'>خطأ في الاتصال بقاعدة البيانات: " . $conn->connect_error . "</h2>");
}

$conn->set_charset("utf8mb4");
$conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$conn->select_db($database);

$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500) DEFAULT 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$res = $conn->query("SELECT COUNT(*) as cnt FROM products");
$row = $res ? $res->fetch_assoc() : ['cnt' => 0];
if ($row['cnt'] == 0) {
    $conn->query("INSERT INTO products (name, price, image) VALUES 
        ('ساعة رولكس كلاسيكية', 1250.00, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'),
        ('عطر نيش الملكي', 450.00, 'https://images.unsplash.com/photo-1541643600914-78b084683601'),
        ('قلادة ألماس عيار 18', 2450.00, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f')");
}

$products = $conn->query("SELECT * FROM products");
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
        <?php while($p = $products->fetch_assoc()): ?>
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
