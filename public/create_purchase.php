<?php
require_once '../src/conn.php';

$companies = [];
$res = $conn->query("SELECT id, companyname, gstno, gsttype, address, state, district FROM companydata ORDER BY companyname");
while ($row = $res->fetch_assoc()) $companies[] = $row;

$tools = [];
$res = $conn->query("SELECT id, toolname, rate FROM tools ORDER BY toolname");
while ($row = $res->fetch_assoc()) $tools[] = $row;

$inv_res = $conn->query("SELECT MAX(bill) as maxbill FROM purchase");
$inv_row = $inv_res->fetch_assoc();
$next_bill = ($inv_row['maxbill'] ?? 0) + 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Purchase - Delvin Diamond Tools</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .invoice-box { max-width: 900px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .item-row { background: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 8px; }
        .totals-section { background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 15px; }
        .totals-section p { margin-bottom: 5px; font-size: 16px; }
        .totals-section .total-final { font-size: 20px; font-weight: bold; }
    </style>
</head>
<body>
<div class="invoice-box">
    <h2 class="text-center mb-1">DELVIN DIAMOND TOOL INDUSTRIES</h2>
    <p class="text-center mb-0">1/56, Easu Street, Somarasampettai (PO), Trichy - 620 102</p>
    <p class="text-center mb-0">GST NO: 33AAAPY1027F1Z3 | HSN CODE: 68042110</p>
    <hr>
    <h4 class="text-center">PURCHASE</h4>
    <form method="POST" action="../src/save_invoice.php">
        <input type="hidden" name="invoice_type" value="purchase">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Company Name:</label>
                <select class="form-control" id="companyname" name="company_id" required>
                    <option value="">Select Company</option>
                    <?php foreach ($companies as $c): ?>
                    <option value="<?php echo $c['id']; ?>"
                        data-gstno="<?php echo htmlspecialchars($c['gstno']); ?>"
                        data-gsttype="<?php echo htmlspecialchars($c['gsttype']); ?>"
                        data-address="<?php echo htmlspecialchars($c['address'] ?? ''); ?>"
                        data-state="<?php echo htmlspecialchars($c['state'] ?? ''); ?>"
                        data-district="<?php echo htmlspecialchars($c['district'] ?? ''); ?>"
                        data-name="<?php echo htmlspecialchars($c['companyname']); ?>">
                        <?php echo htmlspecialchars($c['companyname']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Bill No:</label>
                <input type="number" class="form-control" name="bill" value="<?php echo $next_bill; ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">GST No:</label>
                <input type="text" class="form-control" id="gst_no" name="gst_no" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">State:</label>
                <input type="text" class="form-control" id="company_state" name="company_state" readonly>
                <input type="hidden" id="gst_type" name="gst_type" value="">
            </div>
            <div class="col-md-4">
                <label class="form-label">Date:</label>
                <input type="date" class="form-control" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"><strong>Address:</strong></div>
            <div class="col-md-9"><span id="company_address">-</span></div>
        </div>
        <hr>
        <h5>Items</h5>
        <div id="items-container"></div>
        <button type="button" class="btn btn-success btn-sm mb-3" onclick="addItem()">+ Add Item</button>
        <div class="totals-section">
            <div class="row">
                <div class="col-md-6">
                    <p>Taxable Amount: <strong>&#8377;<span id="disp_taxable">0.00</span></strong></p>
                    <p id="cgst_row" style="display:none;">CGST (9%): &#8377;<span id="disp_cgst">0.00</span></p>
                    <p id="sgst_row" style="display:none;">SGST (9%): &#8377;<span id="disp_sgst">0.00</span></p>
                    <p id="igst_row" style="display:none;">IGST (18%): &#8377;<span id="disp_igst">0.00</span></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="total-final">TOTAL: &#8377;<span id="disp_total">0.00</span></p>
                </div>
            </div>
        </div>
        <input type="hidden" name="taxable_amount" id="hid_taxable" value="0">
        <input type="hidden" name="cgst" id="hid_cgst" value="0">
        <input type="hidden" name="sgst" id="hid_sgst" value="0">
        <input type="hidden" name="igst" id="hid_igst" value="0">
        <input type="hidden" name="total" id="hid_total" value="0">
        <input type="hidden" name="company_name" id="hid_cname" value="">
        <div class="mt-3 text-center">
            <button type="submit" class="btn btn-primary btn-lg">Create Purchase</button>
            <a href="dashboard.php" class="btn btn-secondary btn-lg">Cancel</a>
        </div>
    </form>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
var toolsData = <?php echo json_encode($tools); ?>;
var itemCount = 0;
$(document).ready(function(){
    $('#companyname').select2({placeholder: "Select or search company", allowClear: true});
    $('#companyname').on('change', function(){
        var opt = $(this).find(':selected');
        $('#gst_no').val(opt.data('gstno') || '');
        $('#gst_type').val(opt.data('gsttype') || '');
        $('#company_state').val(opt.data('state') || '');
        $('#company_address').text((opt.data('address') || '') + ', ' + (opt.data('district') || '') + ', ' + (opt.data('state') || ''));
        $('#hid_cname').val(opt.data('name') || '');
        recalculate();
    });
    addItem();
});
function addItem() {
    itemCount++;
    var toolOptions = '<option value="">Select Tool</option>';
    toolsData.forEach(function(t){
        toolOptions += '<option value="'+t.id+'" data-rate="'+t.rate+'">'+t.toolname+' (Rs.'+parseFloat(t.rate).toFixed(2)+')</option>';
    });
    var html = '<div class="item-row row align-items-end" id="item-'+itemCount+'">' +
        '<div class="col-md-3"><label class="form-label">Tool</label><select class="form-control tool-select" name="items['+itemCount+'][tool_id]" onchange="toolSelected(this)" required>'+toolOptions+'</select></div>' +
        '<div class="col-md-2"><label class="form-label">Qty</label><input type="number" class="form-control item-qty" name="items['+itemCount+'][qty]" value="1" min="1" onchange="recalculate()" required></div>' +
        '<div class="col-md-2"><label class="form-label">Rate</label><input type="number" class="form-control item-rate" name="items['+itemCount+'][rate]" step="0.01" onchange="recalculate()" required></div>' +
        '<div class="col-md-2"><label class="form-label">Discount</label><select class="form-select item-disc-yn" onchange="toggleDiscount(this, '+itemCount+')"><option value="no">No</option><option value="yes">Yes (30%)</option></select><input type="number" class="form-control item-disc mt-1" id="disc-'+itemCount+'" name="items['+itemCount+'][discount_pct]" value="30" step="0.01" style="display:none" onchange="recalculate()"></div>' +
        '<div class="col-md-2"><label class="form-label">Amount</label><input type="text" class="form-control item-amount" readonly></div>' +
        '<div class="col-md-1"><button type="button" class="btn btn-danger btn-sm mt-4" onclick="removeItem('+itemCount+')">X</button></div>' +
        '</div>';
    $('#items-container').append(html);
}
function toolSelected(sel) {
    var rate = $(sel).find(':selected').data('rate') || 0;
    $(sel).closest('.item-row').find('.item-rate').val(parseFloat(rate).toFixed(2));
    recalculate();
}
function toggleDiscount(sel, idx) {
    if ($(sel).val() === 'yes') { $('#disc-'+idx).show(); } else { $('#disc-'+idx).hide(); }
    recalculate();
}
function removeItem(idx) { $('#item-'+idx).remove(); recalculate(); }
function recalculate() {
    var taxable = 0;
    $('.item-row').each(function(){
        var qty = parseFloat($(this).find('.item-qty').val()) || 0;
        var rate = parseFloat($(this).find('.item-rate').val()) || 0;
        var amount = qty * rate;
        var discYN = $(this).find('.item-disc-yn').val();
        var discPct = parseFloat($(this).find('.item-disc').val()) || 0;
        if (discYN === 'yes') { amount -= amount * discPct / 100; }
        $(this).find('.item-amount').val(amount.toFixed(2));
        taxable += amount;
    });
    var gstType = $('#gst_type').val();
    var cgst=0, sgst=0, igst=0;
    if (gstType === 'tngst') { cgst = taxable*0.09; sgst = taxable*0.09; $('#cgst_row').show(); $('#sgst_row').show(); $('#igst_row').hide(); }
    else if (gstType === 'igst') { igst = taxable*0.18; $('#cgst_row').hide(); $('#sgst_row').hide(); $('#igst_row').show(); }
    else { $('#cgst_row').hide(); $('#sgst_row').hide(); $('#igst_row').hide(); }
    var total = taxable + cgst + sgst + igst;
    $('#disp_taxable').text(taxable.toFixed(2));
    $('#disp_cgst').text(cgst.toFixed(2));
    $('#disp_sgst').text(sgst.toFixed(2));
    $('#disp_igst').text(igst.toFixed(2));
    $('#disp_total').text(Math.round(total).toFixed(2));
    $('#hid_taxable').val(taxable.toFixed(1));
    $('#hid_cgst').val(cgst.toFixed(1));
    $('#hid_sgst').val(sgst.toFixed(1));
    $('#hid_igst').val(igst.toFixed(1));
    $('#hid_total').val(Math.round(total));
    $('#hid_cname').val($('#companyname').find(':selected').data('name') || '');
}
</script>
</body>
</html>