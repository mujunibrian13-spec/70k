# Loan Payment Approval Workflow

## Overview
The system has been updated to require **administrator approval** for all loan payments made by members. This ensures proper control and verification of payment transactions.

## Workflow Steps

### 1. Member Submits Payment
- Member navigates to **Pay Loan** page
- Enters payment amount, method, and optional receipt/notes
- Clicks "Record Payment"
- Payment is saved with status **PENDING**
- Member sees a confirmation message indicating payment is awaiting admin approval

### 2. Member Sees Pending Status
- On the Pay Loan page, members can see a section showing all their **pending payment approvals**
- Lists each pending payment with:
  - Payment amount
  - Submission date
  - Current status (Pending)
- Message informs them the administrator will review shortly

### 3. Administrator Reviews Payments
- Admin navigates to **Admin Dashboard**
- New card shows "Payment Approvals" count with pending payment count
- Admin clicks on the "Payment Approvals" card or uses navigation to go to **Approve Payments** page

### 4. Administrator Takes Action
- Admin sees all loan payments organized by status (Pending → Approved → Rejected)
- For each pending payment, admin can:
  - **Approve**: Accepts the payment
    - Updates loan balance
    - Marks loan as cleared if balance reaches zero
    - Distributes interest to all members
    - Payment status changes to APPROVED
  - **Reject**: Denies the payment
    - Payment status changes to REJECTED
    - Loan balance remains unchanged
    - No interest distributed

### 5. Automatic Actions on Approval
When admin approves a payment:
1. Loan `remaining_balance` is updated
2. Loan `amount_paid` is updated
3. Loan `status` is automatically set to:
   - `active` if balance remains
   - `cleared` if balance reaches zero
4. Payment record is marked as APPROVED
5. Interest is immediately distributed to all members based on their savings ratio
6. Transaction log is created for audit trail

### 6. Member Notification
- Member can check payment status on Pay Loan page
- Once approved, they see:
  - Updated loan balance
  - Updated loan status
  - Transaction history showing payment

## Database Changes

The system uses the existing `loan_payments` table:

```sql
CREATE TABLE loan_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    member_id INT NOT NULL,
    payment_amount DECIMAL(15, 2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'mobile_money') DEFAULT 'cash',
    receipt_number VARCHAR(50),
    notes TEXT,
    status ENUM('pending', 'approved') DEFAULT 'pending',  -- NEW: 'rejected' added
    payment_date DATE NOT NULL,
    approved_by INT,
    approval_date DATETIME,
    created_at DATETIME,
    FOREIGN KEY (loan_id) REFERENCES loans(id),
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);
```

**Status values:**
- `pending`: Payment submitted, awaiting admin review
- `approved`: Payment has been approved and processed
- `rejected`: Payment has been rejected

## New Files

### 1. `approve_payments.php`
Admin page for reviewing and approving/rejecting loan payments.

**Features:**
- View all pending, approved, and rejected payments
- See member details (name, email, phone)
- See loan and payment details
- Approve or reject pending payments with confirmation dialog
- Filter by payment status
- Responsive table design

## Updated Files

### 1. `pay_loan.php`
**Changes:**
- Added pending payments display at top of page
- Shows member's pending payment requests
- Updated success message to indicate pending admin approval
- Updated information alert to explain the approval process
- Payment button now says "Record Payment" instead of "Pay Loan"

### 2. `admin.php`
**Changes:**
- Added new "Payment Approvals" stat card
- Shows count of pending payment approvals
- Card is clickable and links to approve_payments.php
- Navigation includes "Approve Payments" link
- Updated "Pending Applications" label to clarify it's for loan applications

### 3. `config/functions.php`
**Existing functions:**
- `recordLoanPayment()`: Already creates payments with status 'pending' instead of immediately updating loans
- `approveLoanPayment()`: Already implemented to handle approval, balance updates, and interest distribution

**No changes needed** - functions were already designed for this workflow

## Interest Distribution

Interest is distributed **ONLY WHEN** a payment is approved:

1. Interest amount = Payment amount × LOAN_INTEREST_RATE (2% default)
2. Interest is distributed to all active members based on their **savings ratio**
3. Savings ratio = Member's savings ÷ Total group savings
4. Each member receives: Total interest × Their savings ratio

**Example:**
- Payment approved: 100,000 UGX
- Interest generated: 100,000 × 0.02 = 2,000 UGX
- Total group savings: 1,000,000 UGX
- Member A has 200,000 UGX (20% ratio) → receives 2,000 × 0.20 = 400 UGX
- Member B has 300,000 UGX (30% ratio) → receives 2,000 × 0.30 = 600 UGX
- Etc.

## User Permissions

### Members Can:
- Submit loan payments
- View their pending payments
- View approved payment history
- See updated loan balance after approval

### Admins Can:
- View all pending payments
- View all approved payments
- View all rejected payments
- Approve pending payments
- Reject pending payments
- See payment details (amount, method, receipt, notes)
- See member information

## Testing the Workflow

### Step 1: Login as Member
1. Login to member account
2. Go to **Pay Loan** page
3. Submit a payment for an active loan
4. Confirm message says "pending admin approval"
5. See payment in "Pending Payment Approvals" section

### Step 2: Login as Admin
1. Login to admin account
2. Go to **Admin Dashboard**
3. See new "Payment Approvals" card with pending count
4. Click card or "Approve Payments" in navigation
5. See the pending payment
6. Click "Approve" and confirm

### Step 3: Verify Results
1. Check `loan_payments` table - status should be 'approved'
2. Check `loans` table - `remaining_balance` should be updated
3. Check `members` table - `savings_amount` should be increased (interest distributed)
4. Check `transactions` table - should have new entries for:
   - Loan payment
   - Interest earned for each member

## Configuration

### Default Interest Rate
- Set in `config/db_config.php`: `LOAN_INTEREST_RATE = 0.02` (2%)
- Can be modified as needed

### Payment Methods
- Cash
- Bank Transfer
- Mobile Money

## Security & Audit

All payment approvals are logged:
- `loan_payments` table: who approved, when
- `transactions` table: payment and interest transactions
- `audit_log` table: all admin actions (if implemented)

## Error Handling

The system handles:
- Duplicate approvals (payment already approved)
- Duplicate rejections (payment already rejected)
- Deleted loans/members (validation)
- Payment exceeds remaining balance (validation on submission)
- Negative balances (prevented)

## Future Enhancements

Possible improvements:
1. Email notifications to members when payment is approved/rejected
2. Admin comments on rejected payments
3. Payment deadline enforcement
4. Automatic rejection if payment not approved within X days
5. Payment schedule/installment plan support
6. Payment reconciliation reports
