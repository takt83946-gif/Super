<?php
// اتصال بقاعدة البيانات (يدعم التشغيل المحلي وعلى Railway تلقائياً)
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

// جلب التصنيفات الموجودة أولاً
$categories_sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''";
$categories_result = $conn->query($categories_sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سوبرماركت الساحة</title>
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
            margin: 0;
            padding: 0;
            direction: rtl;
            text-align: right;
            transition: background-color 0.3s;
        }
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
        
        /* تصميم الفوتر السفلي المطلوب */
        footer {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 20px;
            margin-top: 50px;
            transition: background-color 0.3s;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }
        .footer-section h3 {
            font-size: 18px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-section ul li {
            margin-bottom: 8px;
        }
        .footer-section ul li a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-section ul li a:hover {
            color: white;
            text-decoration: underline;
        }
        .color-options {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .color-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid white;
            display: inline-block;
        }
        .footer-bottom {
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 25px;
            padding-top: 15px;
            font-size: 14px;
            color: #ccc;
        }

        /* تصميم نافذة سلة المشتريات المنبثقة */
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
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            position: relative;
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
            max-height: 250px;
            overflow-y: auto;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
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
        .total-price {
            font-weight: bold;
            font-size: 18px;
            margin: 15px 0;
            text-align: left;
        }
    </style>
</head>
<body>

<header>
    <h1>سوبرماركت الساحة</h1>
    <button class="cart-icon-btn" onclick="toggleCartModal()">🛒 سلة المشتريات (<span id="cart-count">0</span>)</button>
</header>

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
                    echo '<div class="product-card">';
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

<!-- الفوتر السفلي (الأقسام الأربعة المطلوبة) -->
<footer>
    <div class="footer-container">
        <!-- 1. المنيو (التصنيفات) -->
        <div class="footer-section">
            <h3>المنيو (التصنيفات)</h3>
            <ul>
                <?php
                // إعادة الاتصال لجلب التصنيفات للفوتر
                $conn_f = new mysqli($host, $user, $pass, $dbname, (int)$port);
                $conn_f->set_charset("utf8");
                $cat_f_result = $conn_f->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
                if ($cat_f_result) {
                    while ($cf = $cat_f_result->fetch_assoc()) {
                        echo '<li><a href="#cat-' . md5($cf['category']) . '">' . htmlspecialchars($cf['category']) . '</a></li>';
                    }
                }
                $conn_f->close();
                ?>
            </ul>
        </div>

        <!-- 2. زر تغيير لون الموقع -->
        <div class="footer-section">
            <h3>تغيير لون الموقع</h3>
            <p style="font-size: 13px; color: #ddd; margin-bottom: 8px;">اختر اللون المفضل:</p>
            <div class="color-options">
                <span class="color-circle" style="background-color: #2c3e50;" onclick="changeTheme('#2c3e50', '#3498db')" title="كلاسيكي داكن"></span>
                <span class="color-circle" style="background-color: #8e44ad;" onclick="changeTheme('#8e44ad', '#9b59b6')" title="بنفسجي"></span>
                <span class="color-circle" style="background-color: #d35400;" onclick="changeTheme('#d35400', '#e67e22')" title="برتقالي"></span>
                <span class="color-circle" style="background-color: #16a085;" onclick="changeTheme('#16a085', '#1abc9c')" title="تركواز"></span>
            </div>
        </div>

        <!-- 3. About الموقع -->
        <div class="footer-section">
            <h3>عن سوبرماركت الساحة</h3>
            <p style="font-size: 14px; color: #ddd; line-height: 1.6;">
                نقدم أفضل المنتجات الغذائية والاستهلاكية الطازجة بأفضل الأسعار لتلبية احتياجات عائلتك اليومية بكل سرعة وسهولة.
            </p>
        </div>

        <!-- 4. التواصل معنا -->
        <div class="footer-section">
            <h3>التواصل معنا</h3>
            <p style="font-size: 14px; color: #ddd; line-height: 1.8;">
                📞 الهاتف / واتساب: <br>
                <a href="https://wa.me/96181058043" target="_blank" style="color: #2ecc71; font-weight: bold; font-size: 16px;">+961 81 058 043</a>
            </p>
        </div>
    </div>
    <div class="footer-bottom">
        جميع الحقوق محفوظة &copy; سوبرماركت الساحة 2026
    </div>
</footer>

<!-- نافذة سلة المشتريات المنبثقة -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="toggleCartModal()">&times;</span>
        <h2>سلة المشتريات</h2>
        <ul id="cart-items" class="cart-items-list">
            <p style="text-align:center; color:#777;">السلة فارغة حالياً.</p>
        </ul>
        <div class="total-price">المجموع الكلي: <span id="cart-total">0</span> ليرة</div>
        <button class="whatsapp-checkout-btn" onclick="sendToWhatsApp()">إرسال الطلب عبر واتساب 📱</button>
    </div>
</div>

<script>
let cart = [];

function addToCart(name, price) {
    let existingItem = cart.find(item => item.name === name);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ name: name, price: price, quantity: 1 });
    }
    updateCartUI();
    alert("تمت إضافة " + name + " إلى السلة");
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
    updateCartUI();
}

function toggleCartModal() {
    let modal = document.getElementById('cartModal');
    modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
}

function changeTheme(primary, accent) {
    document.documentElement.style.setProperty('--primary-color', primary);
    document.documentElement.style.setProperty('--accent-color', accent);
}

function sendToWhatsApp() {
    if (cart.length === 0) {
        alert("السلة فارغة! قم بإضافة منتجات أولاً.");
        return;
    }

    let message = "مرحباً، أريد طلب المنتجات التالية من سوبرماركت الساحة:\n\n";
    let totalPrice = 0;

    cart.forEach(item => {
        let itemTotal = item.price * item.quantity;
        message += `- ${item.name} (الكمية: ${item.quantity}) - السعر: ${itemTotal} ليرة\n`;
        totalPrice += itemTotal;
    });

    message += `\nالمجموع الكلي: ${totalPrice} ليرة`;

    let phoneNumber = "96181058043"; 
    let encodedMessage = encodeURIComponent(message);
    
    window.open(`https://wa.me/${phoneNumber}?text=${encodedMessage}`, '_blank');
}

window.onclick = function(event) {
    let modal = document.getElementById('cartModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>
