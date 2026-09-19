<?php
/* ==========================================================
   1. الاتصال بقاعدة البيانات ومعالجة إرسال الإعلانات (سند Wish اليدوي)
   ========================================================== */
$host = getenv('MYSQLHOST') ?: "localhost";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "supermarket_alsaaha";
$port = getenv('MYSQLPORT') ?: 3306;

$conn = new mysqli($host, $user,$pass, $dbname, (int)$port);
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
    wish_ref VARCHAR(100) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$toast_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) &&$_POST['action'] === 'submit_ad') {
    $cust_name = trim($_POST['ad_name'] ?? '');
    $cust_phone = trim($_POST['ad_phone'] ?? '');
    $ad_title = trim($_POST['ad_title'] ?? '');
    $ad_desc = trim($_POST['ad_desc'] ?? '');
    $wish_ref = trim($_POST['wish_ref'] ?? '');
    $image_url = trim($_POST['ad_image'] ?? '');

    if ($cust_name &&$cust_phone && $ad_title &&$wish_ref) {
        $stmt =$conn->prepare("INSERT INTO customer_ads (customer_name, phone, title, description, image_url, wish_ref, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssss", $cust_name,$cust_phone, $ad_title,$ad_desc, $image_url,$wish_ref);
        if ($stmt->execute()) {$toast_message = "تم إرسال إعلانك بنجاح! سيتم نشره بعد التحقق من سند الـ Wish ✅";
        }
        $stmt->close();
    } else {
        $toast_message = "الرجاء تعبئة الحقول الأساسية ورقم سند Wish!";
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
            --btn-cart: #27ae60;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            margin: 0; padding: 0; direction: rtl; text-align: right;
            transition: background-color 0.3s;
        }
        .announcement-bar {
            background-color: #e74c3c; color: white; padding: 8px 0; font-size: 14px; font-weight: bold; overflow: hidden; white-space: nowrap;
        }
        header {
            background-color: var(--primary-color); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        header h1 { margin: 0; font-size: 24px; }
        .top-nav-bar {
            background-color: #34495e; color: white; display: flex; justify-content: space-around; align-items: center; padding: 10px 0; position: relative; z-index: 99; flex-wrap: wrap;
        }
        .top-nav-item {
            display: flex; flex-direction: column; align-items: center; font-size: 13px; font-weight: 600; color: white; cursor: pointer; text-decoration: none; flex: 1; background: none; border: none; font-family: 'Cairo', sans-serif;
        }
        .top-nav-item:hover { color: var(--accent-color); }
        .color-popup {
            display: none; position: absolute; top: 50px; background: white; padding: 12px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); gap: 10px; align-items: center; z-index: 1000; flex-wrap: wrap; max-width: 220px; justify-content: center;
        }
        .color-circle { width: 26px; height: 26px; border-radius: 50%; cursor: pointer; border: 2px solid #ddd; transition: transform 0.2s; }
        .color-circle:hover { transform: scale(1.15); }
        .cart-icon-btn, .add-ad-btn {
            background-color: var(--btn-cart); color: white; border: none; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: bold; margin-left: 5px;
        }
        .add-ad-btn { background-color: #e67e22; }
        .search-container { max-width: 600px; margin: 20px auto 0 auto; padding: 0 15px; }
        .search-input { width: 100%; padding: 12px 15px; border: 2px solid #ddd; border-radius: 8px; font-family: 'Cairo', sans-serif; font-size: 16px; outline: none; background: white; box-sizing: border-box; }
        .container { max-width: 1200px; margin: 20px auto; padding: 0 15px; min-height: 60vh; }
        
        /* Bento Grid للأصول والإعلانات الخرافية */
        .category-title { font-size: 22px; color: var(--primary-color); border-bottom: 2px solid var(--accent-color); padding-bottom: 5px; margin: 30px 0 20px; font-weight: 700; }
        .products-grid, .ads-bento-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        
        .ads-bento-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .ad-bento-card {
            background: linear-gradient(135deg, #ffffff 0%, #f4f6f8 100%);
            border-radius: 14px; padding: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s;
        }
        .ad-bento-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .ad-bento-card img { width: 100%; height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; }
        .ad-badge { background: #d4ac0d; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 20px; align-self: flex-start; margin-bottom: 8px; font-weight: bold; }
        
        .product-card { background: white; border-radius: 10px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; display: flex; flex-direction: column; justify-content: space-between; transition: 0.2s; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .product-card img { width: 100%; height: 140px; object-fit: cover; border-radius: 6px; }
        .product-title { font-size: 17px; font-weight: 600; margin: 10px 0 5px; color: #333; }
        .product-price { color: #27ae60; font-size: 16px; font-weight: bold; margin-bottom: 12px; }
        .btn { background-color: var(--accent-color); color: white; border: none; padding: 9px 15px; border-radius: 6px; cursor: pointer; font-family: 'Cairo', sans-serif; width: 100%; font-weight: 600; }
        
        /* Modals */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: white; margin: 4% auto; padding: 25px; border-radius: 14px; width: 90%; max-width: 500px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); max-height: 85vh; overflow-y: auto; position: relative; }
        .close-btn { color: #aaa; float: left; font-size: 26px; font-weight: bold; cursor: pointer; }
        .close-btn:hover { color: black; }
        .wish-box { background: #e8f8f5; border: 1px dashed #1abc9c; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; color: #16a085; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #555; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Cairo', sans-serif; box-sizing: border-box; }
        
        #toast { visibility: hidden; min-width: 250px; background-color: #222; color: #fff; text-align: center; border-radius: 6px; padding: 14px; position: fixed; z-index: 3000; left: 50%; bottom: 30px; transform: translateX(-50%); font-size: 14px; }
        #toast.show { visibility: visible; animation: fadeInOut 3s ease; }
        @keyframes fadeInOut { 0%{opacity:0;bottom:10px;} 15%{opacity:1;bottom:30px;} 85%{opacity:1;bottom:30px;} 100%{opacity:0;bottom:40px;} }

        /* تنسيق عناصر السلة وأزرار التحكم بالكمية */
        .cart-items-list { list-style: none; padding: 0; margin: 15px 0; max-height: 220px; overflow-y: auto; }
        .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f1f1; gap: 10px; }
        .cart-item-info { flex: 1; }
        .cart-item-name { font-weight: 600; font-size: 14px; color: #333; }
        .cart-item-price { color: #27ae60; font-size: 13px; font-weight: bold; }
        .quantity-controls { display: flex; align-items: center; gap: 5px; background: #f1f2f6; padding: 2px 6px; border-radius: 6px; }
        .qty-btn { background-color: white; color: #333; border: 1px solid #ddd; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; font-weight: bold; font-family: 'Cairo', sans-serif; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .qty-val { font-weight: bold; min-width: 20px; text-align: center; font-size: 14px; }
        .remove-item-btn { background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 16px; padding: 4px; }
        
        .customer-form { margin-top: 15px; border-top: 2px solid #f1f1f1; padding-top: 15px; }
        .customer-form h3 { margin-bottom: 10px; font-size: 16px; color: var(--primary-color); }
        .whatsapp-checkout-btn { background-color: #25d366; color: white; border: none; padding: 11px; border-radius: 6px; width: 100%; font-family: 'Cairo', sans-serif; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .whatsapp-checkout-btn:hover { background-color: #1ebe5d; }
        .save-invoice-btn { background-color: #3498db; color: white; border: none; padding: 9px; border-radius: 6px; width: 100%; font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: bold; cursor: pointer; margin-top: 8px; }
        .clear-cart-btn { background-color: #ffeaa7; color: #d35400; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-family: 'Cairo', sans-serif; font-size: 12px; font-weight: bold; }
        .total-price { font-weight: bold; font-size: 17px; color: #2c3e50; }
        #scrollTopBtn { display: none; position: fixed; bottom: 20px; left: 20px; z-index: 99; font-size: 18px; background-color: var(--primary-color); color: white; border: none; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
    </style>
</head>
<body>

<div class="announcement-bar">
    <marquee behavior="scroll" direction="right">🔥 أهلاً بكم في Ali And Store - تسوق الآن أو أضف إعلانك المميز وادفعه عبر Wish Money! 🔥</marquee>
</div>

<header>
    <h1>Ali And Store</h1>
    <div>
        <button class="add-ad-btn" onclick="toggleAdModal()">📢 أضف إعلانك</button>
        <button class="cart-icon-btn" onclick="toggleCartModal()">🛒 السلة (<span id="cart-count">0</span>)</button>
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
            <span class="color-circle" style="background-color: #c0392b;" onclick="changeTheme('#c0392b', '#e74c3c')"></span>
            <span class="color-circle" style="background-color: #16a085;" onclick="changeTheme('#16a085', '#1abc9c')"></span>
            <span class="color-circle" style="background-color: #d4ac0d;" onclick="changeTheme('#d4ac0d', '#f1c40f')"></span>
            <span class="color-circle" style="background-color: #34495e;" onclick="changeTheme('#34495e', '#7f8c8d')"></span>
        </div>
    </div>
    <a href="admin.php" class="top-nav-item" style="color: #f1c40f; text-decoration:none;"><span class="icon">⚙️</span> لوحة التحكم</a>
    <a href="https://wa.me/96181058043" target="_blank" class="top-nav-item" style="color: #2ecc71; text-decoration:none;"><span class="icon">📞</span> التواصل</a>
</div>

<div class="search-container">
    <input type="text" id="searchInput" class="search-input" placeholder="🔍 ابحث عن أي منتج تريد..." onkeyup="filterProducts()">
</div>

<div class="container">
    <!-- شبكة إعلانات الزباين (Bento Grid) المعتمدة مع التواصل المباشر دون عرض الرقم -->
    <div class="category-title">🌟 إعلانات الزباين المميزة</div>
    <div class="ads-bento-grid">
        <?php
        $ads_res =$conn->query("SELECT * FROM customer_ads WHERE status = 'approved' ORDER BY id DESC");
        if ($ads_res &&$ads_res->num_rows > 0) {
            while ($ad =$ads_res->fetch_assoc()) {
                echo '<div class="ad-bento-card">';
                echo '<div>';
                echo '<span class="ad-badge">إعلان معتمد</span>';
                if (!empty($ad['image_url'])) {
                    echo '<img src="' . htmlspecialchars($ad['image_url']) . '" alt="إعلان">';
                }
                echo '<h3 style="margin:5px 0; font-size:16px; color:#2c3e50;">' . htmlspecialchars($ad['title']) . '</h3>';
                echo '<p style="font-size:13px; color:#666; margin-bottom:10px;">' . nl2br(htmlspecialchars($ad['description'])) . '</p>';
                echo '</div>';
                
                echo '<div style="border-top:1px solid #eee; padding-top:10px; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#555;">';
                echo '<span>👤 ' . htmlspecialchars($ad['customer_name']) . '</span>';
                
                // تنظيف الرقم للتوجيه واتساب دون عرضه نصاً على الكرت
                $clean_phone = preg_replace('/[^0-9]/', '', $ad['phone']);$whatsapp_url = "https://wa.me/" . $clean_phone . "?text=" . urlencode("مرحباً، مهتم بإعلانك: " . $ad['title'] . " المنشور في Ali And Store");
                
                echo '<a href="' . $whatsapp_url . '" target="_blank" style="background:#25d366; color:white; padding:6px 14px; border-radius:6px; text-decoration:none; font-weight:bold; font-size:13px;">💬 تواصل مع المعلن</a>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p style="color:#777; grid-column: 1/-1;">لا توجد إعلانات معتمدة حالياً. كن أول المعلنين!</p>';
        }
        ?>
    </div>

    <!-- أقسام المنتجات الأساسية -->
    <?php
    if ($categories_result &&$categories_result->num_rows > 0) {
        while ($cat_row =$categories_result->fetch_assoc()) {
            $current_category =$cat_row['category'];
            echo '<div class="category-section" id="cat-' . md5($current_category) . '">';
            echo '<div class="category-title">' . htmlspecialchars($current_category) . '</div>';
            echo '<div class="products-grid">';

            $stmt =$conn->prepare("SELECT * FROM products WHERE category = ?");
            $stmt->bind_param("s", $current_category);$stmt->execute();
            $products_result =$stmt->get_result();

            while($product =$products_result->fetch_assoc()) {
                echo '<div class="product-card" data-name="' . htmlspecialchars($product['name'], ENT_QUOTES) . '">';
                echo '<img src="' . (!empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/150') . '" alt="">';
                echo '<div>';
                echo '<div class="product-title">' . htmlspecialchars($product['name']) . '</div>';
                echo '<div class="product-price">' . htmlspecialchars($product['price']) . ' ليرة</div>';
                echo '</div>';
                echo '<button class="btn" onclick="addToCart(\'' . htmlspecialchars($product['name'], ENT_QUOTES) . '\', ' .$product['price'] . ')">إضافة إلى السلة</button>';
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

<!-- نافذة المنيو المنبثقة -->
<div id="menuModal" class="modal">
    <div class="modal-content" style="max-width: 340px; text-align: center;">
        <span class="close-btn" onclick="toggleMenuModal()">&times;</span>
        <h2 style="margin-top:0; color:var(--primary-color);">أقسام المتجر</h2>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 15px;">
            <?php
            $conn_mb = new mysqli($host,$user, $pass,$dbname, (int)$port);$conn_mb->set_charset("utf8");
            $cat_mb_result =$conn_mb->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
            if ($cat_mb_result) {
                while ($cmb =$cat_mb_result->fetch_assoc()) {
                    echo '<a href="#cat-' . md5($cmb['category']) . '" onclick="toggleMenuModal()" style="background:var(--accent-color); color:white; padding:10px; border-radius:6px; text-decoration:none; font-weight:bold;">' . htmlspecialchars($cmb['category']) . '</a>';
                }
            }
            $conn_mb->close();
            ?>
        </div>
    </div>
</div>

<!-- Modal إضافة إعلان مع Wish Money (يدوي) -->
<div id="adModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="toggleAdModal()">&times;</span>
        <h2 style="color:var(--primary-color); margin-top:0;">📢 إضافة إعلان جديد</h2>
        <div class="wish-box">
            <b>💳 طريقة الدفع عبر Wish Money:</b><br>
            حوّل رسم الإعلان إلى رقم Wish: <b>03-000000 (Ali Store)</b>، ثم أدخل رقم سند الحوالة أدناه ليتم اعتماد إعلانك.
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="submit_ad">
            <div class="form-group"><label>اسم المعلن:</label><input type="text" name="ad_name" required></div>
            <div class="form-group"><label>رقم الهاتف (للتواصل معي):</label><input type="text" name="ad_phone" placeholder="مثال: 96170123456" required></div>
            <div class="form-group"><label>عنوان الإعلان:</label><input type="text" name="ad_title" required></div>
            <div class="form-group"><label>وصف الإعلان:</label><textarea name="ad_desc" rows="3"></textarea></div>
            <div class="form-group"><label>رابط الصورة (اختياري):</label><input type="url" name="ad_image" placeholder="https://..."></div>
            <div class="form-group"><label>رقم سند تحويل Wish:</label><input type="text" name="wish_ref" required></div>
            <button type="submit" class="btn" style="background:#27ae60; margin-top:10px;">إرسال الإعلان للمراجعة</button>
        </form>
    </div>
</div>

<!-- Modal سلة المشتريات ومعلومات الزبون -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="toggleCartModal()">&times;</span>
        <h2 style="margin-top:0; color:var(--primary-color);">🛒 سلة المشتريات</h2>
        <ul id="cart-items" class="cart-items-list"><p style="text-align:center; color:#777;">السلة فارغة حالياً.</p></ul>
        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 10px;">
            <button class="clear-cart-btn" onclick="clearCart()">🗑️ تفريغ السلة</button>
            <div class="total-price">المجموع: <span id="cart-total">0</span> ليرة</div>
        </div>
        <div class="customer-form">
            <h3>📝 معلومات التوصيل:</h3>
            <div class="form-group"><label>اسم الزبون:</label><input type="text" id="custName" oninput="saveCustomerData()"></div>
            <div class="form-group"><label>رقم الهاتف:</label><input type="text" id="custPhone" oninput="saveCustomerData()"></div>
            <div class="form-group"><label>العنوان:</label><textarea id="custAddress" rows="2" oninput="saveCustomerData()"></textarea></div>
        </div>
        <button class="whatsapp-checkout-btn" onclick="sendToWhatsApp()">إرسال الطلب عبر واتساب 📱</button>
        <button class="save-invoice-btn" onclick="saveInvoice()">📥 حفظ / طباعة الفاتورة</button>
    </div>
</div>

<div id="toast"><?php echo $toast_message; ?></div>

<script>
let cart = JSON.parse(localStorage.getItem('ali_store_cart')) || [];
loadCustomerData();
updateCartUI();

<?php if($toast_message): ?> showToast("<?php echo $toast_message; ?>"); <?php endif; ?>

function showToast(text) {
    let t = document.getElementById("toast"); t.innerText = text; t.className = "show";
    setTimeout(() => { t.className = ""; }, 3000);
}
function toggleAdModal() { let m = document.getElementById('adModal'); m.style.display = m.style.display === 'block' ? 'none' : 'block'; }
function toggleCartModal() { let m = document.getElementById('cartModal'); m.style.display = m.style.display === 'block' ? 'none' : 'block'; }
function toggleMenuModal() { let m = document.getElementById('menuModal'); m.style.display = m.style.display === 'block' ? 'none' : 'block'; }
function toggleColorPopup() { let p = document.getElementById('colorPopup'); p.style.display = p.style.display === 'flex' ? 'none' : 'flex'; }
function changeTheme(p, a) { document.documentElement.style.setProperty('--primary-color', p); document.documentElement.style.setProperty('--accent-color', a); document.getElementById('colorPopup').style.display='none'; }

function addToCart(name, price) {
    let item = cart.find(i => i.name === name);
    if(item) item.quantity++; else cart.push({name, price, quantity:1});
    saveAndupdateCart(); showToast("تمت الإضافة للسلة ✅");
}
function increaseQty(index) { cart[index].quantity++; saveAndupdateCart(); }
function decreaseQty(index) { if(cart[index].quantity > 1) cart[index].quantity--; else cart.splice(index, 1); saveAndupdateCart(); }
function removeFromCart(index) { cart.splice(index, 1); saveAndupdateCart(); }
function clearCart() { if(confirm("تفريغ السلة؟")) { cart = []; saveAndupdateCart(); } }
function saveAndupdateCart() { localStorage.setItem('ali_store_cart', JSON.stringify(cart)); updateCartUI(); }

function updateCartUI() {
    let cnt = document.getElementById('cart-count'), list = document.getElementById('cart-items'), tot = document.getElementById('cart-total');
    let totalC = 0, totalP = 0; list.innerHTML = '';
    if(cart.length === 0) { list.innerHTML = '<p style="text-align:center; color:#777;">السلة فارغة حالياً.</p>'; }
    else {
        cart.forEach((item, idx) => {
            totalC += item.quantity; totalP += item.price * item.quantity;
            list.innerHTML += `<li class="cart-item">
                <div class="cart-item-info"><div class="cart-item-name">${item.name}</div><div class="cart-item-price">${item.price * item.quantity} ليرة</div></div>
                <div class="quantity-controls"><button class="qty-btn" onclick="decreaseQty(${idx})">-</button><span class="qty-val">${item.quantity}</span><button class="qty-btn" onclick="increaseQty(${idx})">+</button></div>
                <button class="remove-item-btn" onclick="removeFromCart(${idx})">❌</button>
            </li>`;
        });
    }
    cnt.innerText = totalC; tot.innerText = totalP;
}

function saveCustomerData() {
    localStorage.setItem('ali_store_customer', JSON.stringify({
        name: document.getElementById('custName').value,
        phone: document.getElementById('custPhone').value,
        address: document.getElementById('custAddress').value
    }));
}
function loadCustomerData() {
    let s = JSON.parse(localStorage.getItem('ali_store_customer'));
    if(s) {
        if(document.getElementById('custName')) document.getElementById('custName').value = s.name || '';
        if(document.getElementById('custPhone')) document.getElementById('custPhone').value = s.phone || '';
        if(document.getElementById('custAddress')) document.getElementById('custAddress').value = s.address || '';
    }
}
function filterProducts() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(c => {
        c.style.display = c.getAttribute('data-name').toLowerCase().includes(input) ? 'flex' : 'none';
    });
}
function sendToWhatsApp() {
    if(cart.length===0) return alert("السلة فارغة!");
    let name = document.getElementById('custName').value.trim(), phone = document.getElementById('custPhone').value.trim(), address = document.getElementById('custAddress').value.trim();
    if(!name || !phone || !address) return alert("الرجاء تعبئة بيانات التوصيل!");
    let msg = `🛒 *طلب جديد من Ali And Store*\n👤 ${name}\n📞 ${phone}\n📍 ${address}\n\n🛍️ *المنتجات:*\n` + cart.map(i => `- ${i.name} (x${i.quantity}) - ${i.price*i.quantity} ليرة`).join('\n');
    let totalP = cart.reduce((acc, i) => acc + (i.price * i.quantity), 0);
    msg += `\n\n💰 *المجموع:* ${totalP} ليرة`;
    window.open(`https://wa.me/96181058043?text=` + encodeURIComponent(msg), '_blank');
}
function saveInvoice() {
    if(cart.length===0) return alert("السلة فارغة!");
    window.print();
}
window.onscroll = function() {
    document.getElementById("scrollTopBtn").style.display = (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) ? "block" : "none";
};
function scrollToTop() { window.scrollTo({top: 0, behavior: 'smooth'}); }
window.onclick = function(e) {
    if(e.target === document.getElementById('cartModal')) document.getElementById('cartModal').style.display='none';
    if(e.target === document.getElementById('menuModal')) document.getElementById('menuModal').style.display='none';
    if(e.target === document.getElementById('adModal')) document.getElementById('adModal').style.display='none';
}
</script>
</body>
</html>
