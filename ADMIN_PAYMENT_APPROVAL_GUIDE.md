# Admin Payment Approval - Quick Reference

## Accessing Payment Approvals

### From Dashboard
1. Login as Admin
2. Look for **"Payment Approvals"** stat card (red/coral colored)
3. The card shows the number of pending payments
4. Click the card to go to approval page

### From Navigation
1. Click **"Approve Payments"** in the navbar

## Payment Approval Page

### Layout
- **Header**: Shows "Approve Loan Payments" with description
- **Table**: Lists all payments (Pending, Approved, Rejected)
- **Filters**: Status badges show payment status
- **Action Buttons**: Approve or Reject for pending payments

### Payment Information Columns
| Column | Shows |
|--------|-------|
| Member | Full name, email, phone |
| Loan Amount | Original loan amount (in red) |
| Payment Amount | Amount being paid (in yellow) |
| Remaining Balance | Balance after approval (in green) |
| Method | Cash, Bank Transfer, or Mobile Money |
| Date | When payment was submitted |
| Status | Pending, Approved, or Rejected |
| Action | Approve/Reject or Locked |

## Approving a Payment

### Steps
1. Find the pending payment in the table
2. Click the green **"Approve"** button
3. Confirmation dialog appears
4. Click "OK" to confirm
5. Payment status changes to **APPROVED**
6. Success message appears

### What Happens on Approval
- ✓ Payment recorded as APPROVED
- ✓ Loan balance updated
- ✓ Loan marked as CLEARED if balance = 0
- ✓ Interest distributed to all members
- ✓ Admin name and timestamp recorded
- ✓ Transaction log created

## Rejecting a Payment

### Steps
1. Find the pending payment in the table
2. Click the red **"Reject"** button
3. Confirmation dialog appears
4. Click "OK" to confirm
5. Payment status changes to **REJECTED**
6. Success message appears

### What Happens on Rejection
- ✓ Payment recorded as REJECTED
- ✓ Loan balance unchanged
- ✓ No interest distributed
- ✓ Admin name and timestamp recorded

## Payment Statuses

### Pending (Yellow Badge)
- Payment submitted by member
- Awaiting your approval
- You can approve or reject
- Button shows: **Approve** | **Reject**

### Approved (Green Badge)
- Payment has been approved
- Loan balance updated
- Interest distributed
- Button shows: **Locked**

### Rejected (Red Badge)
- Payment was rejected
- Loan balance unchanged
- No interest distributed
- Button shows: **Locked**

## Dashboard Card Updates

After you approve/reject payments, the admin dashboard updates:
- **Payment Approvals** card count decreases
- Shows remaining pending payments
- Click card to return to approval page

## Common Scenarios

### Scenario 1: Member Pays 500,000 on 2M Loan
1. Payment submitted (Status: Pending)
2. You approve
3. Loan balance: 1.5M → 1M
4. Interest: 500,000 × 2% = 10,000 UGX distributed
5. Loan status: Active (still owes money)

### Scenario 2: Member Pays Final Amount
1. Payment submitted for exact remaining balance
2. You approve
3. Loan balance: 100,000 → 0
4. Interest: 100,000 × 2% = 2,000 UGX distributed
5. Loan status: **CLEARED** (paid in full)
6. Member can now apply for new loan

### Scenario 3: Reject Payment
1. Payment submitted (Status: Pending)
2. You reject (wrong amount, invalid receipt, etc.)
3. Loan balance unchanged
4. No interest distributed
5. Member can resubmit correct payment

## Interest Distribution

When you approve a payment:
- **Interest calculated**: Payment amount × 2% (default rate)
- **Total group savings**: All members' combined savings
- **Each member gets**: Interest × (Their savings ÷ Group savings)

### Example
- Approved payment: 100,000 UGX
- Interest pool: 100,000 × 0.02 = 2,000 UGX
- Group has 1M total savings
- Member with 250,000 savings gets: 2,000 × (250,000/1,000,000) = 500 UGX

## Tips for Admins

### Best Practices
1. ✓ Review member information before approving
2. ✓ Verify payment amount matches what member intended
3. ✓ Check receipt number if provided
4. ✓ Approve regularly to avoid bottlenecks
5. ✓ Document rejections with notes if system allows

### What to Check
- Is payment amount reasonable?
- Is payment method valid?
- Does receipt number match (if provided)?
- Is member's account in good standing?
- Is the loan still active?

### When to Reject
- ✗ Payment amount exceeds remaining balance
- ✗ Receipt/reference number invalid
- ✗ Member's account suspended
- ✗ Loan already cleared
- ✗ Suspicious activity
- ✗ Member requested rejection

## Keyboard Shortcuts
- None currently, but buttons are clickable
- Confirmation dialogs prevent accidental approvals

## Troubleshooting

### No Payments Showing
- Check admin account has proper permissions
- Verify members have submitted payments
- Check if all payments were already processed

### Approval Failed Error
- Loan may have been deleted
- Member account may be inactive
- Try again or contact support

### Member Sees No Updates
- Page may need refresh (F5)
- Logout and login to see updated balance
- Check loan status in loan details

## Dashboard Walk-through

1. **Login**: Use admin credentials
2. **Dashboard**: See Payment Approvals card
3. **Count**: Shows how many pending
4. **Click**: Open approval page
5. **Review**: Check all payment details
6. **Action**: Approve or Reject
7. **Confirm**: Click OK on dialog
8. **Result**: Payment processed, dashboard updates

## Contact & Support
For issues or questions:
- Check PAYMENT_APPROVAL_WORKFLOW.md for detailed info
- Review database structure in database.sql
- Contact system administrator

---
**Last Updated**: March 2026  
**System**: 70K Savings & Loans Management System
