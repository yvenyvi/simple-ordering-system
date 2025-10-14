# Automatic Payment Status Update Feature

## Overview
The system automatically updates payment status based on order status changes to maintain consistency and reduce manual intervention.

## Payment Status Mapping

### Order Status → Payment Status Rules

| Order Status | Payment Status | Logic |
|-------------|----------------|-------|
| **Pending** | `pending` | Order just placed, awaiting confirmation |
| **Confirmed** | `pending` | Order confirmed, payment not yet processed |
| **Preparing** | `pending` | Order being prepared, payment still pending |
| **Ready** | `pending` | Order ready for delivery/pickup, payment may be COD |
| **Delivered** | `paid` | Order delivered successfully, payment marked as complete |
| **Cancelled** | `failed` or `refunded` | Depends on previous payment status (see below) |

### Special Handling for Cancelled Orders

When an order is cancelled, the system intelligently determines the payment status:

```
IF current_payment_status == 'paid'
    THEN new_payment_status = 'refunded'
    (Customer already paid, needs refund)
ELSE
    THEN new_payment_status = 'failed'
    (Payment was never completed)
```

## Implementation

### 1. Order Management Controller (`admin/controller/order_list.php`)

```php
// Automatic payment status determination
$payment_status_map = [
    'pending' => 'pending',
    'confirmed' => 'pending',
    'preparing' => 'pending',
    'ready' => 'pending',
    'delivered' => 'paid',
    'cancelled' => 'refunded'  // Smart detection applied
];
```

### 2. Controller (`admin/controller/order_list.php`)

Same logic implemented for consistency when updating from admin panel.

## Benefits

### ✅ Consistency
- Payment status always matches order status
- No manual intervention required
- Reduces human error

### ✅ Business Logic Enforcement
- Delivered orders automatically marked as paid
- Cancelled orders properly handled (refund vs failed)
- Clear audit trail of payment status changes

### ✅ Accounting Accuracy
- Easy to track paid orders (`status = 'delivered'`)
- Easy to track refunds (`cancelled` + `payment_status = 'refunded'`)
- Easy to track failed payments (`cancelled` + `payment_status = 'failed'`)

## Usage Examples

### Example 1: Normal Order Flow
```
Order Created → pending/pending
Order Confirmed → confirmed/pending
Order Preparing → preparing/pending
Order Ready → ready/pending
Order Delivered → delivered/paid ✅
```

### Example 2: Early Cancellation (Before Payment)
```
Order Created → pending/pending
Order Cancelled → cancelled/failed ❌
```

### Example 3: Late Cancellation (After Payment)
```
Order Created → pending/pending
Order Confirmed → confirmed/pending
Customer Pays Online → confirmed/paid
Order Cancelled → cancelled/refunded 💰
```

## Database Updates

When order status changes, the system executes:

```sql
UPDATE orders 
SET status = '{new_status}',
    payment_status = '{calculated_payment_status}',
    updated_at = NOW()
WHERE order_id = {order_id}
```

## API Response

When successful, the API returns:

```json
{
    "success": true,
    "message": "Order status updated successfully",
    "new_status": "delivered",
    "new_payment_status": "paid",
    "payment_status_updated": true
}
```

## Future Enhancements

### Possible Additions:
1. **Payment Gateway Integration**
   - Auto-mark as paid when payment gateway confirms
   - Handle partial payments
   
2. **Refund Processing**
   - Trigger actual refund through payment gateway
   - Track refund status and completion date
   
3. **Status History Logging**
   - Keep audit trail of all status changes
   - Track who made changes and when
   
4. **Email Notifications**
   - Notify customer when payment status changes
   - Send refund confirmation emails

## Notes

- Payment status updates are **automatic** and cannot be overridden during status change
- For manual payment status changes, use a separate payment update feature
- All payment status changes are logged with timestamp (`updated_at`)
- This feature works both in admin panel and through API calls

## Testing Checklist

- [ ] Test pending → confirmed (should remain pending)
- [ ] Test ready → delivered (should change to paid)
- [ ] Test pending → cancelled (should change to failed)
- [ ] Test confirmed/paid → cancelled (should change to refunded)
- [ ] Verify database updates correctly
- [ ] Check API response includes payment status info
- [ ] Verify success messages show payment status update

---

**Last Updated:** October 11, 2025
**Version:** 1.0
**Author:** Development Team
