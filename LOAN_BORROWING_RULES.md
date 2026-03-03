# Loan Borrowing and Payment Rules

## Overview
Members can borrow from the group savings pool and must clear their loans to borrow again.

---

## Borrowing Rules

### Eligibility
- ✅ Must have saved at least 5,000 UGX (MANDATORY_SAVINGS)
- ✅ Can borrow at any time as long as they meet mandatory savings requirement

### Maximum Borrowing Limit

**Formula:**
```
Max Borrowable = Group Total Savings - Total Outstanding Loans
```

**Key Points:**
- ✅ NOT limited to individual member's savings
- ✅ Can borrow MORE than individual savings
- ✅ Limited only by available group savings pool
- ✅ Available for all members equally (first-come basis)

### Example Scenario

**Group Status:**
- Total Group Savings: 50,000,000 UGX
- Total Outstanding Loans: 20,000,000 UGX
- Available Pool: 30,000,000 UGX

**Member A:**
- Individual Savings: 2,000,000 UGX
- Max Borrowable: 30,000,000 UGX (NOT limited to 2,000,000)
- Member A can borrow up to 30,000,000 UGX!

**Member B:**
- Individual Savings: 500,000 UGX
- Max Borrowable: Still 30,000,000 UGX
- Member B can also borrow up to 30,000,000 UGX

---

## Loan Status Workflow

```
PENDING → APPROVED → ACTIVE → CLEARED → COMPLETED
                         ↓
                      REJECTED
```

### Status Meanings:

1. **PENDING** ⏳
   - Member has applied for loan
   - Awaiting admin approval
   - No payment button available

2. **APPROVED** ✅
   - Admin approved the loan
   - Loan will become ACTIVE
   - Member can start making payments

3. **ACTIVE** 🔄
   - Loan is disbursed to member
   - Member is making payments
   - Payment button available

4. **CLEARED** 🎉
   - Loan fully paid (principal + interest)
   - Remaining balance: 0
   - Member can apply for NEW loan
   - Cannot be reversed

5. **COMPLETED** ✔️
   - Final status after all processes
   - Loan fully archived
   - Cannot make further changes

6. **REJECTED** ❌
   - Admin rejected the loan application
   - Member cannot borrow this amount
   - Can apply again with new application

---

## Loan Payment Rules

### Making Payments

**How to pay:**
1. Navigate to **"Pay Loan"** page
2. Select the loan to pay
3. Enter payment amount (up to remaining balance)
4. Select payment method
5. Submit payment

**Payment Methods:**
- Cash
- Bank Transfer  
- Mobile Money

### Loan Clearing

**Automatic Status Change:**
- When remaining balance = 0
- Status automatically changes to **CLEARED**
- No manual intervention needed
- Happens immediately after payment

**Requirements to Clear:**
- Pay full total payable amount (principal + interest)
- Interest = Loan Amount × Interest Rate × Number of Months
- Example: 10,000,000 UGX at 2% monthly for 12 months

### After Clearing

**What can member do:**
- ✅ Apply for a new loan
- ✅ Has access to "Apply for Loan" button
- ✅ Maximum borrowable recalculated
- ✅ Can borrow again from group savings

**What is locked:**
- ❌ Cannot make payments on cleared loan
- ❌ Cleared loan is read-only
- ❌ No further modifications possible

---

## Interest Calculation

### Monthly Interest Rate
- 2% per month (configurable)

### Total Payable Calculation
```
Total Payable = Loan Amount × (1 + (Interest Rate × Number of Months))
```

### Example
- Loan: 10,000,000 UGX
- Interest Rate: 2% monthly
- Duration: 12 months
- Total Interest: 10,000,000 × 0.02 × 12 = 2,400,000 UGX
- Total Payable: 12,400,000 UGX

### Interest Distribution
- 2% interest goes to group's savings pool
- Distributed to all members based on savings ratio
- Distributed monthly on the 1st of month

---

## Member Borrowing Timeline

### Timeline Example

**Month 1:**
1. Member has 2,000,000 UGX savings
2. Group has 50,000,000 UGX total
3. Member applies for 5,000,000 UGX loan
4. Status: PENDING (waiting for admin)

