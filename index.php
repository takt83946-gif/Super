<?php
/* ==========================================================
   Ali And Store - Complete Index (Hybrid Payment Mode)
   ========================================================== */
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

// إنشاء جدول الإعلانات إن لم يكن موجوداً
$conn->query("CREATE TABLE IF NOT EXISTS customer_ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    package_type INT DEFAULT 1,
    amount_paid DECIMAL(5,2) DEFAULT 1.00,
    wish_ref VARCHAR(100) DEFAULT 'WPAY',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$toast_message = "";

// معالجة الاعتماد اليدوي السريع عبر الحل الهجين
if (isset($_GET['approve_manual'])) {
    $app_id = intval($_GET['approve_manual']);
    $conn->query("UPDATE customer_ads SET status = 'approved' WHERE id = $app_id");
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// معالجة الضغط على متابعة الدفع (الحل الهجين الموقت)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_wpay') {
    $cust_name = trim($_POST['ad_name'] ?? '');
    $cust_phone = trim($_POST['ad_phone'] ?? '');
    $ad_title = trim($_POST['ad_title'] ?? '');
    $ad_desc = trim($_POST['ad_desc'] ?? '');
    $package_type = intval($_POST['ad_package'] ?? 1);
    $amount_paid = ($package_type === 15) ? 10.00 : 1.00;
    $image_url = trim($_POST['ad_image'] ?? '');

    if (!empty($cust_name) && !empty($cust_phone) && !empty($ad_title)) {
        // 1. حفظ الطلب في قاعدة البيانات بحالة pending
        $stmt = $conn->prepare("INSERT INTO customer_ads (customer_name, phone, title, description, image_url, package_type, amount_paid, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssssid", $cust_name, $cust_phone, $ad_title, $ad_desc, $image_url, $package_type, $amount_paid);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        
        $whatsapp_confirm = "https://wa.me/96181058043?text=" . urlencode("مرحباً، أتممت الدفع بقيمة {$amount_paid}$ للإعلان رقم #{$order_id} بعنوان: {$ad_title} عبر WPAY.");
        
        // 2. فتح رابط الدفع الثابت في تبويب جديد + توجيه الصفحة الحالية لمركز التأكيد
        $static_wpay_link = "https://whish.money/pay/I1QFP1fHd";
        echo "<script>
            window.open('" . $static_wpay_link . "', '_blank');
            window.location.href = 'index.php?payment_sent=1&order_id=$order_id&wa=" . urlencode($whatsapp_confirm) . "';
        </script>";
        exit;
    }
}

$categories_sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''";
$categories_result = $conn->query($categories_sql);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ali And Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-color: #2c3e50; --accent-color: #3498db; --btn-action: #27ae60; }
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; direction: rtl; text-align: right; }
        .announcement-bar { background-color: #e74c3c; color: white; padding: 8px 0; font-size: 14px; font-weight: bold; overflow: hidden; white-space: nowrap; }
        header { background-color: var(--primary-color); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        header h1 { margin: 0; font-size: 24px; }
        .top-nav-bar { background-color: #34495e; color: white; display: flex; justify-content: space-around; align-items: center; padding: 10px 0; position: relative; z-index: 99; flex-wrap: wrap; }
        .top-nav-item { display: flex; flex-direction: column; align-items: center; font-size: 13px; font-weight: 600; color: white; cursor: pointer; text-decoration: none; flex: 1; background: none; border: none; font-family: 'Cairo', sans-serif; }
        .top-nav-item:hover { color: var(--accent-color); }
        .color-popup { display: none; position: absolute; top: 50px; background: white; padding: 12px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); gap: 10px; align-items: center; z-index: 1000; flex-wrap: wrap; max-width: 220px; justify-content: center; }
        .color-circle { width: 26px; height: 26px; border-radius: 50%; cursor: pointer; border: 2px solid #ddd; transition: transform 0.2s; }
        .add-ad-btn { background-color: #e67e22; color: white; border: none; padding: 9px 16px; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: bold; }
        .search-container { max-width: 600px; margin: 20px auto 0 auto; padding: 0 15px; }
        .search-input { width: 100%; padding: 12px 15px; border: 2px solid #ddd; border-radius: 8px; font-family: 'Cairo', sans-serif; font-size: 16px; outline: none; background: white; box-sizing: border-box; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; min-height: 60vh; }
        .category-title { font-size: 22px; color: var(--primary-color); border-bottom: 2px solid var(--accent-color); padding-bottom: 5px; margin: 30px 0 20px; font-weight: 700; }
        .products-grid, .ads-bento-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .ads-bento-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .ad-bento-card { background: linear-gradient(135deg, #ffffff 0%, #f4f6f8 100%); border-radius: 14px; padding: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; }
        .ad-bento-card img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; }
        .ad-badge { background: #d4ac0d; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 20px; align-self: flex-start; margin-bottom: 8px; font-weight: bold; }
        .product-card { background: white; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; display: flex; flex-direction: column; justify-content: space-between; }
        .product-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }
        .product-title { font-size: 17px; font-weight: 600; margin: 10px 0 5px; color: #333; }
        .product-price { color: #27ae60; font-size: 16px; font-weight: bold; margin-bottom: 12px; }
        .btn { background-color: var(--btn-action); color: white; border: none; padding: 9px 15px; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; width: 100%; font-weight: 600; text-decoration: none; display: inline-block; box-sizing: border-box; font-size: 14px; text-align: center; }
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 4% auto; padding: 20px; border-radius: 14px; width: 90%; max-width: 500px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); max-height: 92vh; overflow-y: auto; position: relative; }
        .close-btn { color: #aaa; float: left; font-size: 26px; font-weight: bold; cursor: pointer; }
        .wish-box { background: #e8f8f5; border: 1px dashed #1abc9c; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; color: #16a085; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 3px; color: #555; }
        .form-group input, .form-group textarea { width: 100%; padding: 9px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        #scrollTopBtn { display: none; position: fixed; bottom: 20px; left: 20px; z-index: 99; font-size: 18px; background-color: var(--primary-color); color: white; border: none; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; }
    </style>
</head>
<body>

<?php
if (isset($_GET['payment_sent']) && $_GET['payment_sent'] == '1') {
    $order_id_confirmed = intval($_GET['order_id'] ?? 0);
    $wa_link = $_GET['wa'] ?? '#';
    echo "<div style='background:#d4edda; color:#155724; padding:20px; text-align:center; font-family:Cairo,sans-serif; border-bottom:2px solid #c3e6cb; position:relative; z-index:4000;'>
        <h3 style='margin:0 0 10px 0;'>خطوة أخيرة لتأكيد إعلانك رقم (#{$order_id_confirmed})!</h3>
        <p style='margin:0 0 15px 0;'>تم فتح صفحة الدفع في تبويب جديد. بعد إتمام التحويل، اضغط هنا لتفعيل إعلانك فوراً:</p>
        <a href='{$wa_link}' target='_blank' style='background:#25d366; color:white; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; margin:5px;'>إرسال إيصال واتساب</a>
        <a href='index.php?approve_manual={$order_id_confirmed}' style='background:#27ae60; color:white; padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; margin:5px;'>تفعيل واعتماد الإعلان مباشرة ✔️</a>
    </div>";
}
?>

<div class="announcement-bar"><marquee behavior="scroll" direction="right">🔥 أهلاً بكم في Ali And Store - ادفع بأمان عبر WPAY وانشر إعلانك فوراً! 🔥</marquee></div>

<header>
    <h1>Ali And Store</h1>
    <div><button class="add-ad-btn" onclick="toggleAdModal()">📢 أضف إعلانك</button></div>
</header>

<div class="top-nav-bar">
    <button class="top-nav-item" onclick="toggleMenuModal()"><span class="icon">📋</span> المنيو</button>
    <a href="admin.php" class="top-nav-item" style="color: #f1c40f; text-decoration:none;"><span class="icon">⚙️</span> لوحة التحكم</a>
    <a href="https://wa.me/96181058043" target="_blank" class="top-nav-item" style="color: #2ecc71; text-decoration:none;"><span class="icon">📞</span> التواصل</a>
</div>

<div class="search-container"><input type="text" id="searchInput" class="search-input" placeholder="🔍 ابحث عن أي منتج تريد..." onkeyup="filterProducts()"></div>

<div class="container">
    <div class="category-title">🌟 إعلانات الزباين المميزة</div>
    <div class="ads-bento-grid">
        <?php
        $ads_res = $conn->query("SELECT * FROM customer_ads WHERE status = 'approved' ORDER BY id DESC");
        if ($ads_res && $ads_res->num_rows > 0) {
            while ($ad = $ads_res->fetch_assoc()) {
                echo '<div class="ad-bento-card"><div>';
                echo '<span class="ad-badge">إعلان مدفوع (' . floatval($ad['amount_paid']) . '$)</span>';
                if (!empty($ad['image_url'])) echo '<img src="' . htmlspecialchars($ad['image_url']) . '" alt="إعلان">';
                echo '<h3 style="margin:5px 0; font-size:16px; color:#2c3e50;">' . htmlspecialchars($ad['title']) . '</h3>';
                echo '<p style="font-size:13px; color:#666; margin-bottom:10px;">' . nl2br(htmlspecialchars($ad['description'])) . '</p>';
                echo '</div><div style="border-top:1px solid #eee; padding-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:12px;">';
                echo '<span>👤 ' . htmlspecialchars($ad['customer_name']) . '</span>';
                $clean_phone = preg_replace('/[^0-9]/', '', $ad['phone']);
                echo '<a href="https://wa.me/' . $clean_phone . '" target="_blank" style="background:#25d366; color:white; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:bold;">💬 تواصل</a>';
                echo '</div></div>';
            }
        } else {
            echo '<p style="color:#777; grid-column: 1/-1;">لا توجد إعلانات معتمدة حالياً.</p>';
        }
        ?>
    </div>

    <?php
    if ($categories_result && $categories_result->num_rows > 0) {
        while ($cat_row = $categories_result->fetch_assoc()) {
            $current_category = $cat_row['category'];
            echo '<div class="category-section" id="cat-' . md5($current_category) . '">';
            echo '<div class="category-title">' . htmlspecialchars($current_category) . '</div>';
            echo '<div class="products-grid">';
            $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
            $stmt->bind_param("s", $current_category);
            $stmt->execute();
            $products_result = $stmt->get_result();
            while($product = $products_result->fetch_assoc()) {
                $order_msg = "مرحباً، أود طلب المنتج: " . $product['name'] . " بسعر " . $product['price'] . " ليرة";
                echo '<div class="product-card" data-name="' . htmlspecialchars($product['name'], ENT_QUOTES) . '">';
                echo '<img src="' . (!empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/150') . '" alt="">';
                echo '<div><div class="product-title">' . htmlspecialchars($product['name']) . '</div><div class="product-price">' . htmlspecialchars($product['price']) . ' ليرة</div></div>';
                echo '<a href="https://wa.me/96181058043?text=' . urlencode($order_msg) . '" target="_blank" class="btn">💬 اطلب عبر واتساب</a>';
                echo '</div>';
            }
            $stmt->close();
            echo '</div></div>';
        }
    }
    $conn->close();
    ?>
</div>

<div id="adModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="toggleAdModal()">&times;</span>
        <h2 style="color:var(--primary-color); margin-top:0; font-size: 20px;">📢 إضافة إعلان ودفع عبر WPAY</h2>
        <div class="wish-box">
            <b>💳 باقات الإعلانات الإلكترونية:</b><br>
            <label style="cursor:pointer; display:block; margin:4px 0;"><input type="radio" name="ad_package_choice" value="1" data-price="1.00" checked onchange="updatePrice()"> إعلان واحد (1$)</label>
            <label style="cursor:pointer; display:block; margin:4px 0;"><input type="radio" name="ad_package_choice" value="15" data-price="10.00" onchange="updatePrice()"> باقة 15 إعلاناً (10$)</label>
            <div style="margin-top:6px; border-top:1px dashed #1abc9c; padding-top:4px;">المبلغ الإجمالي: <strong id="adPriceDisplay" style="color:#d35400;">1$</strong></div>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="pay_wpay">
            <input type="hidden" name="ad_package" id="selectedPackageInput" value="1">
            <div class="form-group"><label>اسم المعلن:</label><input type="text" name="ad_name" required></div>
            <div class="form-group"><label>رقم الهاتف (واتساب):</label><input type="text" name="ad_phone" required></div>
            <div class="form-group"><label>عنوان الإعلان:</label><input type="text" name="ad_title" required></div>
            <div class="form-group"><label>وصف الإعلان:</label><textarea name="ad_desc" rows="2"></textarea></div>
            <div class="form-group"><label>رابط الصورة (اختياري):</label><input type="url" name="ad_image"></div>
            <button type="submit" class="btn" style="background:#e74c3c; margin-top:10px;">متابعة الدفع عبر WPAY 🚀</button>
        </form>
    </div>
</div>

<script>
function toggleAdModal() { let m = document.getElementById('adModal'); m.style.display = m.style.display === 'block' ? 'none' : 'block'; }
function toggleMenuModal() { let m = document.getElementById('menuModal'); m.style.display = m.style.display === 'block' ? 'none' : 'block'; }
function updatePrice() {
    let selected = document.querySelector('input[name="ad_package_choice"]:checked');
    if(selected) {
        document.getElementById('adPriceDisplay').innerText = selected.getAttribute('data-price') + '$';
        document.getElementById('selectedPackageInput').value = selected.value;
    }
}
function filterProducts() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => {
        c.style.display = c.getAttribute('data-name').toLowerCase().includes(input) ? 'flex' : 'none';
    });
}
window.onclick = function(e) { if(e.target === document.getElementById('adModal')) document.getElementById('adModal').style.display='none'; }
</script>
</body>
</html>
