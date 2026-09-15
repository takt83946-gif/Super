<?php
/* ==========================================================
   1. قسم الاتصال بقاعدة البيانات وإعدادات السيرفر
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

/* ==========================================================
   2. قسم جلب التصنيفات من قاعدة البيانات
   ========================================================== */
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
        /* ==========================================================
           3. قسم تنسيقات التصميم (CSS Styles)
           ========================================================== */
        :root {
            --primary-color: #2c3e50;
            --accent-color: #3498db;
            --btn-cart: #27ae60;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            direction: rtl;
            text-align: right;
            transition: background-color 0.3s;
        }

        /* شريط الإعلانات المتحرك العلوي */
        .announcement-bar {
            background-color: #e74c3c;
            color: white;
            padding: 8px 0;
            font-size: 14px;
            font-weight: bold;
            overflow: hidden;
            white-space: nowrap;
        }
        .announcement-bar marquee {
            width: 100%;
        }

        /* الهيدر الرئيسي */
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            transition: background-color 0.3s;
        }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        /* شريط التنقل العلوي (Top Bar) */
        .top-nav-bar {
            background-color: #34495e;
            color: white;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
            z-index: 99;
            flex-wrap: wrap;
        }
        .top-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 13px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            text-decoration: none;
            flex: 1;
            text-align: center;
            background: none;
            border: none;
            font-family: 'Cairo', sans-serif;
        }
        .top-nav-item span.icon {
            font-size: 16px;
            margin-bottom: 2px;
        }
        .top-nav-item:hover {
            color: var(--accent-color);
        }

        /* قائمة اختيار الألوان المنبثقة */
        .color-popup {
            display: none;
            position: absolute;
            top: 50px;
            background: white;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            gap: 10px;
            align-items: center;
            z-index: 1000;
            flex-wrap: wrap;
            max-width: 220px;
            justify-content: center;
        }
        .color-circle {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #ddd;
            transition: transform 0.2s;
        }
        .color-circle:hover {
            transform: scale(1.15);
        }

        .cart-icon-btn {
            background-color: var(--btn-cart);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .cart-icon-btn:hover {
            opacity: 0.9;
        }

        /* شريط البحث الفوري */
        .search-container {
            max-width: 600px;
            margin: 20px auto 0 auto;
            padding: 0 15px;
        }
        .search-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        .search-input:focus {
            border-color: var(--accent-color);
        }

        /* حاوية المنتجات والأقسام */
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            min-height: 60vh;
        }
        .category-section {
            margin-bottom: 35px;
        }
        .category-title {
            font-size: 22px;
            color: var(--primary-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-3px);
        }
        .product-card img {
            max-width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 5px;
        }
        .product-title {
            font-size: 17px;
            font-weight: 600;
            margin: 10px 0 5px;
        }
        .product-price {
            color: #27ae60;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            width: 100%;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn:hover {
            opacity: 0.9;
        }
        
        /* النوافذ المنبثقة (Modals) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 6% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            position: relative;
            max-height: 85vh;
            overflow-y: auto;
        }
        .close-btn {
            color: #aaa;
            float: left;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-btn:hover {
            color: black;
        }
        .cart-items-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
            max-height: 180px;
            overflow-y: auto;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        /* نموذج بيانات الزبون */
        .customer-form {
            margin-top: 15px;
            border-top: 2px solid #eee;
            padding-top: 15px;
        }
        .customer-form h3 {
            margin-bottom: 10px;
            font-size: 17px;
            color: var(--primary-color);
        }
        .form-group {
            margin-bottom: 10px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 9px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Cairo', sans-serif;
            box-sizing: border-box;
            outline: none;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--accent-color);
        }

        .whatsapp-checkout-btn {
            background-color: #25d366;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .whatsapp-checkout-btn:hover {
            background-color: #1ebe5d;
        }
        .clear-cart-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            font-size: 13px;
            margin-top: 5px;
        }
        .total-price {
            font-weight: bold;
            font-size: 18px;
            margin: 15px 0;
            text-align: left;
        }

        /* رسالة التنبيه المنبثقة المؤقتة (Toast) */
        #toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 12px;
            position: fixed;
            z-index: 2000;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            font-size: 15px;
        }
        #toast.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2s;
        }
        @keyframes fadein {from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;}}
        @keyframes fadeout {from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;}}

        /* زر العودة للأعلى */
        #scrollTopBtn {
            display: none;
            position: fixed;
            bottom: 20px;
            left: 20px;
            z-index: 99;
            font-size: 18px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            outline: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            transition: background-color 0.3s;
        }
        #scrollTopBtn:hover {
            background-color: var(--accent-color);
        }
    </style>
