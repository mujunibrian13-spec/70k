# Demo Checklist - 70K Savings & Loans System

## Pre-Demo Setup (Do Once)

- [ ] Database is running and connected
- [ ] Navigate to: `http://localhost/70k/setup_demo_member.php`
- [ ] Click to create demo member
- [ ] Save demo credentials:
  - Email: `demo@70k.local`
  - Password: `demo123`
- [ ] Admin credentials ready:
  - Email: `admin@70k.local`
  - Password: `admin123`

---

## Demo Flow (Follow in Order)

### Part 1: Member Features (10 minutes)

#### Login as Member
- [ ] Go to `http://localhost/70k/login.php`
- [ ] Enter: demo@70k.local / demo123
- [ ] Show member dashboard
- [ ] Navigate to Profile page
- [ ] Show personal details and savings information

#### View Savings
- [ ] Go to Savings page (from member view)
- [ ] Show current savings: 500,000 UGX
- [ ] Show mandatory savings target: 500,000 UGX
- [ ] Show progress bar (100% complete)
- [ ] Show savings history

#### Apply for Loan
- [ ] Go to Loans page
- [ ] Click "Apply for Loan"
- [ ] Enter amount: 1,000,000 UGX
- [ ] Enter purpose: "Business Equipment"
- [ ] Submit application
- [ ] Show status: Pending

#### View Reports
- [ ] Go to Reports page
- [ ] Show transaction history
- [ ] Highlight savings entries
- [ ] Show running balance calculation

---

### Part 2: Admin Features - Savings Management (8 minutes)

#### Login as Admin
- [ ] Logout from member account
- [ ] Go to `http://localhost/70k/login.php`
- [ ] Enter: admin@70k.local / admin123
- [ ] Show admin dashboard

#### Navigate to Add Savings
- [ ] Click "Add Savings" in navigation
- [ ] **NEW FEATURE**: Show improved member search dropdown
- [ ] Type "John" or "demo" to search
- [ ] Select "John Demo Member - demo@70k.local"
- [ ] Show how easy it is to find members now

#### Add Savings
- [ ] Amount: 250,000 UGX
- [ ] Payment Method: Mobile Money
- [ ] Notes: "Additional voluntary savings"
- [ ] Click "Record Savings"
- [ ] Show success message

#### Update Savings
- [ ] Go back to Add Savings
- [ ] Same member should be selected
- [ ] Show updated total: 750,000 UGX
- [ ] Show progress bar now exceeds target
- [ ] Show savings history with new entry

---

### Part 3: Admin Features - Member Management (10 minutes)

#### View All Members
- [ ] Go to Admin Dashboard
- [ ] Click "All Members" tab
- [ ] Find "John Demo Member"
- [ ] Show member details in table
- [ ] **NEW BUTTON**: Point out "Edit" button in Actions column

#### Edit Member Profile
- [ ] Click "Edit" button next to demo member
- [ ] Show edit member page
- [ ] Update phone number (e.g., +256702345678)
- [ ] Update address to something different
- [ ] Click "Update Profile"
- [ ] Show success message
- [ ] Show member sidebar with current info

#### Navigate Back
- [ ] Click "Back to Dashboard"
- [ ] Return to members list
- [ ] Verify member shows updated information

---

### Part 4: Loan Approval (5 minutes)

#### Find Pending Loan
- [ ] Go to Admin Dashboard
- [ ] Check "Pending Loan Applications" tab
- [ ] Find the loan from demo member
- [ ] Show loan details:
  - Member: John Demo Member
  - Amount: 1,000,000 UGX
  - Max Allowed: 750,000 UGX (based on savings)

#### Demonstrate Loan Rules
- [ ] Point out: Loan exceeds savings amount
- [ ] Explain: Max loan = member's savings
- [ ] Show rejection rule: Amount > Max Allowed
- [ ] Click "Reject" button
- [ ] Show confirmation dialog
- [ ] Confirm rejection
- [ ] Show status changed to "Rejected"

#### Apply Different Loan
- [ ] Login as member again
- [ ] Apply for smaller loan: 500,000 UGX (within limit)
- [ ] Login as admin
- [ ] Find new pending loan
- [ ] Click "Approve" button
- [ ] Show confirmation dialog
- [ ] Confirm approval
- [ ] Show status changed to "Approved" then "Active"

---

### Part 5: Delete & Restore (The Star Feature!) (8 minutes)

#### Navigate to Edit Member
- [ ] Go to Admin Dashboard → All Members
- [ ] Find "John Demo Member"
- [ ] Click "Edit" button
- [ ] Scroll down to see "Delete Member" button (in red)

