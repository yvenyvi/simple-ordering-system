# Payment Method & Status Logic

## Overview
The payment status automation now works differently based on the payment method chosen by the customer.

## Payment Methods
- **Cash**: Payment collected on delivery
- **Credit Card**: Payment processed online immediately  
- **Debit Card**: Payment processed online immediately
- **Online**: Digital payment processed immediately

## Payment Status Logic

### Cash Payment Method
For cash orders, payment status follows the traditional delivery-based model:

| Order Status | Payment Status | Reason |
|-------------|---------------|---------|
| pending | pending | Order just placed |
| confirmed | pending | Order confirmed but not delivered |
| preparing | pending | Food being prepared |
| ready | pending | Ready for delivery |
| delivered | **paid** | Cash collected on delivery |
| cancelled | failed/refunded | Depends on current payment status |

### Online/Card Payment Methods
For credit_card, debit_card, and online payments, payment is processed immediately:

| Order Status | Payment Status | Reason |
|-------------|---------------|---------|
| pending | **paid** | Payment processed when order placed |
| confirmed | **paid** | Payment already successful |
| preparing | **paid** | Payment already successful |
| ready | **paid** | Payment already successful |
| delivered | **paid** | Payment already successful |
| cancelled | **refunded** | Automatic refund for cancelled orders |

## Implementation Details

### Order Creation (`process_order.php`)
```php
// Set initial payment status based on payment method
$initial_payment_status = 'pending';
if (in_array($payment_method, ['credit_card', 'debit_card', 'online'])) {
    $initial_payment_status = 'paid';
}
```

### Status Updates (`update_order_status.php`)
The system checks the payment method and applies the appropriate logic:

1. **Cash Orders**: Follow the traditional model where payment happens on delivery
2. **Card/Online Orders**: Payment is immediately successful, only refunded if cancelled

### Business Logic Benefits

1. **Cash Orders**: 
   - Realistic payment tracking (payment on delivery)
   - Clear distinction between order status and payment collection

2. **Card/Online Orders**:
   - Immediate payment confirmation for better customer experience
   - Automatic refund processing for cancellations
   - Simplified order management (no need to wait for payment)

### Edge Cases Handled

1. **Cancelled Cash Orders**: 
   - If payment was pending → status becomes "failed"
   - If payment was already made → status becomes "refunded"

2. **Cancelled Card/Online Orders**:
   - Always refunded since payment was processed upfront

3. **Status Changes for Paid Orders**:
   - Card/online orders maintain "paid" status through all positive status changes
   - Only cancellation triggers a refund

## Database Schema Requirements

The `orders` table must include:
- `payment_method` ENUM('cash', 'credit_card', 'debit_card', 'online')
- `payment_status` ENUM('pending', 'paid', 'failed', 'refunded')

## API Response Format

The `update_order_status.php` API now returns:
```json
{
    "success": true,
    "message": "Order status updated successfully",
    "new_status": "confirmed",
    "new_payment_status": "paid", 
    "payment_status_updated": true,
    "payment_method": "credit_card"
}
```

This ensures transparency about payment method influence on status changes.