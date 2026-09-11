<?php
// إعدادات الاتصال بقاعدة البيانات تلقائياً
$host = getenv('MYSQLHOST') ?: 'localhost';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'elite_boutique';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // إنشاء قاعدة البيانات إن لم تكن موجودة
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$database`;");

    // إنشاء الجداول إن لم تكن موجودة
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        category VARCHAR(100) NOT NULL,
        image VARCHAR(500) DEFAULT 'https://images.unsplash.com/photo-1523275335684-37898b6baf30',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_name VARCHAR(255) NOT NULL,
        client_phone VARCHAR(50) NOT NULL,
        address TEXT NOT NULL,
        items TEXT NOT NULL,
        total DECIMAL(10, 2) NOT NULL,
        status VARCHAR(50) DEFAULT 'قيد المعالجة',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // إضافة منتجات تجريبية لو الجدول فارغ
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO products (name, price, category, image) VALUES 
            ('ساعة رولكس كلاسيكية إصدار خاص', 1250.00, 'ساعات ملكية', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30'),
            ('عطر نيش الملكي الفاخر', 450.00, 'عطور نادرة', 'https://images.unsplash.com/photo-1541643600914-78b084683601'),
            ('قلادة ألماس عيار 18', 2450.00, 'مجوهرات وألماس', 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f')");
    }
} catch (PDOException $e) {
    die("<div style='font-family:Tahoma; color:red; text-align:center; margin-top:50px;'>خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "</div>");
}

// معالجة إضافة منتج من لوحة التحكم الداخلية
$admin_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_product') {
    $p_name = $_POST['product_name'];
    $p_price = $_POST['product_price'];
    $p_cat = $_POST['product_category'];
    $p_img = $_POST['product_image'] ?: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30';
    
    $stmt = $pdo->prepare("INSERT INTO products (name, price, category, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$p_name, $p_price, $p_cat, $p_img]);
    header("Location: index.php?view=admin&success=1");
    exit;
}

// معالجة الطلب وإرساله لواتساب
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $name = htmlspecialchars($_POST['name']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $cart_data = htmlspecialchars($_POST['cart_data']);
    $total = htmlspecialchars($_POST['total_amount']);

    $stmt = $pdo->prepare("INSERT INTO orders (client_name, client_phone, address, items, total, status) VALUES (?, ?, ?, ?, ?, 'قيد المعالجة')");
    $stmt->execute([$name, $phone, $address, $cart_data, $total]);
    $order_id = $pdo->lastInsertId();

    $store_whatsapp = "963900000000"; // استبدل برقم هاتفك
    $whatsapp_url = "https://wa.me/$store_whatsapp?text=" . urlencode("مرحباً، أود تأكيد طلبي الملكي رقم (#$order_id)\nالاسم: $name\nالهاتف: $phone\nالعنوان: $address\nالمنتجات: $cart_data\nالإجمالي: $total $");
    
    header("Location: $whatsapp_url");
    exit;
}

$view = isset($_GET['view']) ? $_GET['view'] : 'store';
$selectedCat = isset($_GET['cat']) ? trim($_GET['cat']) : '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Boutique | بوتيك النخبة الفاخر</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; background-color: #050505; color: #f3f4f6; }
        .luxury-gold { background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .luxury-bg-gold { background: linear-gradient(135deg, #d4af37 0%, #aa771c 100%); }
        .glass-card { background: rgba(18, 18, 20, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(212, 175, 55, 0.15); }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-amber-500 selection:text-black">

    <header class="border-b border-amber-500/10 bg-black/80 backdrop-blur-xl sticky top-0 z-40 px-6 py-5">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-black tracking-widest luxury-gold">✧ ELITE BOUTIQUE</a>
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-xs text-zinc-400 hover:text-amber-400 transition">الرئيسية</a>
                <a href="index.php?view=admin" class="text-xs text-zinc-400 hover:text-amber-400 transition">لوحة الإدارة</a>
                <?php if ($view === 'store'): ?>
                <button onclick="toggleCart()" class="relative bg-zinc-900 hover:bg-zinc-800 border border-amber-500/30 px-5 py-2.5 rounded-full text-xs font-bold transition flex items-center gap-3">
                    <span class="text-amber-400">حقيبة التسوق</span>
                    <span id="cart-count" class="luxury-bg-gold text-black font-black rounded-full w-5 h-5 flex items-center justify-center text-[10px]">0</span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-10 flex-grow">
        <?php if ($view === 'admin'): 
            $products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            $orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        ?>
            <div class="max-w-6xl mx-auto">
                <?php if(isset($_GET['success'])): ?>
                    <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs text-center">✨ تم إضافة المنتج بنجاح للقاعدة.</div>
                <?php endif; ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="glass-card p-6 rounded-2xl h-fit">
                        <h2 class="text-sm font-bold mb-6 luxury-gold border-b border-amber-500/10 pb-3">إضافة منتج جديد</h2>
                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="add_product">
                            <input type="text" name="product_name" required placeholder="اسم المنتج" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white">
                            <input type="number" step="0.01" name="product_price" required placeholder="السعر ($)" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white">
                            <input type="text" name="product_category" required placeholder="القسم" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white">
                            <input type="text" name="product_image" placeholder="رابط الصورة (URL)" class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-3 py-2 text-xs text-white">
                            <button type="submit" class="w-full luxury-bg-gold text-black font-black py-3 rounded-xl text-xs">حفظ وإضافة +</button>
                        </form>
                    </div>
                    <div class="lg:col-span-2 space-y-8">
                        <div class="glass-card p-6 rounded-2xl">
                            <h2 class="text-sm font-bold mb-4 luxury-gold border-b border-amber-500/10 pb-3">المنتجات المعروضة</h2>
                            <table class="w-full text-right text-xs">
                                <thead><tr class="text-zinc-500 border-b border-zinc-800"><th class="pb-2">المنتج</th><th class="pb-2">القسم</th><th class="pb-2">السعر</th></tr></thead>
                                <tbody class="divide-y divide-zinc-800/50">
                                    <?php foreach($products as $p): ?>
                                    <tr><td class="py-3"><?= htmlspecialchars($p['name']) ?></td><td class="py-3 text-zinc-400"><?= htmlspecialchars($p['category']) ?></td><td class="py-3 text-amber-400"><?= $p['price'] ?>$</td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="glass-card p-6 rounded-2xl">
                            <h2 class="text-sm font-bold mb-4 luxury-gold border-b border-amber-500/10 pb-3">سجل طلبات العملاء</h2>
                            <table class="w-full text-right text-xs">
                                <thead><tr class="text-zinc-500 border-b border-zinc-800"><th class="pb-2">#</th><th class="pb-2">العميل</th><th class="pb-2">المنتجات</th><th class="pb-2">الإجمالي</th></tr></thead>
                                <tbody class="divide-y divide-zinc-800/50">
                                    <?php foreach($orders as $o): ?>
                                    <tr><td class="py-3">#<?= $o['id'] ?></td><td class="py-3"><?= htmlspecialchars($o['client_name']) ?><br><span class="text-[10px] text-zinc-500"><?= $o['client_phone'] ?></span></td><td class="py-3"><?= htmlspecialchars($o['items']) ?></td><td class="py-3 text-amber-400"><?= $o['total'] ?>$</td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: 
            $categories = $pdo->query("SELECT DISTINCT category as name FROM products")->fetchAll(PDO::FETCH_ASSOC);
            $totalProductsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
            
            $sql = "SELECT * FROM products WHERE 1=1";
            $params = [];
            if (!empty($selectedCat)) {
                $sql .= " AND category = ?";
                $params[] = $selectedCat;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <aside class="lg:col-span-1">
                    <div class="glass-card rounded-2xl p-6 sticky top-28 shadow-2xl">
                        <h3 class="text-xs font-bold tracking-widest uppercase mb-5 luxury-gold border-b border-amber-500/10 pb-3 text-center">مجموعات المتجر</h3>
                        <div class="flex flex-col gap-2.5">
                            <a href="index.php" class="flex items-center justify-between px-4 py-3.5 rounded-xl font-medium text-xs border <?= empty($selectedCat) ? 'bg-amber-500/10 text-amber-300 border-amber-500/60' : 'bg-zinc-900/40 text-zinc-400 border-zinc-800' ?>">
                                <span>كافة المنتجات</span>
                                <span class="text-[10px] bg-black/60 px-2 py-0.5 rounded-full text-zinc-500"><?= $totalProductsCount ?></span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="index.php?cat=<?= urlencode($cat['name']) ?>" class="flex items-center justify-between px-4 py-3.5 rounded-xl font-medium text-xs border <?= $selectedCat == $cat['name'] ? 'bg-amber-500/10 text-amber-300 border-amber-500/60' : 'bg-zinc-900/40 text-zinc-400 border-zinc-800' ?>">
                                    <span><?= htmlspecialchars($cat['name']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>

                <div class="lg:col-span-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php if (empty($products)): ?>
                            <div class="col-span-full text-center py-20 text-zinc-600 glass-card rounded-2xl">لا توجد قطع متوفرة حالياً.</div>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <div class="glass-card rounded-2xl overflow-hidden flex flex-col justify-between group">
                                    <div class="relative overflow-hidden aspect-[4/3] bg-zinc-950">
                                        <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover opacity-90 group-hover:scale-110 transition-all duration-700">
                                    </div>
                                    <div class="p-5 flex flex-col flex-grow justify-between gap-5">
                                        <div>
                                            <h3 class="font-medium text-xs mb-2 text-zinc-200 line-clamp-1"><?= htmlspecialchars($p['name']) ?></h3>
                                            <div class="luxury-gold font-black text-base"><?= number_format($p['price'], 2) ?> $</div>
                                        </div>
                                        <button onclick='addToCart(<?= json_encode($p) ?>)' class="w-full bg-zinc-900 hover:bg-amber-500 text-amber-400 hover:text-black font-bold py-2.5 rounded-xl border border-amber-500/30 transition text-xs">إضافة للحقيبة +</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="cart-modal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden justify-end">
                <div class="w-full max-w-md bg-[#0a0a0c] border-l border-amber-500/20 h-full p-6 flex flex-col justify-between shadow-2xl">
                    <div>
                        <div class="flex justify-between items-center mb-6 border-b border-amber-500/10 pb-4">
                            <h3 class="text-xs font-bold tracking-widest uppercase luxury-gold">🛒 حقيبة التسوق الخاصة</h3>
                            <button onclick="toggleCart()" class="text-zinc-500 hover:text-white text-sm font-bold">✕</button>
                        </div>
                        <div id="cart-items" class="space-y-3 max-h-[40vh] overflow-y-auto pr-1"></div>
                    </div>
                    <div class="border-t border-amber-500/10 pt-4">
                        <div class="mb-4 flex justify-between items-center text-xs font-bold bg-zinc-900 p-3 rounded-xl">
                            <span class="text-zinc-400">الإجمالي:</span>
                            <span id="cart-total" class="luxury-gold text-base">0.00 $</span>
                        </div>
                        <form method="POST" class="space-y-3" onsubmit="prepareCheckout(event)">
                            <input type="hidden" name="action" value="checkout">
                            <input type="hidden" id="cart_data" name="cart_data">
                            <input type="hidden" id="total_amount" name="total_amount">
                            <input type="text" name="name" placeholder="الاسم الكريم" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-zinc-200 focus:outline-none focus:border-amber-500">
                            <input type="text" name="phone" placeholder="رقم الهاتف" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-zinc-200 focus:outline-none focus:border-amber-500">
                            <input type="text" name="address" placeholder="عنوان التوصيل بالتفصيل" required class="w-full bg-zinc-900 border border-zinc-800 rounded-xl px-4 py-2.5 text-xs text-zinc-200 focus:outline-none focus:border-amber-500">
                            <button type="submit" class="w-full luxury-bg-gold text-black font-black py-3 rounded-xl text-xs tracking-wider">إتمام الطلب الملكي عبر الواتساب 👑</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="border-t border-amber-500/10 py-6 text-center text-[10px] text-zinc-600 bg-black">Elite Boutique © 2026</footer>

    <?php if ($view === 'store'): ?>
    <script>
        let cart = [];
        function toggleCart() {
            const modal = document.getElementById('cart-modal');
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
        function addToCart(product) {
            const existing = cart.find(item => item.id === product.id);
            if (existing) { existing.qty++; } else { cart.push({ ...product, qty: 1 }); }
            updateCartUI();
        }
        function changeQty(index, delta) {
            cart[index].qty += delta;
            if (cart[index].qty <= 0) cart.splice(index, 1);
            updateCartUI();
        }
        function updateCartUI() {
            const container = document.getElementById('cart-items');
            container.innerHTML = cart.length === 0 ? '<div class="text-center py-10 text-zinc-600 text-xs">الحقيبة فارغة</div>' : '';
            let total = 0, count = 0;
            cart.forEach((item, index) => {
                total += item.price * item.qty;
                count += item.qty;
                container.innerHTML += `<div class="flex justify-between items-center bg-zinc-900/40 p-3 rounded-xl border border-zinc-800"><div class="truncate"><h4 class="text-xs text-zinc-200">${item.name}</h4><p class="text-[10px] text-amber-400">${item.price}$</p></div><div class="flex items-center gap-2 bg-black px-2 py-1 rounded"><button type="button" onclick="changeQty(${index}, -1)" class="text-zinc-400">-</button><span class="text-xs text-white">${item.qty}</span><button type="button" onclick="changeQty(${index}, 1)" class="text-zinc-400">+</button></div></div>`;
            });
            document.getElementById('cart-count').innerText = count;
            document.getElementById('cart-total').innerText = total.toFixed(2) + ' $';
        }
        function prepareCheckout(e) {
            if (cart.length === 0) { alert('الحقيبة فارغة!'); e.preventDefault(); return; }
            let summary = cart.map(i => `${i.name} (العدد: ${i.qty})`).join(', ');
            let total = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            document.getElementById('cart_data').value = summary;
            document.getElementById('total_amount').value = total.toFixed(2);
        }
    </script>
    <?php endif; ?>
</body>
</html>
