# Loan Payment Approval Flow Diagram

## System Flow (High Level)

```
┌─────────────────────────────────────────────────────────────────┐
│                    LOAN PAYMENT APPROVAL SYSTEM                  │
└─────────────────────────────────────────────────────────────────┘

                              ┌──────────────┐
                              │    MEMBER    │
                              └──────┬───────┘
                                     │
                          ┌──────────┴─────────────┐
                          │                        │
                    1. Go to Pay                2. View pending
                       Loan page                  payments
                          │                        │
                          ▼                        ▼
                  ┌─────────────────┐      ┌──────────────────┐
                  │  Submit Payment │      │ Pending Approvals│
                  │   - Amount      │      │ - Submitted date │
                  │   - Method      │      │ - Payment amount │
                  │   - Receipt     │      │ - Status         │
                  └────────┬────────┘      └────────┬─────────┘
                           │                        │
                           │                        │
                    ┌──────▼─────────────────────────┤
                    │                                │
                    │  INSERT into loan_payments     │
                    │  - status = 'pending'          │
                    │  - payment_date = today        │
                    │                                │
                    │  UPDATE displays:              │
                    │  - "Pending admin approval"    │
                    │  - Shows payment in list       │
                    │                                │
                    └──────┬─────────────────────────┘
                           │
                           │
                    ┌──────▼──────────┐
                    │  ADMIN APPROVAL │
                    └──────┬──────────┘
                           │
                ┌──────────┴──────────────┐
                │                         │
         ┌──────▼──────┐          ┌──────▼──────┐
         │  APPROVE    │          │   REJECT    │
         │  Payment    │          │   Payment   │
         └──────┬──────┘          └──────┬──────┘
                │                        │
         ┌──────▼─────────────────────────┤
         │                                 │
         │ UPDATE loan_payments            │
         │   - status = 'approved'         │
         │   - approved_by = admin_id      │
         │   - approval_date = now()       │
         │                                 │
         │ UPDATE loans                    │
         │   - remaining_balance -= amount │
         │   - amount_paid += amount       │
         │   - status = (if balance=0)     │
         │     'cleared' else 'active'     │
         │                                 │
         │ DISTRIBUTE INTEREST             │
         │   - Calculate interest pool     │
         │   - Distribute to members by    │
         │     savings ratio               │
         │                                 │
         │ LOG TRANSACTION                 │
         │   - Payment transaction         │
         │   - Interest transactions       │
         │                                 │
         └──────┬──────────────────────────┘
                │
         ┌──────▼──────────────┐
         │  UPDATE ADMIN:      │
         │  - Shows success    │
         │  - Payment now      │
         │    "approved"       │
         │  - Stat card count  │
         │    decreases        │
         └──────┬──────────────┘
                │
         ┌──────▼──────────────┐
         │ MEMBER SEES:        │
         │ - Updated balance   │
         │ - Increased savings │
         │   (interest earned) │
         │ - Loan status may   │
         │   change to         │
         │   "cleared"         │
         └─────────────────────┘

         vs.

    ┌──────▼────────┐
    │  REJECTION    │
    │  Payment      │
    │  (admin only) │
    └──────┬────────┘
           │
    ┌──────▼─────────────────────┐
    │ UPDATE loan_payments        │
    │   - status = 'rejected'     │
    │   - approved_by = admin_id  │
    │   - approval_date = now()   │
    │                             │
    │ DO NOT CHANGE:              │
    │   - Loan balance            │
    │   - Loan status             │
    │   - Member savings          │
    │   - No interest distributed │
    │                             │
    └──────┬─────────────────────┘
           │
    ┌──────▼──────────────┐
    │  ADMIN SEES:        │
    │  - Success message  │
    │  - Payment now      │
    │    "rejected"       │
    │  - Stat card count  │
    │    decreases        │
    └──────┬──────────────┘
           │
    ┌──────▼──────────────┐
    │ MEMBER SEES:        │
    │ - Payment still in  │
    │   pending or list   │
    │ - Can resubmit      │
    │   corrected payment │
    │ - Loan unchanged    │
    └─────────────────────┘
```

## Admin Dashboard - Visual Flow

```
┌────────────────────────────────────────────┐
│         ADMIN DASHBOARD                    │
├────────────────────────────────────────────┤
│                                            │
│  ┌──────────────────────────────────────┐  │
│  │ Stat Cards:                          │  │
│  │  • Total Members: 25                 │  │
│  │  • Group Savings: 50,000,000 UGX     │  │
│  │  • Outstanding Loans: 10,000,000 UGX │  │
│  │  • Pending Applications: 3           │  │
│  │  • PAYMENT APPROVALS: 5  ◄── NEW!    │  │
│  │    [Click to view payments]          │  │
│  └────────────────────────────────────┬─┘  │
│                                       │     │
│                                       ▼     │
│                            ┌─────────────────────────┐
│                            │ Approve Payments Page   │
│                            └────────┬────────────────┘
│                                     │
└────────────────────────────────────┼──────┘
                                     │
                    ┌────────────────┴────────────────┐
                    │                                 │
            ┌───────▼────────┐            ┌──────────▼──────┐
            │  Pending Tab   │            │  Approved Tab   │
            │  (Count: 5)    │            │  (All records)  │
            ├────────────────┤            ├─────────────────┤
            │ Payment #1     │            │ Payment #1 ✓    │
            │ [Approve|Reject]            │ [Locked]        │
            │                │            │                 │
            │ Payment #2     │            │ Payment #2 ✓    │
            │ [Approve|Reject]            │ [Locked]        │
            │                │            │                 │
            │ Payment #3     │            │ Payment #3 ✓    │
            │ [Approve|Reject]            │ [Locked]        │
            │                │            │                 │
            │ Payment #4     │            │ ...             │
            │ [Approve|Reject]            │                 │
            │                │            │                 │
            │ Payment #5     │            └─────────────────┘
            │ [Approve|Reject]
            └────────────────┘
```

