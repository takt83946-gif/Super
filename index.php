<?php
// إعدادات الاتصال بقاعدة البيانات عبر MySQLi (مفعلة دائماً افتراضياً)
$host = getenv('MYSQLHOST') ?: 'localhost';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'elite_boutique';
$port = (int)(getenv('MYSQLPORT') ?: 3306);

// الاتصال بالسيرفر أولاً بدون تحديد قاعدة البيانات لإنشائها إن لم تكن موجودة
$conn = new mysqli($host, $username, $password, "", $port);

if ($conn->connect_error) {
    die("<div style='font-family:Tahoma; color:red; text-align:center; margin-top:50px;'>خطأ في الاتصال بالسيرفر: " . $conn->connect_error . "</div>");
}

$conn->set_charset("utf8mb4");

// إنشاء قاعدة البيانات إن لم تكن موجودة
$conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
$conn->select_db($database);

// إنشاء الجداول إن لم تكن موجودة
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
$res = $conn->query("SELECT COUNT(*) as cnt FROM products");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("INSERT INTO products (name, price, category, image) VALUES 
        ('ساعة رولكس كلاسيكية إصدار خاص', 1250.00, 'ساعات ملكية', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'),
        ('عطر نيش الملكي الفاخر', 450.00, 'عطور نادرة', 'https://images.unsplash.com/photo-1541643600914-78b084683601'),
        ('قلادة ألماس عيار 18', 2450.00, 'مجوهرات وألماس', 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f')");
}