</head>
<body>

<!-- ==========================================================
   4. هيكل واجهة المستخدم (HTML Layout)
   ========================================================== -->

<!-- شريط الإعلانات -->
<div class="announcement-bar">
    <marquee behavior="scroll" direction="right">🔥 أهلاً بكم في Ali And Store - تسوق الآن بكل سهولة وأرسل طلبك عبر الواتساب 🔥</marquee>
</div>

<!-- الهيدر -->
<header>
    <h1>Ali And Store</h1>
    <button class="cart-icon-btn" onclick="toggleCartModal()">🛒 سلة المشتريات (<span id="cart-count">0</span>)</button>
</header>

<!-- شريط التنقل العلوي -->
<div class="top-nav-bar">
    <button class="top-nav-item" onclick="toggleMenuModal()">
        <span class="icon">📋</span> المنيو
    </button>

    <div style="position: relative; display: flex; flex: 1; justify-content: center;">
        <button class="top-nav-item" onclick="toggleColorPopup()">
            <span class="icon">🎨</span> الألوان
        </button>
        <div id="colorPopup" class="color-popup">
            <span class="color-circle" style="background-color: #2c3e50;" onclick="changeTheme('#2c3e50', '#3498db')" title="كلاسيكي"></span>
            <span class="color-circle" style="background-color: #27ae60;" onclick="changeTheme('#27ae60', '#2ecc71')" title="أخضر"></span>
            <span class="color-circle" style="background-color: #8e44ad;" onclick="changeTheme('#8e44ad', '#9b59b6')" title="بنفسجي"></span>
            <span class="color-circle" style="background-color: #d35400;" onclick="changeTheme('#d35400', '#e67e22')" title="برتقالي"></span>
            <span class="color-circle" style="background-color: #c0392b;" onclick="changeTheme('#c0392b', '#e74c3c')" title="أحمر"></span>
            <span class="color-circle" style="background-color: #16a085;" onclick="changeTheme('#16a085', '#1abc9c')" title="تركواز"></span>
            <span class="color-circle" style="background-color: #d4ac0d;" onclick="changeTheme('#d4ac0d', '#f1c40f')" title="ذهبي"></span>
            <span class="color-circle" style="background-color: #34495e;" onclick="changeTheme('#34495e', '#7f8c8d')" title="رمادي"></span>
        </div>
    </div>

    <button class="top-nav-item" onclick="showToast('Ali And Store: متجرك المفضل لتلبية احتياجاتك بأفضل الأسعار.');">
        <span class="icon">ℹ️</span> عن المتجر
    </button>

    <a href="https://wa.me/96181058043" target="_blank" class="top-nav-item" style="color: #2ecc71; text-decoration:none;">
        <span class="icon">📞</span> التواصل
    </a>
</div>

<!-- شريط البحث الفوري -->
<div class="search-container">
    <input type="text" id="searchInput" class="search-input" placeholder="🔍 ابحث عن أي منتج تريد..." onkeyup="filterProducts()">
</div>

<!-- عرض الأقسام والمنتجات ديناميكياً من قاعدة البيانات -->
<div class="container">
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

            if ($products_result && $products_result->num_rows > 0) {
                while($product = $products_result->fetch_assoc()) {
                    echo '<div class="product-card" data-name="' . htmlspecialchars($product['name'], ENT_QUOTES) . '">';
                    if (!empty($product['image'])) {
                        echo '<img src="' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                    } else {
                        echo '<img src="https://via.placeholder.com/150" alt="منتج">';
                    }
                    echo '<div>';
                    echo '<div class="product-title">' . htmlspecialchars($product['name']) . '</div>';
                    echo '<div class="product-price">' . htmlspecialchars($product['price']) . ' ليرة</div>';
                    echo '</div>';
                    echo '<button class="btn" onclick="addToCart(\'' . htmlspecialchars($product['name'], ENT_QUOTES) . '\', ' . $product['price'] . ')">إضافة إلى السلة</button>';
                    echo '</div>';
                }
            }
            $stmt->close();

            echo '</div>'; 
            echo '</div>'; 
        }
    } else {
        echo '<p style="text-align:center; margin-top:50px;">لا توجد تصنيفات أو منتجات متوفرة حالياً.</p>';
    }
    $conn->close();
    ?>
