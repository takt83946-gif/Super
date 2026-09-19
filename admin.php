<?php
$host = getenv('MYSQLHOST') ?: "localhost";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "supermarket_alsaaha";
$port = getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $user, $pass, $dbname, (int)$port);
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
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
    $stmt->bind_param("sdss", $name, $price, $cat, $img);
    $stmt->execute(); $stmt->close();
    header("Location: admin.php?msg=product_added"); exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم - Ali And Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f0f2f5; padding: 20px; direction: rtl; text-align: right; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 30px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: right; font-size: 13px; }
        th { background: #2c3e50; color: white; }
        .btn-act { padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer; text-decoration: none; font-size: 12px; display:inline-block; margin-left: 3px;}
        .btn-green { background: #27ae60; } .btn-red { background: #e74c3c; }
        h2, h3 { color: #2c3e50; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 15px; }
        .form-grid input { padding: 9px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Cairo', sans-serif; }
        .btn-add { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>⚙️ لوحة التحكم - إعلانات Wish والمنتجات</h2>
        <a href="index.php" style="color:#3498db; font-weight:bold; text-decoration:none;">الرجوع للمتجر ⬅</a>
    </div>

    <h3>📢 طلبات إعلانات الزباين (بانتظار التحقق من سند Wish)</h3>
    <table>
        <thead>
            <tr>
                <th>المعلن</th>
                <th>الهاتف المدخل</th>
                <th>العنوان</th>
                <th>سند Wish</th>
                <th>الحالة</th>
                <th>الإجراء</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ads = $conn->query("SELECT * FROM customer_ads ORDER BY id DESC");
            if($ads && $ads->num_rows > 0) {
                while($ad = $ads->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>'.htmlspecialchars($ad['customer_name']).'</td>';
                    echo '<td>'.htmlspecialchars($ad['phone']).'</td>';
                    echo '<td>'.htmlspecialchars($ad['title']).'</td>';
                    echo '<td><b>'.htmlspecialchars($ad['wish_ref']).'</b></td>';
                    echo '<td><span style="background:'.($ad['status']=='approved'?'#d4edda':'#fff3cd').'; color:'.($ad['status']=='approved'?'#155724':'#856404').'; padding:3px 8px; border-radius:4px; font-weight:bold;">'.$ad['status'].'</span></td>';
                    echo '<td>';
                    if($ad['status'] !== 'approved') {
                        echo '<a href="admin.php?approve_ad='.$ad['id'].'" class="btn-act btn-green">اعتماد ✔️</a> ';
                    }
                    echo '<a href="admin.php?delete_ad='.$ad['id'].'" class="btn-act btn-red" onclick="return confirm(\'متأكد من الحذف؟\')">حذف ❌</a>';
                    echo '</td>';
                    echo '</tr>';
                }
            } else {
                echo '<tr><td colspan="6" style="text-align:center; color:#777;">لا توجد طلبات إعلانات حالياً</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <hr style="border:0; border-top:1px solid #eee; margin:25px 0;">

    <h3>➕ إضافة منتج جديد للمتجر</h3>
    <form method="POST">
        <input type="hidden" name="add_product" value="1">
        <div class="form-grid">
            <input type="text" name="p_name" placeholder="اسم المنتج" required>
            <input type="number" name="p_price" placeholder="السعر" required>
            <input type="text" name="p_cat" placeholder="التصنيف (مثال: خضار، مشروبات)" required>
            <input type="url" name="p_img" placeholder="رابط الصورة (اختياري)">
        </div>
        <button type="submit" class="btn-add">إضافة المنتج</button>
    </form>
</div>
</body>
</html>
