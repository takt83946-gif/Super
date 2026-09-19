<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cust_name = trim($_POST['ad_name'] ?? '');
    $cust_phone = trim($_POST['ad_phone'] ?? '');
    $ad_title = trim($_POST['ad_title'] ?? '');
    $ad_desc = trim($_POST['ad_desc'] ?? '');
    $image_url = trim($_POST['ad_image'] ?? '');
    $package_type = intval($_POST['ad_package'] ?? 1);
    $amount_paid = ($package_type === 15) ? 10.00 : 1.00;

    // 1. حفظ الطلب مؤقتاً في قاعدة البيانات بحالة غير مدفوع (unpaid)
    $stmt = $conn->prepare("INSERT INTO customer_ads (customer_name, phone, title, description, image_url, package_type, amount_paid, wish_ref, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING_WPAY', 'pending')");
    $stmt->bind_param("sssssids", $cust_name, $cust_phone, $ad_title, $ad_desc, $image_url, $package_type, $amount_paid);
    
    if (!$stmt->execute()) {
        die("خطأ في قاعدة البيانات: " . $stmt->error);
    }
    $order_id = $conn->insert_id;
    $stmt->close();

    // 2. إعداد بيانات الطلب لـ WPAY API (cURL)
    $callback_url = "https://yourdomain.com/wpay_callback.php?order_id=" . $order_id;
    $return_url = "https://yourdomain.com/thankyou.php?order_id=" . $order_id;

    $payload = [
        "merchant_id" => WPAY_MERCHANT_ID,
        "amount" => $amount_paid,
        "currency" => "USD",
        "order_reference" => "AD_ORDER_" . $order_id,
        "customer_phone" => $cust_phone,
        "callback_url" => $callback_url,
        "return_url" => $return_url
    ];

    // توليد التوقيع إن وجد (signature/hash حسب توثيق Whish)
    // $signature = hash_hmac('sha256', json_encode($payload), WPAY_SECRET_KEY);

    // 3. تنفيذ طلب cURL لـ Whish Pay
    $ch = curl_init(WPAY_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . WPAY_SECRET_KEY
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $res_data = json_decode($response, true);

    // 4. التوجيه لصفحة دفع WPAY التي تظهر في صورتك
    // إذا وفر الـ API رابط إعادة توجيه (redirect_url / payment_url):
    if (isset($res_data['payment_url']) && !empty($res_data['payment_url'])) {
        header("Location: " . $res_data['payment_url']);
        exit();
    } else {
        // في حال اختبار محلي أو لم يتوفر رابط ديناميكي فوري من الـ Sandbox، توجيه تجريبي أو إظهار خطأ
        // للتجربة المباشرة أو إذا كان الـ API يعيد رابط افتراضي:
        echo "<div style='font-family:Cairo; direction:rtl; text-align:center; margin-top:50px;'>";
        echo "<h3>تم إنشاء الطلب رقم #$order_id بنجاح</h3>";
        echo "<p>المبلغ المطلوب: <b>$amount_paid USD</b></p>";
        echo "<p style='color:red;'>ملاحظة: تأكد من تفعيل بيانات الـ Merchant ID الخاصة بك من Whish Pay لتوليد رابط WPay الفعلّي.</p>";
        echo "<a href='index.php' style='display:inline-block; margin-top:20px; padding:10px 20px; background:#2c3e50; color:#fff; text-decoration:none; border-radius:5px;'>العودة للمتجر</a>";
        echo "</div>";
    }
}
$conn->close();
