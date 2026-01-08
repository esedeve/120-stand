<?php
/**
 * Take Order Page Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$page_title = 'Take Order - 120 Stand Inventory';
$current_user = Stand120_Auth::get_current_user_data();
$menu_items = Stand120_Database::get_menu_items();

include STAND120_PLUGIN_DIR . 'templates/partials/header.php';
?>

<!-- Staff Info Bar -->
<div class="staff-info-bar">
    <div class="staff-info">
        <div class="staff-avatar">
            <?php echo strtoupper(substr($current_user['display_name'], 0, 1)); ?>
        </div>
        <div class="staff-details">
            <h4><?php echo esc_html($current_user['display_name']); ?></h4>
            <span><?php echo ucfirst($current_user['role']); ?></span>
        </div>
    </div>
    <div class="datetime-display">
        <div class="date-display">
            <i class="fas fa-calendar-alt"></i>
            <span class="date-text"><?php echo date('l, F j, Y'); ?></span>
        </div>
        <div class="time-display">
            <i class="fas fa-clock"></i>
            <span class="digital-clock">--:--:--</span>
        </div>
    </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
    <h1 class="page-title" style="margin-bottom: 0;">
        <i class="fas fa-cart-plus"></i>
        Take Order
    </h1>
    <a href="<?php echo home_url('/120-stand/take-order-history/'); ?>" class="history-btn">
        <i class="fas fa-history"></i> View Order History
    </a>
</div>

<!-- Order Form -->
<form id="orderForm">
    <!-- Order Items Table -->
    <div class="glass-card">
        <h3 style="margin-bottom: 16px; color: var(--primary-color);">
            <i class="fas fa-list"></i> Order Items
        </h3>
        
        <div class="table-responsive">
            <table class="table" id="orderTable">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price (₦)</th>
                        <th>Quantity</th>
                        <th>Total (₦)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_items as $item): ?>
                    <tr data-product-id="<?php echo $item->id; ?>">
                        <td><?php echo esc_html($item->name); ?></td>
                        <td class="price-cell formatted-number">₦<?php echo number_format($item->price, 0); ?></td>
                        <td>
                            <input type="number" class="table-input qty-input" value="0" min="0" data-price="<?php echo $item->price; ?>">
                        </td>
                        <td class="total-cell formatted-number">₦0</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="text-align: right; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-glass);">
            <span style="font-size: 1.1rem; color: var(--text-secondary);">Subtotal: </span>
            <span id="subtotal" class="formatted-number" style="font-size: 1.3rem; font-weight: 600;">₦0</span>
        </div>
    </div>
    
    <!-- Payment Section -->
    <div class="glass-card">
        <h3 style="margin-bottom: 20px; color: var(--primary-color);">
            <i class="fas fa-credit-card"></i> Payment Details
        </h3>
        
        <div class="payment-section">
            <!-- Payment Method -->
            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <div class="payment-method-select">
                    <label class="payment-option selected">
                        <input type="radio" name="payment_method" value="cash" checked>
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Cash</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="transfer">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transfer/Card</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="both">
                        <i class="fas fa-coins"></i>
                        <span>Both</span>
                    </label>
                </div>
            </div>
            
            <!-- Cash Amount -->
            <div class="form-group" id="cashAmountGroup">
                <label class="form-label" for="cashAmount">Cash Amount (₦)</label>
                <input type="text" id="cashAmount" class="form-control number-input" placeholder="0">
            </div>
            
            <!-- Transfer Amount -->
            <div class="form-group" id="transferAmountGroup" style="display: none;">
                <label class="form-label" for="transferAmount">Transfer/Card Amount (₦)</label>
                <input type="text" id="transferAmount" class="form-control number-input" placeholder="0" disabled>
            </div>
            
            <!-- Delivery Fee -->
            <div class="form-group">
                <label class="form-label" for="deliveryFee">Delivery Fee (₦)</label>
                <input type="text" id="deliveryFee" class="form-control number-input" placeholder="0">
            </div>
        </div>
        
        <!-- Payment Confirmation -->
        <div class="confirmation-warning">
            <label class="form-check">
                <input type="checkbox" id="paymentConfirmed">
                <span class="form-check-label">
                    <i class="fas fa-exclamation-triangle"></i>
                    I confirm that payment has been received and verified on the POS system before submitting this order.
                </span>
            </label>
        </div>
    </div>
    
    <!-- Grand Total -->
    <div class="grand-total-section">
        <span class="grand-total-label">
            <i class="fas fa-receipt"></i> Grand Total
        </span>
        <span id="grandTotal" class="grand-total-value">₦0</span>
    </div>
    
    <!-- Submit Button -->
    <div style="margin-top: 24px; text-align: center;">
        <button type="button" id="submitOrder" class="btn btn-primary btn-lg">
            <i class="fas fa-check-circle"></i> Submit Order
        </button>
    </div>
</form>

<script>
    $(document).ready(function() {
        TakeOrder.init();
        
        // Payment method toggle
        $('input[name="payment_method"]').on('change', function() {
            $('.payment-option').removeClass('selected');
            $(this).closest('.payment-option').addClass('selected');
            
            const method = $(this).val();
            
            if (method === 'cash') {
                $('#cashAmountGroup').show();
                $('#cashAmount').prop('disabled', false);
                $('#transferAmountGroup').hide();
                $('#transferAmount').prop('disabled', true).val('');
            } else if (method === 'transfer') {
                $('#cashAmountGroup').hide();
                $('#cashAmount').prop('disabled', true).val('');
                $('#transferAmountGroup').show();
                $('#transferAmount').prop('disabled', false);
            } else {
                $('#cashAmountGroup').show();
                $('#cashAmount').prop('disabled', false);
                $('#transferAmountGroup').show();
                $('#transferAmount').prop('disabled', false);
            }
        });
    });
</script>

<?php include STAND120_PLUGIN_DIR . 'templates/partials/footer.php'; ?>