**Month 1 (Day 5):**
1. Admin approves loan
2. Status: APPROVED

**Month 1 (Day 6):**
1. Status changes to: ACTIVE
2. Member receives 5,000,000 UGX
3. Loan payment page shows remaining balance

**Months 1-12:**
1. Member makes monthly payments
2. Each payment reduces remaining balance
3. Status stays ACTIVE while balance > 0

**Month 12 (Final Payment):**
1. Member pays final amount
2. Remaining balance = 0
3. Status automatically changes to: CLEARED
4. Member can now apply for new loan!

---

## Restrictions on Cleared Members

**Member CANNOT:**
- ❌ Pay more on cleared loan
- ❌ Modify cleared loan details
- ❌ Reverse loan clearing

**Member CAN:**
- ✅ View cleared loan history
- ✅ See payment records
- ✅ Apply for new loan
- ✅ See in reports and statements

---

## Admin Dashboard - Loan Management

### Viewing Loans
- Shows all loans (pending, approved, active, cleared, rejected)
- Sorted by status (pending first)
- Displays remaining balance

### Actions Available
- Approve PENDING loans
- Reject PENDING loans
- View all loan details
- NO action on ACTIVE/CLEARED loans (locked)

---

## Loan Payment Page Features

### For Each Active Loan
- Loan amount (principal)
- Interest rate
- Total payable
- Remaining balance
- Progress bar (% paid)

### Payment Form
- Payment amount (max = remaining balance)
- Payment method (cash/transfer/mobile)
- Receipt number (optional)
- Notes (optional)

### Automatic Features
- Progress bar updates
- Remaining balance recalculates
- Status changes to CLEARED automatically
- Transaction logged

---

## Use Cases

### Case 1: Member Borrows > Their Savings
1. Member A has 2M in savings
2. Member A borrows 8M (4x their savings!)
3. This is ALLOWED because group has 50M available
4. Member A must pay 8M + interest to clear

### Case 2: Multiple Members Borrowing
1. Member A borrows 10M
2. Member B borrows 15M
3. Member C wants 20M
4. Available = 50M - 10M - 15M = 25M
5. Member C can borrow up to 25M

### Case 3: Clearing and Re-borrowing
1. Member X has active 5M loan
2. Member X makes all payments
3. Remaining balance = 0
4. Status = CLEARED
5. Member X can immediately apply for new loan
6. No waiting period!

### Case 4: Loan Denied Then Reapply
1. Member Y applies for 30M (too much)
2. Admin rejects
3. Member Y applies for 15M
4. Admin can approve this new application
5. Previous rejected loan stays in history

---

## Database Changes

### New Loan Status
- Added: `'cleared'` to loan status ENUM
- Automatically assigned when remaining_balance = 0

### Loan Payment Recording
- Tracks payment_amount
- Records payment_method
- Stores receipt_number
- Logs payment_date
- Updates remaining_balance
- Updates amount_paid total

---

## API Reference

### Functions

#### getMaxBorrowableAmount($conn, $member_id)
Returns the maximum amount a member can borrow
- Input: Database connection, member ID
- Output: Float (maximum borrowable amount)
- Logic: Group savings - Outstanding loans

#### recordLoanPayment($conn, $loan_id, $payment_amount, $payment_method, $receipt_number, $notes)
Records a loan payment and updates status
- Input: Loan ID, payment amount, method, receipt, notes
- Output: Array with success status and message
- Auto-clears loan when balance = 0

---

## Testing Checklist

- [ ] Member can borrow more than their individual savings
- [ ] Max borrowable = group savings - outstanding loans
- [ ] Member can make partial payments
- [ ] Loan status changes to CLEARED when fully paid
- [ ] Member can apply for new loan after clearing
- [ ] Payment page shows correct remaining balance
- [ ] Progress bar updates after payment
- [ ] Admin cannot approve already approved loans
- [ ] Admin cannot reject non-pending loans
- [ ] Interest is correctly calculated
- [ ] Payment history is recorded

---

**Version:** 2.0  
**Last Updated:** March 2026  
**Status:** ✅ Active
