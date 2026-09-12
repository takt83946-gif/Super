<?php
// ملف لوحة التحكم البسيطة - admin.php
session_start();

// بيانات تسجيل دخول وهمية للأدمن (يمكنك ربطها بقاعدة البيانات لاحقاً)
$admin_user = "admin";
$admin_pass = "123456";

$error = "";

// التحقق من إرسال بيانات الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
    }
}

// تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة تحكم Livsh</title>
    <style>
        body { font-family: Tahoma, sans-serif; background: #f4f4f4; margin: 0; padding: 50px; text-align: center; }
        .box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); display: inline-block; width: 300px; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .dashboard { text-align: right; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['is_admin'])): ?>
    <div class="box">
        <h2>تسجيل دخول الأدمن</h2>
        <?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="اسم المستخدم" required><br>
            <input type="password" name="password" placeholder="كلمة المرور" required><br>
            <button type="submit">دخول</button>
        </form>
    </div>
<?php else: ?>
    <div class="box dashboard" style="width: 600px;">
        <h2>أهلاً بك في لوحة تحكم متجر Livsh</h2>
        <p>من هنا يمكنك البدء في إضافة المنتجات وتعديل محتوى المتجر.</p>
        <hr>
        <a href="admin.php?logout=true"><button style="background: #dc3545;">تسجيل الخروج</button></a>
    </div>
<?php endif; ?>

</body>
</html>