## Database Operations - Approval Sequence

```
STEP 1: MEMBER SUBMITS PAYMENT
┌──────────────────────────────────────────────┐
│ INSERT INTO loan_payments:                   │
│  - loan_id = 42                              │
│  - member_id = 15                            │
│  - payment_amount = 500,000                  │
│  - payment_method = 'cash'                   │
│  - status = 'pending'  ◄─ KEY CHANGE        │
│  - payment_date = CURDATE()                  │
│ ┌──────────────────────────────────────────┐ │
│ │ loan_payments table:                     │ │
│ │ id│loan_id│member_id│amount │status     │ │
│ │42│   42  │   15    │500000│pending│    │ │
│ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘

STEP 2: ADMIN APPROVES PAYMENT
┌──────────────────────────────────────────────┐
│ UPDATE loan_payments:                        │
│  - status = 'approved'                       │
│  - approved_by = 1 (admin user ID)           │
│  - approval_date = NOW()                     │
│                                              │
│ ┌──────────────────────────────────────────┐ │
│ │ loan_payments table:                     │ │
│ │ id│status │approved_by│approval_date    │ │
│ │42│approved│    1      │2026-03-02...    │ │
│ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘

STEP 3: UPDATE LOAN BALANCE
┌──────────────────────────────────────────────┐
│ UPDATE loans:                                │
│  - remaining_balance -= 500,000              │
│  - amount_paid += 500,000                    │
│  - status = (if balance <= 0)                │
│            'cleared' else 'active'           │
│                                              │
│ ┌──────────────────────────────────────────┐ │
│ │ loans table:                             │ │
│ │ id│remaining│amount_│status             │ │
│ │42│  500000 │500000│active (still owed) │ │
│ │  │  or 0   │      │cleared (fully paid)│ │
│ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘

STEP 4: DISTRIBUTE INTEREST
┌──────────────────────────────────────────────┐
│ Calculate & Distribute:                      │
│  - Interest = 500,000 × 0.02 = 10,000 UGX   │
│  - For each member:                          │
│      member_interest = 10,000 ×              │
│      (member_savings / total_savings)        │
│                                              │
│ UPDATE members (for each member):            │
│  - savings_amount += member_interest         │
│                                              │
│ ┌──────────────────────────────────────────┐ │
│ │ members table:                           │ │
│ │ id│full_name  │savings_amt│             │ │
│ │1 │Member A   │  250500   │ +500 interest│ │
│ │2 │Member B   │  150750   │ +750 interest│ │
│ │..│...        │  ...      │ ...          │ │
│ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘

STEP 5: LOG TRANSACTIONS
┌──────────────────────────────────────────────┐
│ INSERT INTO transactions (2+ records):       │
│  1. Loan payment:                            │
│     - type = 'loan_payment'                  │
│     - amount = 500,000                       │
│     - member_id = 15                         │
│                                              │
│  2. Interest earned (for each member):       │
│     - type = 'interest_earned'               │
│     - amount = 500, 750, etc.                │
│     - member_id = 1, 2, etc.                 │
│                                              │
│ ┌──────────────────────────────────────────┐ │
│ │ transactions table:                      │ │
│ │ id│member_id│type     │amount │date     │ │
│ │..|   15     │loan_pay │500000│2026-03-02
│ │..|    1     │interest │  500 │2026-03-02
│ │..|    2     │interest │  750 │2026-03-02
│ │..│  ...     │...      │  ...  │...      │ │
│ └──────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘
```

## Payment Status Transitions

```
         ┌──────────────────────────────┐
         │  Member Submits Payment      │
         │  INSERT INTO loan_payments   │
         └──────────┬───────────────────┘
                    │
         ┌──────────▼──────────┐
         │  status = PENDING   │
         │  (Awaiting approval)│
         └──────────┬──────────┘
                    │
        ┌───────────┴────────────┐
        │                        │
   ┌────▼─────────┐       ┌──────▼───────┐
   │  APPROVED    │       │   REJECTED   │
   │  (Admin OK)  │       │  (Admin No)  │
   ├──────────────┤       ├──────────────┤
   │ Loan updated │       │ No changes   │
   │ Interest     │       │ No interest  │
   │ Distributes  │       │ Can resubmit │
   │ Member sees  │       │ Member sees  │
   │ changes      │       │ unchanged    │
   └──────────────┘       └──────────────┘

   Final state:        Final state:
   ✓ APPROVED          ✗ REJECTED
   (immutable)         (can be resubmitted)
```

## Key Changes Summary

### Before (Immediate Processing)
```
Member payment → Instant balance update → Instant interest distribution
```

### After (Admin Approval)
```
Member payment → Pending status → Admin approval → Balance update → Interest distribution
```

### Time Frame
- **Before**: Changes instant (seconds)
- **After**: Changes when admin approves (minutes to hours)

### Member Experience
- **Before**: "Payment recorded and processed"
- **After**: "Payment pending admin approval"

### Admin Workflow
- **Before**: No approval step
- **After**: Review and approve/reject on dedicated page

---

**System**: 70K Savings & Loans Management System  
**Updated**: March 2026  
**Requirement**: Admin must approve all loan payments
