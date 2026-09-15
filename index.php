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
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8f9fa;
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
        }
        header h1 {
            margin: 0;
            font-size: 24px;
        }
        .cart-icon-btn {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            font-weight: bold;
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
            font-size: 22px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
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
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Cairo', sans-serif;
            width: 100%;
            font-weight: 600;
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
        // حلقة تكرارية لكل تصنيف (Category)
        while ($cat_row = $categories_result->fetch_assoc()) {
            $current_category = $cat_row['category'];
            echo '<div class="category-section">';
            echo '<div class="category-title">' . htmlspecialchars($current_category) . '</div>';
            echo '<div class="products-grid">';

            // جلب المنتجات التابعة لهذا التصنيف حصراً
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
                        // صورة افتراضية في حال عدم توفر صورة
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

            echo '</div>'; // نهاية products-grid
            echo '</div>'; // نهاية category-section
        }
    } else {
        echo '<p style="text-align:center; margin-top:50px;">لا توجد تصنيفات أو منتجات متوفرة حالياً.</p>';
    }
    $conn->close();
    ?>
</div>

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

    // استبدل الرقم أدناه برقم الواتساب الخاص بالمتجر مع رمز الدولة
    let phoneNumber = "963000000000"; 
    let encodedMessage = encodeURIComponent(message);
    
    window.open(`https://wa.me/${phoneNumber}?text=${encodedMessage}`, '_blank');
}

// إغلاق النافذة المنبثقة عند النقر خارجها
window.onclick = function(event) {
    let modal = document.getElementById('cartModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>
