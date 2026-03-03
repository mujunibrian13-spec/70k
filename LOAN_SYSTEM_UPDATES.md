# Loan System Updates - Summary

## Changes Implemented

### 1. Borrowing from Group Savings Pool ✅

**Before:**
- Members could only borrow up to 2x their individual savings
- Limited by their own savings amount

**After:**
- Members can borrow from total group savings
- No limit based on individual savings
- Limited only by available group pool: (Total Savings - Outstanding Loans)

**Example:**
- Member with 1M savings can now borrow 10M if group has 50M with only 10M borrowed
- Available pool = 50M - 10M = 40M
- Member can borrow up to 40M (not just 2M)

---

### 2. Loan Payment System ✅

**New Feature: Pay Loan Page**

**Location:** `pay_loan.php`

**Features:**
- View all active loans
- Make payments towards any active loan
- Track remaining balance
- See payment progress
- Record payment method (cash, bank transfer, mobile money)
- Optional receipt number and notes

**How it works:**
1. Navigate to "Pay Loan" (in member navigation)
2. Select loan to pay
3. Enter payment amount
4. Choose payment method
5. Click "Record Payment"

---

### 3. Automatic Loan Clearing ✅

**New Status: CLEARED**

**What happens:**
- When remaining balance = 0
- Status automatically changes to CLEARED
- No manual intervention needed
- Happens immediately after payment

**Member can then:**
- Apply for new loan
- Borrow again from group pool
- Full loan history is preserved

---

### 4. Updated Loan Status Workflow ✅

**New Flow:**
```
PENDING → APPROVED → ACTIVE → CLEARED → COMPLETED
                        ↓
                     REJECTED
```

**New Status: CLEARED**
- Loan fully paid (principal + interest)
- Member can borrow again
- Cannot be reversed
- Read-only (no further changes)

---

## Code Changes

### Functions (config/functions.php)

#### Updated: getMaxBorrowableAmount()
```php
// OLD: Limited to min(member_savings, available_pool)
// NEW: Limited only to available_pool (group savings - outstanding loans)
$available_pool = $total_savings - $total_borrowed;
return max(0, $available_pool);
```

#### New: recordLoanPayment()
```php
// Records payment and updates loan status
// Automatically changes status to 'cleared' when balance = 0
// Logs transaction for audit trail
```

---

### Pages

#### pay_loan.php (NEW)
- Displays all active loans for member
- Payment form for each loan
- Shows remaining balance and progress
- Automatic status change on payment

#### loans.php (UPDATED)
- Updated info text about group borrowing
- "Max Borrowing" now shows group pool available
- Info about clearing loans to borrow again

#### index.php (UPDATED)
- Added "Pay Loan" link in navigation
- Removed "Add Savings" button (admin only)

---

### Database Schema

#### database.sql (UPDATED)
```sql
-- Added 'cleared' to loan status enum
status ENUM('pending', 'approved', 'active', 'cleared', 'completed', 'rejected')
```

#### database_simplified.sql (UPDATED)
```sql
-- Same change for simpler version
status ENUM('pending', 'approved', 'active', 'cleared', 'completed', 'rejected')
```

---

## User Journey

### Member A - Borrowing More Than Savings

**Step 1: Check Savings**
- Member A has 2,000,000 UGX savings
- Goes to Loans page

**Step 2: Check Borrowing Limit**
- Group has 100M total savings
- Outstanding loans: 30M
- Available: 70M
- Max Borrowable: 70M (not limited to 2M!)

**Step 3: Apply for Loan**
- Member A applies for 10,000,000 UGX
- Status: PENDING

**Step 4: Admin Approves**
- Admin views pending loans
- Approves the 10M loan
- Status: APPROVED

**Step 5: Loan Becomes Active**
- Status: ACTIVE
- Member sees "Pay Loan" button
- Can make payments

**Step 6: Member Pays**
- Goes to "Pay Loan" page
- Makes several payments
- Progress bar shows payment status
- Remaining balance decreases

**Step 7: Final Payment**
- Makes final payment
- Remaining balance = 0
- Status automatically → CLEARED
- Transaction logged

**Step 8: Borrow Again**
- Member can now apply for new loan
- New max borrowable recalculated
- Process repeats!

---

## Loan Payment Process

### Payment Record Fields
- Loan ID
- Member ID
- Payment Amount
- Payment Method (cash/transfer/mobile)
- Receipt Number (optional)
- Notes (optional)
- Payment Date (auto)

### Validation
- Payment amount must be > 0
- Payment amount ≤ remaining balance
- Payment method required
- Loan must be ACTIVE (not already cleared)

### Automatic Updates
- Remaining balance = remaining_balance - payment_amount
- Amount paid = total_payable - remaining_balance
- Status = 'cleared' if remaining_balance <= 0
- Transaction logged

---

## Admin View Changes

### Loan Approval Table

**Now Shows:**
- Loan Status column
- All loan statuses (not just pending)
- Color-coded badges
- Locked buttons for processed loans

**Status Colors:**
- PENDING: Yellow (action available)
- APPROVED: Blue (locked)
- ACTIVE: Green (locked)
- CLEARED: Purple (locked)
- REJECTED: Red (locked)
- COMPLETED: Gray (locked)

---

## Documentation Files

Created comprehensive guides:

1. **LOAN_BORROWING_RULES.md**
   - Complete borrowing rules
   - Payment process
   - Status workflow
   - Use cases and examples

2. **LOAN_SYSTEM_UPDATES.md** (this file)
   - Summary of all changes
   - Code modifications
   - User journey

---

## Migration Guide

### For Existing Database

**If you already have the database:**

1. Add 'cleared' status to loans table:
```sql
ALTER TABLE loans 
MODIFY status ENUM('pending', 'approved', 'active', 'cleared', 'completed', 'rejected');
```

2. No existing data needs to change
3. New loans will use updated status system

**If you're creating new database:**

1. Use updated `database.sql` or `database_simplified.sql`
2. 'cleared' status already included
3. Ready to use immediately

---

## Testing Checklist

- [ ] Member can borrow more than individual savings
- [ ] Max borrowable shows group pool available
- [ ] Member can access "Pay Loan" page
- [ ] Payment reduces remaining balance
- [ ] Status changes to CLEARED automatically
- [ ] Member can borrow again after clearing
- [ ] Payment history is recorded
- [ ] Admin sees CLEARED status in loans table
- [ ] Admin cannot modify cleared loans
- [ ] Interest is included in total payable

---

## Feature Comparison

| Feature | Before | After |
|---------|--------|-------|
| Max Borrowable | min(savings, pool) | Available pool only |
| Can Borrow > Savings? | No | Yes |
| Loan Clearing | Manual | Automatic |
| Reborrow After Clear | Must wait | Immediate |
| Payment Tracking | No | Yes |
| Payment Page | No | Yes |
| Loan Status | 5 types | 6 types (+ cleared) |

---

## Advantages of New System

✅ **More Flexible Borrowing**
- Members not limited to individual savings
- Can access more credit if group savings available

✅ **Automatic Clearing**
- No manual admin work
- Instant status change
- No data entry errors

✅ **Payment Tracking**
- Full payment history
- Progress visualization
- Audit trail

✅ **Better Group Management**
- Encourages group saving
- More loans available
- Better utilization of group funds

✅ **Faster Reborrowing**
- Clear debt → Borrow again immediately
- No waiting periods
- Continuous access to credit

---

**Status:** ✅ Complete  
**Version:** 2.0  
**Date:** March 2026
