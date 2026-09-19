<?php
// إعدادات القاعدة
$host = getenv('MYSQLHOST') ?: "localhost";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "supermarket_alsaaha";
$port = getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// إعدادات WPAY API (استبدلها بالبيانات الفعلية التي تستلمها من شركة Whish)
define('WPAY_MERCHANT_ID', 'YOUR_MERCHANT_ID');
define('WPAY_SECRET_KEY', 'YOUR_SECRET_KEY');
define('WPAY_API_URL', 'https://whish.money/itel-service/api/v1/merchant/checkout'); // الرابط الرسمي المعتمد من Whish
