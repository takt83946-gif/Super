<?php
// إعدادات الاتصال بقاعدة البيانات عبر MySQLi
$host = getenv('MYSQLHOST') ?: 'localhost';
$username = getenv('MYSQLUSER') ?: 'root';$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'elite_boutique';$port = (int)(getenv('MYSQLPORT') ?: 3306);

// الاتصال بالسيرفر
$conn = new mysqli($host,$username, $password, "", $port);
if ($conn->connect_error) {
    die("خطأ في الاتصال بالسيرفر: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// إنشاء القاعدة والجداول إن لم تكن موجودة
$conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$conn->select_db($database);

$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(100) NOT NULL,
    image VARCHAR(500) DEFAULT 'https://images.unsplash.com/photo-1523275335684-37898b6baf30',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL,
    client_phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    items TEXT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'قيد المعالجة',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// إضافة منتجات تجريبية لو الجدول فارغ
$res =$conn->query("SELECT COUNT(*) as cnt FROM products");
$row =$res->fetch_assoc();
if ($row['cnt'] == 0) {$conn->query("INSERT INTO products (name, price, category, image) VALUES 
        ('ساعة رولكس كلاسيكية إصدار خاص', 1250.00, 'ساعات ملكية', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'),
        ('عطر نيش الملكي الفاخر', 450.00, 'عطور نادرة', 'https://images.unsplash.com/photo-1541643600914-78b084683601'),
        ('قلادة ألماس عيار 18', 2450.00, 'مجوهرات وألماس', 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f')");
}

// معالجة طلب الشراء عند إرساله
$success_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $c_name =$conn->real_escape_string($_POST['name']);$c_phone = $conn->real_escape_string($_POST['phone']);
    $c_address =$conn->real_escape_string($_POST['address']);$c_items = $conn->real_escape_string($_POST['items']);
    $c_total = (float)$_POST['total'];

    if (!empty($c_name) && !empty($c_phone) && !empty($c_total)) {$conn->query("INSERT INTO orders (client_name, client_phone, address, items, total) VALUES ('$c_name', '$c_phone', '$c_address', '$c_items',$c_total)");
        $success_msg
