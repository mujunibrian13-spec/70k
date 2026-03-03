# Single Active Loan Restriction Policy

## Overview
Members can only have ONE active/approved loan at a time. They must clear their current loan before applying for a new one.

---

## Rules

### ✅ What Members CAN Do:

1. **Apply for a Loan**
   - When they have NO active or approved loans
   - After clearing (paying off) a previous loan
   - Status can be: PENDING, CLEARED, COMPLETED, or REJECTED

2. **Pay Their Loan**
   - While loan is ACTIVE or APPROVED
   - Make partial or full payments
   - Once cleared, status changes to CLEARED

3. **Apply Again**
   - Immediately after clearing a loan
   - No waiting period
   - Can apply for larger/smaller amounts

### ❌ What Members CANNOT Do:

1. **Apply for Second Loan**
   - ❌ While existing loan is APPROVED
   - ❌ While existing loan is ACTIVE
   - Error: "You cannot apply for a new loan while you have an existing [status] loan"

2. **Apply While Existing Loan is Not Cleared**
   - ❌ Form is disabled
   - ❌ Submit button is greyed out
   - ❌ Tooltip shows reason

3. **Have Multiple Active Loans**
   - ❌ System prevents creating second loan if first still active
   - ❌ Database validation blocks duplicate active loans

---

## Loan Status & Application Eligibility

| Loan Status | Can Apply New Loan? | Reason |
|-------------|-------------------|--------|
| PENDING | ❌ No | Must wait for approval or rejection |
| APPROVED | ❌ No | Loan will soon become active |
| ACTIVE | ❌ No | Must pay off and clear first |
| CLEARED | ✅ Yes | Previous loan is fully paid |
| COMPLETED | ✅ Yes | Previous loan is archived |
| REJECTED | ✅ Yes | Can apply for new loan immediately |

---

## User Experience Flow

### Scenario 1: Member with No Loans
1. Member navigates to Loans page
2. Apply for Loan form is ENABLED
3. Member can submit application
4. Loan status: PENDING

### Scenario 2: Member with Rejected Loan
1. Member had loan application REJECTED
2. Member can immediately apply for NEW loan
3. Form is ENABLED
4. Can submit new application

### Scenario 3: Member with Approved Loan
1. Member has loan status: APPROVED
2. Tries to navigate to Loans page
3. **Red Alert Shows:**
   - "Existing Loan Pending"
   - "You have an existing approved loan with remaining balance of X"
   - "You cannot apply for a new loan until this loan is cleared"
   - Button: "Pay Loan" (links to pay_loan.php)
4. Apply for Loan form is DISABLED
5. Submit button is greyed out
6. Tooltip on hover: "You must clear your existing loan first"

### Scenario 4: Member with Active Loan
1. Member has loan status: ACTIVE
2. Tries to apply for new loan
3. **Red Alert Shows:**
   - Shows existing active loan details
   - Remaining balance amount
   - Recommendation to pay
4. Form is DISABLED
5. Cannot submit application

### Scenario 5: Member Pays Off Loan
1. Member goes to "Pay Loan" page
2. Makes payments
3. Remaining balance → 0
4. Status automatically changes to CLEARED
5. **Member can now:**
   - Go back to Loans page
   - Form is now ENABLED
   - Apply for NEW loan immediately
   - No waiting period!

---

## Technical Implementation

### Backend Check (PHP)

```php
// Check if member has existing active or approved loans
$existing_loan_query = "SELECT id, loan_amount, total_payable, remaining_balance, status FROM loans 
                        WHERE member_id = ? AND status IN ('approved', 'active')
                        LIMIT 1";
$existing_stmt = $conn->prepare($existing_loan_query);
$existing_stmt->bind_param('i', $member_id);
$existing_stmt->execute();
$existing_loan_result = $existing_stmt->get_result();
$has_existing_loan = $existing_loan_result->num_rows > 0;

// Member cannot apply if they have existing active/approved loan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_loan'])) {
    if ($has_existing_loan) {
        $error = 'You cannot apply for a new loan while you have an existing ' . 
                 $existing_loan['status'] . ' loan. Please clear your current loan first.';
    }
}
```

