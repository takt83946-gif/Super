<?php
require_once 'config.php';

// تحديث هيكل الجداول وإنشاؤها تلقائياً مع الحقول الجديدة
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) DEFAULT '$',
        category VARCHAR(100) NOT NULL,
        image VARCHAR(255) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // إضافة الأعمدة إن لم تكن موجودة مسبقاً في الجدول القديم
    $columns = $conn->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('currency', $columns)) {
        $conn->exec("ALTER TABLE products ADD COLUMN currency VARCHAR(10) DEFAULT '$'");
    }
    if (!in_array('image', $columns)) {
        $conn->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT ''");
    }

    $conn->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        total_price VARCHAR(100) NOT NULL,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch(PDOException $e) {}

$message = "";

// معالجة إضافة منتج جديد مع رفع الصورة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $currency = $_POST['currency'];
    $category = trim($_POST['category']);
    $imagePath = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    if (!empty($name) && $price > 0) {
        try {
            $stmt = $conn->prepare("INSERT INTO products (name, price, currency, category, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $currency, $category, $imagePath]);
            $message = "تم إضافة المنتج بنجاح!";
        } catch(PDOException $e) {
            $message = "خطأ في الإضافة: " . $e->getMessage();
        }
    } else {
        $message = "الرجاء إدخال اسم وسعر صحيحين.";
    }
}

// معالجة حذف منتج
if (isset($_GET['delete_product'])) {
    $id = intval($_GET['delete_product']);
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();
    if ($prod && !empty($prod['image']) && file_exists($prod['image'])) {
        unlink($prod['image']);
    }
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit();
}

// إحصائيات المبيعات
try {
    $today = date('Y-m-d');
    $thisMonth = date('Y-m');

    $stmtToday = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE(order_date) = ?");
    $stmtToday->execute([$today]);
    $salesToday = $stmtToday->fetch(PDO::FETCH_ASSOC)['count'];

    $stmtMonth = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = ?");
    $stmtMonth->execute([$thisMonth]);
    $salesMonth = $stmtMonth->fetch(PDO::FETCH_ASSOC)['count'];

    $products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $orders = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $salesToday = 0; $salesMonth = 0; $products = []; $orders = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم الشاملة - المتجر</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-gauge"></i> لوحة تحكم المتجر</a>
            <a href="index.php" target="_blank" class="btn btn-outline-light btn-sm">عرض المتجر 🛒</a>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if (!empty($message)): ?>
            <div class="alert alert-info text-center fw-bold"><?= htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="card bg-primary text-white shadow-sm border-0 text-center p-3">
                    <h6 class="text-white-50"><i class="fa-solid fa-calendar-day"></i> مبيعات اليوم</h6>
                    <h3 class="fw-bold mb-0"><?= $salesToday; ?> طلب</h3>
                </div>
            </div>
            <div class="col-6">
                <div class="card bg-success text-white shadow-sm border-0 text-center p-3">
                    <h6 class="text-white-50"><i class="fa-solid fa-calendar-alt"></i> مبيعات هذا الشهر</h6>
                    <h3 class="fw-bold mb-0"><?= $salesMonth; ?> طلب</h3>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="fa-solid fa-plus-circle"></i> إضافة منتج جديد (صورة + سعر بالدولار أو الليرة)
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">اسم المنتج</label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: آيفون 15 برو">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label">السعر</label>
                            <input type="number" step="0.01" name="price" class="form-control" required placeholder="مثال: 500 أو 4500000">
                        </div>
                        <div class="col-4">
                            <label class="form-label">العملة</label>
                            <select name="currency" class="form-select">
                                <option value="$">دولار ($)</option>
                                <option value="L.L">ليرة لبناني (L.L)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">القسم</label>
                        <select name="category" class="form-select">
                            <option value="هواتف ذكية">هواتف ذكية</option>
                            <option value="إكسسوارات">إكسسوارات</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">صورة المنتج (من الهاتف)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" name="add_product" class="btn btn-success w-100 fw-bold py-2">حفظ ونشر المنتج</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-secondary text-white fw-bold">
                <i class="fa-solid fa-shopping-bag"></i> أحدث طلبات الزبائن
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الطلب</th>
                                <th>اسم المنتج المطلُوب</th>
                                <th>السعر</th>
                                <th>التاريخ والوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders) > 0): ?>
                                <?php foreach ($orders as $ord): ?>
                                    <tr>
                                        <td>#<?= $ord['id']; ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($ord['product_name']); ?></td>
                                        <td class="text-success fw-bold"><?= htmlspecialchars($ord['total_price']); ?></td>
                                        <td class="small text-muted"><?= $ord['order_date']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-muted py-3">لا توجد طلبات مسجلة حتى الآن.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="fa-solid fa-list"></i> إدارة المنتجات الحالية
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>الصورة</th>
                                <th>الاسم</th>
                                <th>السعر</th>
                                <th>القسم</th>
                                <th>حذف</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $row): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                                                <img src="<?= $row['image']; ?>" alt="img" style="width: 45px; height: 45px; object-fit: cover;" class="rounded">
                                            <?php else: ?>
                                                <span class="text-muted small">بدون</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['name']); ?></td>
                                        <td class="text-success fw-bold"><?= number_format($row['price'], 2) . ' ' . $row['currency']; ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['category']); ?></span></td>
                                        <td>
                                            <a href="admin.php?delete_product=<?= $row['id']; ?>" onclick="return confirm('هل أنت متأكد من الحذف؟');" class="btn btn-danger btn-sm">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-muted py-3">لا توجد منتجات مضافة حالياً.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