</div>

<!-- زر العودة للأعلى -->
<button onclick="scrollToTop()" id="scrollTopBtn" title="العودة للأعلى">⬆</button>

<!-- نافذة المنيو المنبثقة -->
<div id="menuModal" class="modal">
    <div class="modal-content" style="max-width: 350px; text-align: center;">
        <span class="close-btn" onclick="toggleMenuModal()">&times;</span>
        <h2>أقسام المنيو</h2>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
            <?php
            $conn_mb = new mysqli($host, $user, $pass, $dbname, (int)$port);
            $conn_mb->set_charset("utf8");
            $cat_mb_result = $conn_mb->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
            if ($cat_mb_result) {
                while ($cmb = $cat_mb_result->fetch_assoc()) {
                    echo '<a href="#cat-' . md5($cmb['category']) . '" onclick="toggleMenuModal()" style="background:var(--accent-color); color:white; padding:10px; border-radius:5px; text-decoration:none; font-weight:bold;">' . htmlspecialchars($cmb['category']) . '</a>';
                }
            }
            $conn_mb->close();
            ?>
        </div>
    </div>
</div>

<!-- نافذة سلة المشتريات ومعلومات الزبون -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="toggleCartModal()">&times;</span>
        <h2>سلة المشتريات</h2>
        
        <ul id="cart-items" class="cart-items-list">
            <p style="text-align:center; color:#777;">السلة فارغة حالياً.</p>
        </ul>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <button class="clear-cart-btn" onclick="clearCart()">تفريغ السلة</button>
            <div class="total-price" style="margin:0;">المجموع: <span id="cart-total">0</span> ليرة</div>
        </div>

        <!-- قسم بيانات الزبون (مع الحفظ التلقائي) -->
        <div class="customer-form">
            <h3>📝 معلومات التوصيل:</h3>
            <div class="form-group">
                <label>اسم الزبون:</label>
                <input type="text" id="custName" placeholder="أدخل اسمك الكريم" oninput="saveCustomerData()">
            </div>
            <div class="form-group">
                <label>رقم الهاتف:</label>
                <input type="text" id="custPhone" placeholder="أدخل رقم هاتفك" oninput="saveCustomerData()">
            </div>
            <div class="form-group">
                <label>العنوان بالتفصيل:</label>
                <textarea id="custAddress" rows="2" placeholder="المدينة، الشارع، البناء..." oninput="saveCustomerData()"></textarea>
            </div>
        </div>

        <button class="whatsapp-checkout-btn" onclick="sendToWhatsApp()">إرسال الطلب عبر واتساب 📱</button>
    </div>
</div>

<!-- رسالة التنبيه المؤقتة -->
<div id="toast">رسالة تنبيه</div>


<!-- ==========================================================
   5. قسم البرمجة النصية (JavaScript Logic)
   ========================================================== -->
<script>
// استرجاع السلة وبيانات الزبون من LocalStorage لتوفير تجربة ممتازة
let cart = JSON.parse(localStorage.getItem('ali_store_cart')) || [];
loadCustomerData();
updateCartUI();

function showToast(text) {
    let toast = document.getElementById("toast");
    toast.innerText = text;
    toast.className = "show";
    setTimeout(() => { toast.className = toast.className.replace("show", ""); }, 2500);
}

function addToCart(name, price) {
    let existingItem = cart.find(item => item.name === name);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ name: name, price: price, quantity: 1 });
    }
    saveAndupdateCart();
    showToast("تمت إضافة " + name + " إلى السلة");
}

function saveAndupdateCart() {
    localStorage.setItem('ali_store_cart', JSON.stringify(cart));
    updateCartUI();
}

function updateCartUI() {
    let cartCount = document.getElementById('cart-count');
    let cartItemsList = document.getElementById('cart-items');
    let cartTotal = document.getElementById('cart-total');

    let totalCount = 0;
    let totalPrice = 0;
    cartItemsList.innerHTML = '';

    if (cart.length === 0) {
        cartItemsList.innerHTML = '<p style="text-align:center; color:#777;">السلة فارغة حالياً.</p>';
    } else {
        cart.forEach((item, index) => {
            totalCount += item.quantity;
            totalPrice += item.price * item.quantity;

            let li = document.createElement('li');
            li.className = 'cart-item';
            li.innerHTML = `
                <span>${item.name} (${item.quantity})</span>
                <span>${item.price * item.quantity} ليرة</span>
                <button onclick="removeFromCart(${index})" style="background:#e74c3c; color:white; border:none; padding:3px 8px; border-radius:3px; cursor:pointer;">حذف</button>
            `;
            cartItemsList.appendChild(li);
        });
    }

    cartCount.innerText = totalCount;
    cartTotal.innerText = totalPrice;
}

