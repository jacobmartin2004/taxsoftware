<?php
require_once 'conn.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_POST['company_id'];
    $tool_ids = $_POST['tool_ids'];
    $quantities = array_map('intval', explode(',', $_POST['quantities']));
    $state = strtolower(trim($_POST['state']));
    $date = date('Y-m-d');
    $type = 'invoice';
    $total = 0;
    $tax = 0;
    $discount_total = 0;
    $item_data = [];
    foreach ($tool_ids as $i => $tool_id) {
        $qty = $quantities[$i] ?? 1;
        $tool_res = $conn->query("SELECT * FROM tools WHERE id=$tool_id");
        $tool = $tool_res->fetch_assoc();
        $rate = $tool['rate'];
        $is_retailer = $tool['is_retailer'];
        $discount = $is_retailer ? 0.3 * $rate * $qty : 0;
        $amount = $rate * $qty - $discount;
        $total += $amount;
        $discount_total += $discount;
        $item_data[] = [
            'tool_id' => $tool_id,
            'qty' => $qty,
            'rate' => $rate,
            'discount' => $discount
        ];
    }
    if ($state === 'tamil nadu') {
        $tax = 0.18 * $total; // 9% CGST + 9% SGST
    } else {
        $tax = 0.18 * $total; // 18% IGST
    }
    $stmt = $conn->prepare("INSERT INTO invoices (company_id, date, type, total, tax) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issdd', $company_id, $date, $type, $total, $tax);
    $stmt->execute();
    $invoice_id = $conn->insert_id;
    foreach ($item_data as $item) {
        $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, tool_id, qty, rate, discount, tax) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiddd', $invoice_id, $item['tool_id'], $item['qty'], $item['rate'], $item['discount'], $tax/count($item_data));
        $stmt->execute();
    }
    header('Location: ../public/dashboard.php');
    exit();
}
?>