#### Show Delete Button
- [ ] Point out: Red "Delete Member" button
- [ ] Explain: This is a destructive action
- [ ] Show modal opens on click
- [ ] Explain warning: "This action cannot be undone!"
- [ ] Point out what will be deleted:
  - Member profile and account
  - All savings records
  - All loan records
  - All transaction history
  - All interest calculations

#### Safe Deletion
- [ ] Show: "Type the member's email to confirm deletion"
- [ ] Explain: Extra safety layer
- [ ] Type member's email: demo@70k.local
- [ ] Show "Delete Permanently" button becomes enabled
- [ ] Click delete
- [ ] Show success message: "You can undo this action for the next 24 hours"
- [ ] Member is now deleted!

#### Show Undo Panel
- [ ] Click "Back to Dashboard"
- [ ] Go to Admin Dashboard
- [ ] Show "Recently Deleted Members" section at top
- [ ] Display deleted member in a card:
  - Name: John Demo Member
  - Email: demo@70k.local
  - Deleted: [timestamp]
  - "Restore" button

#### Restore the Member
- [ ] Click "Restore" button
- [ ] Show instant restoration
- [ ] Show success message: "Member has been restored successfully"
- [ ] Deleted member card disappears
- [ ] Go to All Members tab
- [ ] Verify member is back!

#### Explain 24-Hour Window
- [ ] Show: "Undo available for 24 hours after deletion"
- [ ] Explain: After 24 hours, member cannot be restored
- [ ] Emphasize: Multiple members can be restored in same period

---

### Part 6: Additional Features (7 minutes)

#### Reset Password
- [ ] With restored member, click "Edit"
- [ ] Click "Reset Password" action button
- [ ] Show password reset page
- [ ] Generate new temporary password
- [ ] Show member would need to change on next login

#### View Member Info Sidebar
- [ ] Still on edit member page
- [ ] Point out right sidebar showing:
  - Member ID
  - Join Date
  - Current Savings
  - Account Status
  - Quick action buttons

#### Verify Transaction History
- [ ] Go to Reports page
- [ ] Verify all actions are logged:
  - Savings additions
  - Loan applications
  - Approvals/Rejections
  - Member profile updates

---

## Post-Demo Activities

### Questions to Ask
- [ ] "What features would you like to see?"
- [ ] "Are there specific workflows you need?"
- [ ] "Any particular member management needs?"
- [ ] "What about reporting or auditing requirements?"

### Show Customization Potential
- [ ] "This can be customized for your needs"
- [ ] "Add more fields to member profiles"
- [ ] "Adjust mandatory savings amounts"
- [ ] "Configure interest rates"

### Ask for Feedback
- [ ] "How easy was the interface to understand?"
- [ ] "Would you want any changes?"
- [ ] "Do you need training for your admins?"
- [ ] "Timeline for going live?"

---

## Key Points to Emphasize

### ✨ Searchable Member Selection
> "Notice how easy it is to find members now. Just type the name or email - no more scrolling through lists!"

### 🔒 Safe Deletion
> "We've built in multiple safety layers. Email confirmation prevents accidental deletion, and you have 24 hours to undo!"

### 📊 Complete Audit Trail
> "Every action is logged - we know who did what, when, and can track everything for compliance."

### 🎯 Smart Loan Rules
> "Loans automatically respect member savings limits. The system enforces your business rules."

### 📱 Professional Interface
> "Clean, intuitive design. Your admin team will find it easy to learn and use."

---

## Timing Guide

| Component | Time |
|-----------|------|
| Setup (pre-demo) | 5 min |
| Part 1: Member Features | 10 min |
| Part 2: Savings Management | 8 min |
| Part 3: Member Management | 10 min |
| Part 4: Loan Approval | 5 min |
| Part 5: Delete & Restore | 8 min |
| Part 6: Additional Features | 7 min |
| Q&A / Feedback | 10 min |
| **Total** | **~63 minutes** |

**Pro Tip**: You can shorten to 30 minutes by doing Parts 1-3 and Part 5 (the headline feature).

---

## Troubleshooting During Demo

### Member Won't Login
- Clear browser cookies
- Try incognito/private window
- Refresh database connection

### Demo Data Not Showing
- Verify setup_demo_member.php was run
- Check database is connected
- Refresh page

### Delete/Restore Not Working
- Ensure backup tables were created via SQL
- Check tables exist in database
- Verify admin session is active

### Search Dropdown Not Working
- Clear browser cache
- Check Select2 library loaded
- Try different browser

---

## Post-Demo Handover

After successful demo, provide:
- [ ] Login credentials document
- [ ] User manual/guide (README.md)
- [ ] System documentation (SETUP.md)
- [ ] Training plan for their admins
- [ ] Support contact information
- [ ] Timeline for implementation
- [ ] Customization quote (if needed)

---

**Demo is Production-Ready!** 🚀

Good luck with your presentation!
