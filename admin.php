<?php
require_once 'config.php';

// إصلاح وإنشاء الأعمدة تلقائياً في قاعدة البيانات لمنع أخطاء SQL نهائياً
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        currency VARCHAR(50) DEFAULT 'دولار ($)',
        category VARCHAR(100) DEFAULT 'عام',
        image VARCHAR(255) DEFAULT ''
    )");
} catch (PDOException $e) {
    // تجاهل إذا الجدول موجود مسبقاً
}

// التأكد من وجود الأعمدة إذا كان الجدول قديماً
$columns = $conn->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('currency', $columns)) {
    $conn->exec("ALTER TABLE products ADD COLUMN currency VARCHAR(50) DEFAULT 'دولار ($)'");
}
if (!in_array('category', $columns)) {
    $conn->exec("ALTER TABLE products ADD COLUMN category VARCHAR(100) DEFAULT 'عام'");
}
if (!in_array('image', $columns)) {
    $conn->exec("ALTER TABLE products ADD COLUMN image VARCHAR(255) DEFAULT ''");
}

$msg = "";
$error = "";

// حذف منتج
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php");
    exit;
}

// إضافة منتج جديد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? 0;
    $currency = $_POST['currency'] ?? 'دولار ($)';
    $category = trim($_POST['category'] ?? 'عام');
    $imagePath = '';

    if (!empty($name) && !empty($price)) {
        // معالجة رفع الصورة
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['image']['tmp_name'];
            $fileName = $_FILES['image']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = './uploads/';
                
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                $dest_path = $uploadFileDir . $newFileName;
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    $imagePath = $dest_path;
                }
            }
        }

        try {
            $stmt = $conn->prepare("INSERT INTO products (name, price, currency, category, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $currency, $category, $imagePath]);
            $msg = "تمت إضافة المنتج بنجاح!";
        } catch (PDOException $e) {
            $error = "خطأ في قاعدة البيانات: " . $e->getMessage();
        }
    } else {
        $error = "يرجى تعبئة اسم المنتج والسعر على الأقل.";
    }
}

// جلب المنتجات
try {
    $products = $conn->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم Alind Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; }
        .navbar-admin { background: #111; color: #fff; }
    </style>
</head>
<body>

    <nav class="navbar navbar-admin py-3 mb-4 shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="fw-bold fs-5"><i class="fa-solid fa-gauge me-2"></i> لوحة التحكم</span>
            <a href="index.php" class="btn btn-light btn-sm fw-bold"><i class="fa-solid fa-store me-1"></i> عرض المتجر</a>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success fw-bold"><?= $msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger fw-bold"><?= $error; ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-dark text-white fw-bold py-3 rounded-top-4">
                <i class="fa-solid fa-plus-circle me-1"></i> إضافة منتج جديد
            </div>
            <div class="card-body p-4">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المنتج</label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: آيفون 15 برو" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">السعر</label>
                            <input type="number" step="0.01" name="price" class="form-control" placeholder="مثال: 500" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">العملة</label>
                            <select name="currency" class="form-select">
                                <option value="دولار ($)">دولار ($)</option>
                                <option value="L.L">ليرة لبنانية (L.L)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">القسم</label>
                        <input type="text" name="category" class="form-control" placeholder="مثال: هواتف ذكية، إكسسوارات..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">صورة المنتج</label>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">حفظ ونشر المنتج 🚀</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-secondary text-white fw-bold py-3 rounded-top-4">
                <i class="fa-solid fa-box me-1"></i> المنتجات المخزنة حالياً (<?= count($products); ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle text-center">
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
                                <?php foreach($products as $p): ?>
                                    <tr>
                                        <td>
                                            <?php if(!empty($p['image']) && file_exists($p['image'])): ?>
                                                <img src="<?= $p['image']; ?>" width="40" height="40" style="object-fit:cover;" class="rounded">
                                            <?php else: ?>
                                                <span class="text-muted small">بدون</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($p['name']); ?></td>
                                        <td class="text-success fw-bold"><?= $p['price'] . ' ' . $p['currency']; ?></td>
                                        <td><span class="badge bg-dark"><?= htmlspecialchars($p['category']); ?></span></td>
                                        <td>
                                            <a href="admin.php?delete=<?= $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-4 text-muted">لا توجد منتجات مسجلة حالياً.</td>
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
