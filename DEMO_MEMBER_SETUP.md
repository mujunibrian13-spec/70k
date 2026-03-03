# Demo Member Setup Guide

## Quick Start

### Step 1: Create Demo Member
1. Open your browser and navigate to:
   ```
   http://localhost/70k/setup_demo_member.php
   ```

2. The page will automatically create a demo member with the following details:
   - **Name**: John Demo Member
   - **Email**: demo@70k.local
   - **Password**: demo123
   - **NIN**: 12345678901234
   - **Phone**: +256701234567
   - **Initial Savings**: 500,000 UGX
   - **Member ID**: (will be shown after creation)

### Step 2: Demo Member Credentials

Use these credentials to test the system:

```
Email:    demo@70k.local
Password: demo123
NIN:      12345678901234
```

---

## What You Can Demo

### As a Member (demo@70k.local)
1. **Login** - Test member login functionality
2. **Profile** - View and update member profile
3. **Savings** - View current savings and contribution history
4. **Loans** - Apply for loans and view loan status
5. **Payments** - Make loan payments
6. **Reports** - View transaction history and reports

### As an Admin
1. **View Member** - Go to Admin Dashboard → All Members to see demo member
2. **Edit Profile** - Click "Edit" to modify member information
3. **Add Savings** - Test the improved savings selector to add/update savings for demo member
4. **Reset Password** - Reset demo member's password if needed
5. **Delete Member** - Test the delete functionality
6. **Restore Member** - Test the undo feature to restore the deleted member

---

## Demo Workflow

### Testing Member Features
1. Log in as: `demo@70k.local` / `demo123`
2. View dashboard and savings
3. Apply for a loan
4. Make a payment
5. View reports

### Testing Admin Features
1. Log in as admin
2. Go to Admin Dashboard
3. Click "All Members" tab
4. Find "John Demo Member"
5. Click "Edit" to modify profile
6. Try adding savings via "Add Savings" link
7. Test reset password
8. Test delete functionality
9. Test restore (undo) functionality

### Complete Feature Test
1. **Add Savings**: Add 100,000 UGX to demo member
2. **Create Loan**: Apply for 2,000,000 UGX loan as demo member
3. **Approve Loan**: Go to admin, approve the pending loan
4. **Make Payment**: Pay 500,000 UGX against the loan
5. **Edit Profile**: Update demo member's phone number
6. **Delete Member**: Delete the demo member (with undo)
7. **Restore Member**: Click undo to restore the member
8. **Check Reports**: Verify all transactions are logged

---

## Demo Data Summary

| Field | Value |
|-------|-------|
| **Member ID** | Auto-assigned on creation |
| **Full Name** | John Demo Member |
| **Email** | demo@70k.local |
| **Phone** | +256701234567 |
| **NIN** | 12345678901234 |
| **Address** | 123 Demo Street, Kampala, Uganda |
| **Status** | Active |
| **Initial Savings** | 500,000 UGX |
| **Password** | demo123 |

---

## Tips for Demonstration

1. **Save Credentials**: Copy the demo credentials for reference during demo
2. **Test Savings First**: Add savings to build up the member's available loan amount
3. **Multiple Actions**: Test adding multiple savings entries on different dates
4. **Loan Approval**: Show how admin approves/rejects loans
5. **Undo Feature**: Show the new delete and restore functionality
6. **Reports**: Show real transaction history generated from demo actions

---

## If You Need to Reset

If you want to clear demo data and start fresh:

1. Go to Admin Dashboard
2. Find "John Demo Member" in Members list
3. Click "Delete" to delete the member
4. Go back to `setup_demo_member.php` to recreate it

---

## Common Scenarios to Test

### Scenario 1: Savings Contribution
1. Log in as demo member
2. View savings (shows 500,000 UGX)
3. Note that mandatory savings = 500,000 UGX (target met)
4. Go back to admin
5. Add additional savings (e.g., 250,000 UGX)
6. Member now has 750,000 UGX total

### Scenario 2: Loan Application
1. Log in as demo member
2. Apply for loan (max = member's savings = current total)
3. Go to admin
4. Approve the loan
5. Member can now see active loan

### Scenario 3: Loan Payment
1. With active loan in place
2. Log in as demo member
3. Make a payment (e.g., 100,000 UGX)
4. Payment shows as pending
5. Go to admin
6. Approve the payment
7. Loan balance decreases by payment amount

### Scenario 4: Member Deletion & Restoration
1. Go to Admin → Members
2. Click "Delete" next to demo member
3. Confirm deletion by typing email
4. Go back to dashboard
5. See "Recently Deleted Members" section
6. Click "Restore" button
7. Member is back in the system

---

## Troubleshooting

**Q: Demo member not created**
- A: Ensure database is running and connected
- Check browser console for error messages
- Try refreshing the page

**Q: Can't login with demo credentials**
- A: Clear browser cookies
- Try incognito/private browsing
- Verify database has the member

**Q: Savings not showing**
- A: Demo member starts with 500,000 UGX
- Check member's savings in profile
- Add more savings through admin interface

**Q: Delete/Restore not working**
- A: Verify `deleted_members` and `deletion_log` tables exist
- Run the SQL setup from UNDO_FEATURE_SETUP.md
- Check admin session is active

---

## Next Steps After Demo

1. **Register Real Members**: Test registration with actual user data
2. **Import Data**: If you have existing members, import from CSV/Excel
3. **Configure System**: Adjust mandatory savings amount if needed
4. **Train Admins**: Show admin panel features
5. **Go Live**: Deploy to production with real member data

---

**Demo Member Ready!** 🎉

Visit `http://localhost/70k/setup_demo_member.php` to create and manage your demo member.
