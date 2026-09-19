<?php
// init_wpay.php
require_once 'config.php'; // أو إعدادات الاتصال بقاعدة البيانات

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cust_name = trim($_POST['ad_name'] ?? '');
    $cust_phone = trim($_POST['ad_phone'] ?? '');
    $ad_title = trim($_POST['ad_title'] ?? '');
    $ad_desc = trim($_POST['ad_desc'] ?? '');
    $package_type = intval($_POST['ad_package'] ?? 1);
    $amount_paid = ($package_type === 15) ? 10.00 : 1.00;
    $image_url = trim($_POST['ad_image'] ?? '');

    // 1. حفظ الطلب مؤقتاً بحالة pending في قاعدة البيانات للحصول على ID
    $stmt = $conn->prepare("INSERT INTO customer_ads (customer_name, phone, title, description, image_url, package_type, amount_paid, wish_ref, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'WPAY_ONLINE', 'pending')");
    $stmt->bind_param("sssssid", $cust_name, $cust_phone, $ad_title, $ad_desc, $image_url, $package_type, $amount_paid);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // 2. بيانات الاتصال بـ WPAY API (ضع بيانات التاجر الحقيقية هنا أو استدعها من config)
    $merchant_id = "YOUR_MERCHANT_ID"; 
    $api_key = "YOUR_API_KEY";
    
    // روابط العودة إلى موقعك على Railway
    $base_url = "https://" . $_SERVER['HTTP_HOST'];
    $return_url = $base_url . "/wpay_success.php?order_id=" . $order_id;
    $cancel_url = $base_url . "/index.php?msg=" . urlencode("تم إلغاء عملية الدفع.");

    // (ملاحظة: إذا كنت تستخدم مكتبة أو رابط API الخاص بـ Whish Pay، يتم إرسال الطلب هنا عبر cURL والحصول على payment_url)
    // كمثال، إذا كان لديك رابط الدفع جاهزاً، قم بالتوجيه إليه مباشرة:
    
    /* مثال على اتصال cURL مع Whish Pay API:
       $payload = json_encode(['amount' => $amount_paid, 'currency' => 'USD', 'return_url' => $return_url, ...]);
       // إرسال الطلب واستقبال رابط الـ Whish
    */

    // إذا كان الرابط يفتح مباشرة كما في صورتك، تأكد أن الـ return_url يوجه إلى wpay_success.php
}
?>
