<?php
// wpay_success.php
require_once 'config.php'; // أو اتصال القاعدة الخاص بك

ini_set('display_errors', 1);
error_reporting(E_ALL);

$order_id = intval($_GET['order_id'] ?? 0);

if ($order_id > 0) {
    // تحديث حالة الإعلان إلى approved ليظهر فوراً في الـ Bento Grid على الصفحة الرئيسية
    $stmt = $conn->prepare("UPDATE customer_ads SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نجاح الدفع - Ali And Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #f8f9fa; text-align: center; padding-top: 80px; }
        .success-box { background: white; max-width: 450px; margin: 0 auto; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .btn { display: inline-block; margin-top: 20px; background: #27ae60; color: white; padding: 10px 25px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .btn:hover { background: #219653; }
    </style>
</head>
<body>
    <div class="success-box">
        <h1 style="color: #27ae60; margin-top: 0;">🎉 تم الدفع بنجاح!</h1>
        <p style="color: #555; font-size: 15px;">شكراً لك! تم اعتماد رسوم الإعلان عبر <b>WPAY</b> ونشر إعلانك بنجاح في المتجر.</p>
        <a href="index.php" class="btn">العودة إلى الصفحة الرئيسية</a>
    </div>
</body>
</html>
