<?php
require_once 'config.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// التحقق من صحة الحدث ومعرف الطلب
if (isset($data['order_reference']) && isset($data['status'])) {
    if ($data['status'] === 'SUCCESS' || $data['status'] === 'paid') {
        // استخراج رقم الـ ID من order_reference مثل AD_ORDER_15
        str_replace('AD_ORDER_', '', $data['order_reference'], $order_id);
        
        $order_id = intval($order_id);
        $tx_ref = $data['transaction_id'] ?? 'WPAY_AUTO';

        $stmt = $conn->prepare("UPDATE customer_ads SET status = 'approved', wish_ref = ? WHERE id = ?");
        $stmt->bind_param("si", $tx_ref, $order_id);
        $stmt->execute();
        $stmt->close();
        
        http_response_code(200);
        echo json_encode(["status" => "ok"]);
        exit();
    }
}
http_response_code(400);
echo json_encode(["status" => "error"]);
