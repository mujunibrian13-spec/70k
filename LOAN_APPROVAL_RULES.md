# Loan Approval Rules

## Overview
Loan applications follow a strict approval workflow to prevent inconsistent states.

---

## Loan Status Workflow

```
PENDING → APPROVED → ACTIVE → COMPLETED
   ↓
REJECTED
```

### Status Definitions:

1. **PENDING** ⏳
   - Initial state when member applies for loan
   - Awaiting admin decision
   - Can be APPROVED or REJECTED
   - **Buttons available:** Approve, Reject

2. **APPROVED** ✅
   - Admin has approved the loan
   - Loan is ready to be disbursed
   - Cannot be changed to REJECTED
   - **Buttons available:** None (Locked)

3. **ACTIVE** 🔄
   - Loan is currently active
   - Member is repaying the loan
   - Cannot be changed
   - **Buttons available:** None (Locked)

4. **COMPLETED** ✔️
   - Loan has been fully paid off
   - Transaction is closed
   - Cannot be changed
   - **Buttons available:** None (Locked)

5. **REJECTED** ❌
   - Admin has rejected the loan
   - Member's request was denied
   - Only available for PENDING loans
   - Cannot be changed back
   - **Buttons available:** None (Locked)

---

## Key Rules

### ✅ What CAN be done:

1. **Reject a PENDING loan**
   - Admin can reject only if status is PENDING
   - Once rejected, it cannot be approved later

2. **Approve a PENDING loan**
   - Admin can approve any PENDING loan
   - Once approved, it moves to APPROVED status

### ❌ What CANNOT be done:

1. **Reject an APPROVED loan**
   - ❌ Status: APPROVED → Cannot Reject
   - Error message: "Cannot reject a loan that is already approved. Only pending loans can be rejected."

2. **Reject an ACTIVE loan**
   - ❌ Status: ACTIVE → Cannot Reject
   - Error message: "Cannot reject a loan that is already active. Only pending loans can be rejected."

3. **Reject a COMPLETED loan**
   - ❌ Status: COMPLETED → Cannot Reject
   - Error message: "Cannot reject a loan that is already completed. Only pending loans can be rejected."

4. **Approve a non-PENDING loan**
   - ❌ Cannot approve already processed loans
   - Error message: "This loan has already been processed (Status: X). Cannot make changes."

---

## Admin Dashboard - Loan Management

### Table View:
- Shows **ALL** loan applications (pending, approved, active, completed, rejected)
- Sorted by status: Pending → Approved → Active → Completed → Rejected
- Latest applications appear first within each status group

### Status Column:
- **PENDING** (Yellow Badge) - Action buttons available
- **APPROVED** (Blue Badge) - Locked (no actions)
- **ACTIVE** (Green Badge) - Locked (no actions)
- **COMPLETED** (Purple Badge) - Locked (no actions)
- **REJECTED** (Red Badge) - Locked (no actions)

### Action Buttons:
- **For PENDING loans:** Approve and Reject buttons
- **For other statuses:** Locked button (disabled)

---

## Validation Logic

```php
// Check current loan status
if ($action === 'reject' && $current_status !== 'pending') {
    // ERROR: Cannot reject non-pending loans
} else if ($current_status !== 'pending') {
    // ERROR: Loan already processed
} else {
    // PROCESS: Approve or Reject
}
```

---

## Use Cases

### Scenario 1: Admin approves a pending loan
1. Loan status: PENDING
2. Admin clicks "Approve"
3. System validates: Status is PENDING ✓
4. Status changes to: APPROVED
5. Result: ✅ Success

### Scenario 2: Admin tries to reject an approved loan
1. Loan status: APPROVED
2. Admin tries to click "Reject" (button is disabled)
3. If somehow rejected anyway:
   - System checks: Status is APPROVED ✗
   - Error: "Cannot reject a loan that is already approved"
4. Result: ❌ Failure (prevents action)

### Scenario 3: Admin rejects a pending loan
1. Loan status: PENDING
2. Admin clicks "Reject"
3. System validates: Status is PENDING ✓
4. Status changes to: REJECTED
5. Result: ✅ Success

### Scenario 4: Member has an active loan
1. Loan status: ACTIVE
2. Admin views loan in table
3. Status badge: Green "ACTIVE"
4. Action button: "Locked" (disabled)
5. Admin cannot make any changes
6. Result: ✅ Loan is protected

---

## Error Messages

| Scenario | Error Message |
|----------|---------------|
| Reject APPROVED loan | "Cannot reject a loan that is already approved. Only pending loans can be rejected." |
| Reject ACTIVE loan | "Cannot reject a loan that is already active. Only pending loans can be rejected." |
| Reject COMPLETED loan | "Cannot reject a loan that is already completed. Only pending loans can be rejected." |
| Approve APPROVED loan | "This loan has already been processed (Status: Approved). Cannot make changes." |
| Non-existent loan | "Loan not found." |

---

## Testing Checklist

- [ ] Admin can approve a PENDING loan
- [ ] Admin can reject a PENDING loan
- [ ] Admin CANNOT reject an APPROVED loan
- [ ] Admin CANNOT reject an ACTIVE loan
- [ ] Reject button is disabled for non-PENDING loans
- [ ] Approve button is disabled for non-PENDING loans
- [ ] Table shows all loans (pending + approved + active + completed + rejected)
- [ ] Status badges display correctly for each status
- [ ] Loan table is sorted correctly (pending first)
- [ ] Error messages appear when attempting invalid actions
- [ ] Member's loan requests appear in the table

---

**Version:** 1.0  
**Date:** March 2026  
**Status:** ✅ Active
