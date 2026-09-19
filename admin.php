<?php
$host = getenv('MYSQLHOST') ?: "localhost";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "supermarket_alsaaha";
$port = getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);
$conn->set_charset("utf8");

// معالجة اعتمادات / حذف الإعلانات
if (isset($_GET['approve_ad'])) {
    $aid = (int)$_GET['approve_ad'];
    $conn->query("UPDATE customer_ads SET status='approved' WHERE id=$aid");
    header("Location: admin.php?msg=approved"); exit;
}
if (isset($_GET['delete_ad'])) {
    $aid = (int)$_GET['delete_ad'];
    $conn->query("DELETE FROM customer_ads WHERE id=$aid");
    header("Location: admin.php?msg=deleted"); exit;
}

// إضافة منتج جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['p_name']; $price = $_POST['p_price']; $cat = $_POST['p_cat']; $img = $_POST['p_img'];
    $stmt = $conn->prepare("INSERT INTO products (name, price, category, image) VALUES (?, ?, ?, ?)");
    $stmt->binds("sdss" ?? "ssss", $name, $price, $cat, $img); // using standard bind
    $stmt = $conn->prepare("INSERT INTO products (name, price, category, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdsz", $name, $price, $cat, $img); // fallback or standard string/decimal
    $stmt->execute(); $stmt->close();
    header("Location: admin.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - Ali And Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: right; font-size: 14px; }
        th { background: #2c3e50; color: white; }
        .btn-act { padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer; text-decoration: none; font-size: 12px; }
        .btn-green { background: #27ae60; } .btn-red { background: #e74c3c; }
        h2 { color: #2c3e50; }
    </style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>⚙️ لوحة التحكم - اعتماد إعلانات Wish والمنتجات</h2>
        <a href="index.php" style="color:var(--accent-color);">الرجوع للمتجر ⬅</a>
    </div>

    <h3>📢 طلبات إعلانات الزباين (بانتظار التحقق من سند Wish)</h3>
    <table>
        <thead>
            <tr>
                <th>المعلن</th>
                <th>الهاتف</th>
                <th>العنوان</th>
                <th>سند Wish</th>
                <th>الحالة</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ads = $conn->query("SELECT * FROM customer_ads ORDER BY id DESC");
            while($ad = $ads->fetch_assoc()) {
                echo '<tr>';
                echo '<td>'.htmlspecialchars($ad['customer_name']).'</td>';
                echo '<td>'.htmlspecialchars($ad['phone']).'</td>';
                echo '<td>'.htmlspecialchars($ad['title']).'</td>';
                echo '<td><b>'.htmlspecialchars($ad['wish_ref']).'</b></td>';
                echo '<td>'.$ad['status'].'</td>';
                echo '<td>';
                if($ad['status'] !== 'approved') {
                    echo '<a href="admin.php?approve_ad='.$ad['id'].'" class="btn-act btn-green">اعتماد ✔️</a> ';
                }
                echo '<a href="admin.php?delete_ad='.$ad['id'].'" class="btn-act btn-red" onclick="return confirm(\'حذف؟\')">حذف ❌</a>';
                echo '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