### Frontend Display

1. **Alert Box** (if existing loan)
   - Red danger alert
   - Shows loan status and remaining balance
   - "Pay Loan" button for quick access
   - Explains cannot apply for new loan

2. **Form Disabled State**
   - All input fields disabled
   - Submit button greyed out with tooltip
   - Cannot interact with form

3. **Tooltip on Hover**
   - "You must clear your existing loan first"
   - Shows reason for disabled state

---

## Error Messages

### When Trying to Apply with Existing Loan
```
You cannot apply for a new loan while you have an existing APPROVED loan. 
Please clear your current loan first by paying it off.
```

### Form Tooltip
```
You must clear your existing loan first
```

### Alert Message
```
Existing Loan Pending
You have an existing ACTIVE loan with a remaining balance of Ush 2,400,000. 
You cannot apply for a new loan until this loan is cleared.
```

---

## Benefits

✅ **Prevents Over-Borrowing**
- Members can't take unlimited loans
- Controlled debt exposure

✅ **Encourages Timely Payment**
- Must pay to access new credit
- Incentivizes loan clearing

✅ **Reduces Risk**
- Admin tracks single loan per member
- Clearer repayment obligations

✅ **Better Cash Flow**
- Ensures priority for loan payments
- Reduces default risk

✅ **Simplifies Accounting**
- One loan per member at a time
- Easier transaction tracking

---

## Member Journey - Timeline

### Month 1
- Member applies for 5M loan → Status: PENDING
- Admin approves → Status: APPROVED
- Member cannot apply for another loan ❌

### Month 2  
- Loan becomes active → Status: ACTIVE
- Member cannot apply for another loan ❌
- Member starts paying → Balance decreases

### Months 2-6
- Member makes monthly payments
- Cannot apply for new loan while balance > 0 ❌
- Status remains ACTIVE

### Month 6 (Final Payment)
- Member pays final amount
- Balance = 0
- Status automatically → CLEARED
- **NOW member CAN apply for new loan** ✅

### Month 7
- Member applies for 8M loan (different amount)
- Loan approved and starts repayment
- Process repeats

---

## Database Validation

### Query to Check Existing Loans
```sql
SELECT id, status, remaining_balance 
FROM loans 
WHERE member_id = ? 
AND status IN ('approved', 'active')
LIMIT 1;
```

### Loan Status Check
- Only APPROVED and ACTIVE prevent new applications
- PENDING loans that were just applied DO block new applications
- CLEARED, COMPLETED, REJECTED do NOT block new applications

---

## Testing Checklist

- [ ] Member with NO loans can apply ✅
- [ ] Member with APPROVED loan sees red alert ❌
- [ ] Member with APPROVED loan cannot apply ❌
- [ ] Member with ACTIVE loan sees red alert ❌
- [ ] Member with ACTIVE loan cannot apply ❌
- [ ] Form fields disabled when existing loan ❌
- [ ] Submit button disabled with tooltip ❌
- [ ] "Pay Loan" button in alert links correctly ✅
- [ ] After clearing loan, can immediately apply ✅
- [ ] Member with REJECTED loan can apply ✅
- [ ] Member with CLEARED loan can apply ✅
- [ ] Tooltip shows on button hover ✅

---

## FAQ

**Q: Can I have 2 loans at once?**
A: No. You can only have ONE active/approved loan. Clear it first by paying it off.

**Q: If my loan is rejected, can I apply again?**
A: Yes! If rejected, you can apply for a new loan immediately.

**Q: How long before I can apply after clearing a loan?**
A: Immediately! Once your loan is cleared (status = CLEARED), you can apply right away.

**Q: What if I have an approved but not active loan?**
A: You still cannot apply. APPROVED loans block new applications. Wait for approval decision.

**Q: Can admin override the single loan restriction?**
A: No. The system prevents multiple active/approved loans per member at the database level.

---

**Version:** 1.0  
**Date:** March 2026  
**Status:** ✅ Active