function removeFromCart(index) {
    cart.splice(index, 1);
    saveAndupdateCart();
}

function clearCart() {
    if (confirm("هل أنت متأكد من تفريغ السلة بالكامل؟")) {
        cart = [];
        saveAndupdateCart();
    }
}

// حفظ بيانات الزبون تلقائياً في ذاكرة المتصفح
function saveCustomerData() {
    let customer = {
        name: document.getElementById('custName').value,
        phone: document.getElementById('custPhone').value,
        address: document.getElementById('custAddress').value
    };
    localStorage.setItem('ali_store_customer', JSON.stringify(customer));
}

function loadCustomerData() {
    let savedCustomer = JSON.parse(localStorage.getItem('ali_store_customer'));
    if (savedCustomer) {
        if(document.getElementById('custName')) document.getElementById('custName').value = savedCustomer.name || '';
        if(document.getElementById('custPhone')) document.getElementById('custPhone').value = savedCustomer.phone || '';
        if(document.getElementById('custAddress')) document.getElementById('custAddress').value = savedCustomer.address || '';
    }
}

function toggleCartModal() {
    let modal = document.getElementById('cartModal');
    modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
}

function toggleMenuModal() {
    let modal = document.getElementById('menuModal');
    modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
}

function toggleColorPopup() {
    let popup = document.getElementById('colorPopup');
    popup.style.display = popup.style.display === 'flex' ? 'none' : 'flex';
}

function changeTheme(primary, accent) {
    document.documentElement.style.setProperty('--primary-color', primary);
    document.documentElement.style.setProperty('--accent-color', accent);
    document.getElementById('colorPopup').style.display = 'none';
}

// محرك البحث الفوري
function filterProducts() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        let name = card.getAttribute('data-name').toLowerCase();
        if (name.includes(input)) {
            card.style.display = "flex";
        } else {
            card.style.display = "none";
        }
    });
}

// إرسال الطلب وتفاصيل الزبون عبر الواتساب
function sendToWhatsApp() {
    if (cart.length === 0) {
        alert("السلة فارغة! قم بإضافة منتجات أولاً.");
        return;
    }

    let name = document.getElementById('custName').value.trim();
    let phone = document.getElementById('custPhone').value.trim();
    let address = document.getElementById('custAddress').value.trim();

    if (!name || !phone || !address) {
        alert("الرجاء تعبئة جميع بيانات التوصيل (الاسم، الهاتف، والعنوان) قبل الإرسال!");
        return;
    }

    let message = `🛒 *طلب جديد من Ali And Store* 🛒\n\n`;
    message += `👤 *الاسم:* ${name}\n`;
    message += `📞 *الهاتف:* ${phone}\n`;
    message += `📍 *العنوان:* ${address}\n\n`;
    message += `🛍️ *المنتجات المطلوبة:*\n`;

    let totalPrice = 0;
    cart.forEach(item => {
        let itemTotal = item.price * item.quantity;
        message += `- ${item.name} (الكمية: ${item.quantity}) - السعر: ${itemTotal} ليرة\n`;
        totalPrice += itemTotal;
    });

    message += `\n💰 *المجموع الكلي:* ${totalPrice} ليرة`;

    let phoneNumber = "96181058043"; 
    let encodedMessage = encodeURIComponent(message);
    
    window.open(`https://wa.me/${phoneNumber}?text=${encodedMessage}`, '_blank');
}

// زر العودة للأعلى
window.onscroll = function() {
    let btn = document.getElementById("scrollTopBtn");
    if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
        btn.style.display = "block";
    } else {
        btn.style.display = "none";
    }
};

function scrollToTop() {
    window.scrollTo({top: 0, behavior: 'smooth'});
}

// إغلاق النوافذ عند النقر خارجها
window.onclick = function(event) {
    let modal = document.getElementById('cartModal');
    let menuModal = document.getElementById('menuModal');
    if (event.target === modal) modal.style.display = 'none';
    if (event.target === menuModal) menuModal.style.display = 'none';
}
</script>

</body>
</html>
