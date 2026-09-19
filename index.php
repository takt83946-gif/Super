<?php
/* ==========================================================
   Ali And Store - Complete Index with WPAY Integration
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

// معالجة الانتقال لدفع WPAY عند ضغط الزر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_wpay') {
    $cust_name = trim($_POST['ad_name'] ?? '');
    $cust_phone = trim($_POST['ad_phone'] ?? '');
    $ad_title = trim($_POST['ad_title'] ?? '');
    $ad_desc = trim($_POST['ad_desc'] ?? '');
    $package_type = intval($_POST['ad_package'] ?? 1);
    $amount_paid = ($package_type === 15) ? 10.00 : 1.00;
    $image_url = trim($_POST['ad_image'] ?? '');

    if (!empty($cust_name) && !empty($cust_phone) && !empty($ad_title)) {
        // 1. حفظ الطلب مؤقتاً في القاعدة
        $stmt = $conn->prepare("INSERT INTO customer_ads (customer_name, phone, title, description, image_url, package_type, amount_paid, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssssid", $cust_name, $cust_phone, $ad_title, $ad_desc, $image_url, $package_type, $amount_paid);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // 2. هنا يتم التوجيه الحقيقي لرابط WPAY الخاص بك (استبدل الرابط أدناه برابط بوابة Whish Pay الخاصة بحسابك التجاري)
        // ومرفق معه رابط العودة الصحيح لتجنب الشاشة البيضاء:
        $base_url = "https://" . $_SERVER['HTTP_HOST'];
        $success_return_url = $base_url . "/index.php?payment_success=1&order_id=" . $order_id;
        
        // مثال للتوجيه إلى بوابة Whish (تأكد من وضع الرابط البرمجي الصحيح أو رابط الـ API الخاص بك هنا)
        // إذا كنت تملك رابط الدفع الجاهز، يمكنك توجيه المستخدم إليه مباشرة كالتالي:
        // header("Location: https://whish.money/pay?amount=" . $amount_paid . "&return_url=" . urlencode($success_return_url));
        // exit;
    }
}

// إذا عاد المستخدم من الدفع بنجاح
if (isset($_GET['payment_success']) && $_GET['payment_success'] == '1') {
    $order_id = intval($_GET['order_id'] ?? 0);
    if ($order_id > 0) {
        $conn->query("UPDATE customer_ads SET status = 'approved' WHERE id = $order_id");
        $toast_message = "تم الدفع عبر WPAY بنجاح ونشر إعلانك! 🎉";
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
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --btn-action: #27ae60;
        }
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; direction: rtl; text-align: right; }
        .announcement-bar { background-color: #e74c3c; color: white; padding: 8px 0; font-size: 14px; font-weight: bold; overflow: hidden; white-space: nowrap; }
        header { background-color: var(--primary-color); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        header h1 { margin: 0; font-size: 24px; }
        .top-nav-bar { background-color: #34495e; color: white; display: flex; justify-content: space-around; align-items: center; padding: 10px 0; position: relative; z-index: 99; flex-wrap: wrap; }
        .top-nav-item { display: flex; flex-direction: column; align-items: center; font-size: 13px; font-weight: 600; color: white; cursor: pointer; text-decoration: none; flex: 1; background: none; border: none; font-family: 'Cairo', sans-serif; }
        .top-nav-item:hover { color: var(--accent-color); }
        .color-popup { display: none; position: absolute; top: 50px; background: white; padding: 12px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); gap: 10px; align-items: center; z-index: 1000; flex-wrap: wrap; max-width: 220px; justify-content: center; }
        .color-circle { width: 26px; height: 26px; border-radius: 50%; cursor: pointer; border: 2px solid #ddd; transition: transform 0.2s; }
        .color-circle:hover { transform: scale(1.15); }
        .add-ad-btn { background-color: #e67e22; color: white; border: none; padding: 9px 16px; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: bold; }
        .search-container { max-width: 600px; margin: 20px auto 0 auto; padding: 0 15px; }
        .search-input { width: 100%; padding: 12px 15px; border: 2px solid #ddd; border-radius: 8px; font-family: 'Cairo', sans-serif; font-size: 16px; outline: none; background: white; box-sizing: border-box; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; min-height: 60vh; }
        .category-title { font-size: 22px; color: var(--primary-color); border-bottom: 2px solid var(--accent-color); padding-bottom: 5px; margin: 30px 0 20px; font-weight: 700; }
        .products-grid, .ads-bento-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .ads-bento-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .ad-bento-card { background: linear-gradient(135deg, #ffffff 0%, #f4f6f8 100%); border-radius: 14px; padding: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s; }
        .ad-bento-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .ad-bento-card img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; }
        .ad-badge { background: #d4ac0d; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 20px; align-self: flex-start; margin-bottom: 8px; font-weight: bold; }
        .product-card { background: white; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; display: flex; flex-direction: column; justify-content: space-between; transition: 0.2s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }
        .product-title { font-size: 17px; font-weight: 600; margin: 10px 0 5px; color: #333; }
        .product-price { color: #27ae60; font-size: 16px; font-weight: bold; margin-bottom: 12px; }
        .btn { background-color: var(--btn-action); color: white; border: none; padding: 9px 15px; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; width: 100%; font-weight: 600; text-decoration: none; display: inline-block; box-sizing: border-box; font-size: 14px; }
        .btn:hover { background-color: #219653; }
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 4% auto; padding: 20px; border-radius: 14px; width: 90%; max-width: 500px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); max-height: 92vh; overflow-y: auto; position: relative; }
        .close-btn { color: #aaa; float: left; font-size: 26px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: black; }
        .wish-box { background: #e8f8f5; border: 1px dashed #1abc9c; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; color: #16a085; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 3px; color: #555; }
        .form-group input, .form-group textarea { width: 100%; padding: 9px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        #toast { visibility: hidden; min-width: 250px; background-color: #222; color: #fff; text-align: center; border-radius: 6px; padding: 14px; position: fixed; z-index: 3000; left: 50%; bottom: 30px; transform: translateX(-50%); font-size: 14px; }
        #toast.show { visibility: visible; animation: fadeInOut 3s ease; }
        @keyframes fadeInOut { 0%{opacity:0;bottom:10px;} 15%{opacity:1;bottom:30px;} 85%{opacity:1;bottom:30px;} 100%{opacity:0;bottom:40px;} }
        #scrollTopBtn { display: none; position: fixed; bottom: 20px; left: 20px; z-index: 99; font-size: 18px; background-color: var(--primary-color); color: white; border: none; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="announcement-bar">
    <marquee behavior="scroll" direction="right">🔥 أهلاً بكم في Ali And Store - ادفع بأمان عبر WPAY وانشر إعلانك فوراً! 🔥</marquee>
</div>

<header>
    <h1>Ali And Store</h1>
    <div>
        <button class="add-ad-btn" onclick="toggleAdModal()">📢 أضف إعلانك</button>
    </div>
</header>

<div class="top-nav-bar">
    <button class="top-nav-item" onclick="toggleMenuModal()"><span class="icon">📋</span> المنيو</button>
    <div style="position: relative; display: flex; flex: 1; justify-content: center;">
        <button class="top-nav-item" onclick="toggleColorPopup()"><span class="icon">🎨</span> الألوان</button>
        <div id="colorPopup" class="color-popup">
            <span class="color-circle" style="background-color: #2c3e50;" onclick="changeTheme('#2c3e50', '#3498db')"></span>
            <span class="color-circle" style="background-color: #27ae60;" onclick="changeTheme('#27ae60', '#2ecc71')"></span>
            <span class="color-circle" style="background-color: #8e44ad;" onclick="changeTheme('#8e44ad', '#9b59b6')"></span>
            <span class="color-circle" style="background-color: #d35400;" onclick="changeTheme('#d35400', '#e67e22')"></span>
        </div>
    </div>
    <a href="admin.php" class="top-nav-item" style="color: #f1c40f; text-decoration:none;"><span class="icon">⚙️</span> لوحة التحكم</a>
    <a href="https://wa.me/96181058043" target="_blank" class="top-nav-item" style="color: #2ecc71; text-decoration:none;"><span class="icon">📞</span> التواصل</a>
</div>

<div class="search-container">
    <input type="text" id="searchInput" class="search-input" placeholder="🔍 ابحث عن أي منتج تريد..." onkeyup="filterProducts()">
</div>

<div class="container">
    <div class="category-title">🌟 إعلانات الزباين المميزة</div>
    <div class="ads-bento-grid">
        <?php
        $ads_res = $conn->query("SELECT * FROM customer_ads WHERE status = 'approved' ORDER BY id DESC");
        if ($ads_res && $ads_res->num_rows > 0) {
            while ($ad = $ads_res->fetch_assoc()) {
                echo '<div class="ad-bento-card">';
                echo '<div>';
                echo '<span class="ad-badge">إعلان مدفوع (' . floatval($ad['amount_paid']) . '$)</span>';
                if (!empty($ad['image_url'])) {
                    echo '<img src="' . htmlspecialchars($ad['image_url']) . '" alt="إعلان">';
                }
                echo '<h3 style="margin:5px 0; font-size:16px; color:#2c3e50;">' . htmlspecialchars($ad['title']) . '</h3>';
                echo '<p style="font-size:13px; color:#666; margin-bottom:10px;">' . nl2br(htmlspecialchars($ad['description'])) . '</p>';
                echo '</div>';
                
                echo '<div style="border-top:1px solid #eee; padding-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#555;">';
                echo '<span>👤 ' . htmlspecialchars($ad['customer_name']) . '</span>';
                $clean_phone = preg_replace('/[^0-9]/', '', $ad['phone']);
                $whatsapp_url = "https://wa.me/" . $clean_phone . "?text=" . urlencode("مرحباً، مهتم بإعلانك: " . $ad['title'] . " المنشور في Ali And Store");
                echo '<a href="' . $whatsapp_url . '" target="_blank" style="background:#25d366; color:white; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:13px;">💬 تواصل مع المعلن</a>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p style="color:#777; grid-column: 1/-1;">لا توجد إعلانات معتمدة حالياً. كن أول المعلنين!</p>';
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
                $wa_product_url = "https://wa.me/96181058043?text=" . urlencode($order_msg);
                
                echo '<div class="product-card" data-name="' . htmlspecialchars($product['name'], ENT_QUOTES) . '">';
                echo '<img src="' . (!empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/150') . '" alt="">';
                echo '<div>';
                echo '<div class="product-title">' . htmlspecialchars($product['name']) . '</div>';
                echo '<div class="product-price">' . htmlspecialchars($product['price']) . ' ليرة</div>';
                echo '</div>';
                echo '<a href="' . $wa_product_url . '" target="_blank" class="btn" style="background:#25d366;">💬 اطلب عبر واتساب</a>';
                echo '</div>';
            }
            $stmt->close();
            echo '</div></div>';
        }
    }
    $conn->close();
    ?>
</div>

<button onclick="scrollToTop()" id="scrollTopBtn" title="العودة للأعلى">⬆</button>

<div id="menuModal" class="modal">
    <div class="modal-content" style="max-width: 340px; text-align: center;">
        <span class="close-btn" onclick="toggleMenuModal()">&times;</span>
        <h2 style="margin-top:0; color:var(--primary-color);">أقسام المتجر</h2>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 15px;">
            <?php
            $conn_mb = new mysqli($host, $user, $pass, $dbname, (int)$port);
            $conn_mb->set_charset("utf8");
            $cat_mb_result = $conn_mb->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
            if ($cat_mb_result) {
                while ($cmb = $cat_mb_result->fetch_assoc()) {
                    echo '<a href="#cat-' . md5($cmb['category']) . '" onclick="toggleMenuModal()" style="background:var(--accent-color); color:white; padding:10px; border-radius:6px; text-decoration:none; font-weight:bold;">' . htmlspecialchars($cmb['category']) . '</a>';
                }
            }
            $conn_mb->close();
            ?>
        </div>
    </div>
</div>

<div id="adModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="toggleAdModal()">&times;</span>
        <h2 style="color:var(--primary-color); margin-top:0; font-size: 20px;">📢 إضافة إعلان ودفع عبر WPAY</h2>
        
        <div class="wish-box">
            <b>💳 باقات الإعلانات الإلكترونية:</b><br>
            <label style="cursor:pointer; display:block; margin:4px 0;"><input type="radio" name="ad_package_choice" value="1" data-price="1.00" checked onchange="updatePrice()"> إعلان واحد (1$)</label>
            <label style="cursor:pointer; display:block; margin:4px 0;"><input type="radio" name="ad_package_choice" value="15" data-price="10.00" onchange="updatePrice()"> باقة 15 إعلاناً (10$)</label>
            <div style="margin-top:6px; border-top:1px dashed #1abc9c; padding-top:4px;">
                المبلغ الإجمالي للدفع: <strong id="adPriceDisplay" style="color:#d35400;">1$</strong> عبر بوابة WPAY الآمنة.
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="pay_wpay">
            <input type="hidden" name="ad_package" id="selectedPackageInput" value="1">
            
            <div class="form-group"><label>اسم المعلن:</label><input type="text" name="ad_name" required></div>
            <div class="form-group"><label>رقم الهاتف (واتساب):</label><input type="text" name="ad_phone" placeholder="96170123456" required></div>
            <div class="form-group"><label>عنوان الإعلان:</label><input type="text" name="ad_title" required></div>
            <div class="form-group"><label>وصف الإعلان:</label><textarea name="ad_desc" rows="2"></textarea></div>
            <div class="form-group"><label>رابط الصورة (اختياري):</label><input type="url" name="ad_image" placeholder="https://..."></div>
            
            <button type="submit" class="btn" style="background:#e74c3c; margin-top:10px; width:100%;">متابعة الدفع عبر WPAY 🚀</button>
        </form>
    </div>
</div>

<div id="toast"><?php echo $toast_message; ?></div>

<script>
<?php if($toast_message): ?> showToast("<?php echo $toast_message; ?>"); <?php endif; ?>

function showToast(text) {
    let t = document.getElementById("toast"); t.innerText = text; t.className = "show";
    setTimeout(() => { t.className = ""; }, 3000);
}
function toggleAdModal() { let m = document.getElementById('adModal'); m.style.display = m.style.display === 'block' ? 'none' : 'block'; }
function toggleMenuModal() { let m = document.getElementById('menuModal'); m.style.display = m.style.display === 'block' ? 'none' : 'block'; }
function toggleColorPopup() { let p = document.getElementById('colorPopup'); p.style.display = p.style.display === 'flex' ? 'none' : 'flex'; }
function changeTheme(p, a) { document.documentElement.style.setProperty('--primary-color', p); document.documentElement.style.setProperty('--accent-color', a); document.getElementById('colorPopup').style.display='none'; }

function updatePrice() {
    let selected = document.querySelector('input[name="ad_package_choice"]:checked');
    if(selected) {
        let price = selected.getAttribute('data-price');
        let val = selected.value;
        document.getElementById('adPriceDisplay').innerText = price + '$';
        document.getElementById('selectedPackageInput').value = val;
    }
}

function filterProducts() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => {
        c.style.display = c.getAttribute('data-name').toLowerCase().includes(input) ? 'flex' : 'none';
    });
}
window.onscroll = function() {
    document.getElementById("scrollTopBtn").style.display = (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) ? "block" : "none";
};
function scrollToTop() { window.scrollTo({top: 0, behavior: 'smooth'}); }
window.onclick = function(e) {
    if(e.target === document.getElementById('menuModal')) document.getElementById('menuModal').style.display='none';
    if(e.target === document.getElementById('adModal')) document.getElementById('adModal').style.display='none';
}
</script>
</body>
</html>
