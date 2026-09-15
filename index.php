<?php
// إظهار الأخطاء لاكتشاف أي مشكلة فوراً بدلاً من الشاشة البيضاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

// اتصال بقاعدة البيانات
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "supermarket_alsaaha"; // تأكد أن هذا الاسم مطابق تماماً لقاعدة البيانات لديك

$conn = new mysqli($host, $user, $pass, $dbname);
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
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            direction: rtl;
            text-align: right;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        header h1 {
            margin: 0;
            font-size: 22px;
        }
        .search-box {
            flex: 1;
            max-width: 400px;
            margin: 0 20px;
        }
        .search-box input {
            width: 100%;
            padding: 8px 15px;
            border-radius: 20px;
            border: none;
            font-family: 'Cairo', sans-serif;
            outline: none;
        }
        .cart-icon-btn {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
        }
        .cart-icon-btn:hover {
            background-color: #219653;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }
        .category-section {
            margin-bottom: 35px;
        }
        .category-title {
            font-size: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        .product-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-card img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            border-radius: 8px;
        }
        .product-title {
            font-size: 16px;
            font-weight: 600;
            margin: 10px 0 5px;
            color: #333;
        }
        .product-price {
            color: #27ae60;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            width: 100%;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover {
            background-color: #2980b9;
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
            backdrop-filter: blur(3px);
        }
        .modal-content {
            background-color: white;
            margin: 8% auto;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        .close-btn {
            color: #aaa;
            float: left;
            font-size: 26px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }
        .close-btn:hover {
            color: black;
        }
        .cart-items-list {
            list-style: none;
            padding: 0;
            margin: 15px 0;
            max-height: 280px;
            overflow-y: auto;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f1f1f1;
            font-size: 14px;
        }
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cart-item-controls button {
            background: #e0e0e0;
            border: none;
            width: 25px;
            height: 25px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .cart-item-controls button:hover {
            background: #d0d0d0;
        }
        .remove-item-btn {
            background: #e74c3c !important;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .whatsapp-checkout-btn {
            background-color: #25d366;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            width: 100%;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s;
        }
        .whatsapp-checkout-btn:hover {
            background-color: #1ebe5d;
        }
        .total-price {
            font-weight: bold;
            font-size: 17px;
            margin: 15px 0;
            text-align: left;
            color: #2c3e50;
        }
        .no-results {
            text-align: center;
            color: #777;
            margin: 40px 0;
            display: none;
        }
    </style>
</head>
<body>

<header>
    <h1>🛒 سوبرماركت الساحة</h1>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="ابحث عن أي منتج..." onkeyup="filterProducts()">
    </div>
    <button class="cart-icon-btn" onclick="toggleCartModal()">
        السلة (<span id="cart-count">0</span>)
    </button>
</header>

<div class="container">
    <?php
    if ($categories_result && $categories_result->num_rows > 0) {
        while ($cat_row = $categories_result->fetch_assoc()) {
            $current_category = $cat_row['category'];
            echo '<div class="category-section" data-category="' . htmlspecialchars($current_category) . '">';
            echo '<div class="category-title">' . htmlspecialchars($current_category) . '</div>';
            echo '<div class="products-grid">';

            $stmt = $conn->prepare("SELECT * FROM products WHERE category = ?");
            $stmt->bind_param("s", $current_category);
            $stmt->execute();
            $products_result = $stmt->get_result();

            if ($products_result && $products_result->num_rows > 0) {
                while($product = $products_result->fetch_assoc()) {
                    $imgSrc = !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/150';
                    echo '<div class="product-card" data-name="' . htmlspecialchars($product['name']) . '">';
                    echo '<img src="' . $imgSrc . '" alt="' . htmlspecialchars($product['name']) . '">';
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
        echo '<p style="text-align:center; margin-top:50px;">لا توجد تصنيفات أو منتجات متوفرة حالياً في قاعدة البيانات.</p>';
    }
    $conn->close();
    ?>
    <div id="noResults" class="no-results">عذراً، لم يتم العثور على منتج بهذا الاسم.</div>
</div>

<!-- نافذة سلة المشتريات المنبثقة -->
<div id="cartModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="toggleCartModal()">&times;</span>
        <h2 style="margin-top:0; font-size:20px;">سلة المشتريات</h2>
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
                <div>
                    <strong>${item.name}</strong><br>
                    <span style="color:#27ae60; font-size:13px;">${item.price} ليرة</span>
                </div>
                <div class="cart-item-controls">
                    <button onclick="changeQuantity(${index}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="changeQuantity(${index}, 1)">+</button>
                    <button class="remove-item-btn" onclick="removeFromCart(${index})">حذف</button>
                </div>
            `;
            cartItemsList.appendChild(li);
        });
    }

    cartCount.innerText = totalCount;
    cartTotal.innerText = totalPrice;
}

function changeQuantity(index, amount) {
    cart[index].quantity += amount;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    updateCartUI();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    updateCartUI();
}

function toggleCartModal() {
    let modal = document.getElementById('cartModal');
    modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
}

function filterProducts() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let sections = document.querySelectorAll('.category-section');
    let hasVisibleProducts = false;

    sections.forEach(section => {
        let products = section.querySelectorAll('.product-card');
        let sectionVisible = false;

        products.forEach(product => {
            let name = product.getAttribute('data-name').toLowerCase();
            if (name.includes(input)) {
                product.style.display = 'flex';
                sectionVisible = true;
                hasVisibleProducts = true;
            } else {
                product.style.display = 'none';
            }
        });

        section.style.display = sectionVisible ? 'block' : 'none';
    });

    document.getElementById('noResults').style.display = hasVisibleProducts ? 'none' : 'block';
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

    // استبدل الرقم أدناه برقم الواتساب الخاص بك مع رمز الدولة (مثال: 9639xxxxxxxx)
    let phoneNumber = "963000000000"; 
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
