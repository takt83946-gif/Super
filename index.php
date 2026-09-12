<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: '127.0.0.1';
$username = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: 'railway';
$port = (int)(getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306);

$conn = @new mysqli($host, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("خطأ في الاتصال بقاعدة البيانات");
}
$conn->set_charset("utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500) DEFAULT 'https://images.unsplash.com/photo-1542838132-92c53300491e'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$res = $conn->query("SELECT COUNT(*) as cnt FROM products");
$row = $res ? $res->fetch_assoc() : ['cnt' => 0];
if ($row['cnt'] == 0) {
    $conn->query("INSERT INTO products (name, price, image) VALUES 
        ('حليب طازج 1 لتر', 2.50, 'https://images.unsplash.com/photo-1563636619-e9143da7973b'),
        ('خبز أبيض طازج', 1.00, 'https://images.unsplash.com/photo-1509440159596-0249088772ff'),
        ('تفاح ريد ديلشيش (كغ)', 3.20, 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6'),
        ('موز طازج (كغ)', 2.80, 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e'),
        ('جبنة كلاسيكية بيضاء', 5.00, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d'),
        ('مياه معدنية (عبوة)', 0.50, 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d')");
}

$products = $conn->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سوبرماركت الخير - تسوق أونلاين</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f9f9f9; margin: 0; padding: 0; }
        header { background: #27ae60; color: #fff; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; }
        .top-bar { text-align: left; padding: 10px 20px; background: #2196f3; }
        .top-bar a { color: #fff; text-decoration: none; font-weight: bold; background: rgba(0,0,0,0.1); padding: 5px 10px; border-radius: 4px; }
        .container { max-width: 1000px; margin: 20px auto; padding: 15px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
        .card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; padding-bottom: 15px; border: 1px solid #eee; }
        .card img { width: 100%; height: 160px; object-fit: cover; }
        .card h3 { margin: 12px 0 8px; font-size: 18px; color: #333; }
        .card p { color: #e67e22; font-size: 18px; font-weight: bold; margin-bottom: 12px; }
        .btn { background: #27ae60; color: #fff; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold; font-family: 'Cairo', sans-serif; }
        .btn:hover { background: #2196f3; }
    </style>
</head>
<body>

<div class="top-bar">
    <a href="admin.php">⚙️ لوحة التحكم</a>
</div>

<header>🛒 سوبرماركت الخير - طازج كل يوم</header>

<div class="container">
    <h2>المنتجات والسلع الغذائية</h2>
    <div class="grid">
        <?php while($p = $products->fetch_assoc()): ?>
            <div class="card">
                <img src="<?php echo $p['image']; ?>" alt="منتج">
                <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                <p>$<?php echo number_format($p['price'], 2); ?></p>
                <button class="btn" onclick="alert('تمت إضافة المنتج إلى سلة المشتريات!')">إضافة للسلة</button>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
