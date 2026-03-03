# Undefined Index Error - Fixed

## Problem

Error: `Notice: Undefined index: member_id in C:\wamp\www\70k\loans.php on line 15`

This error occurred when accessing member-only pages because `$_SESSION['member_id']` was not set.

## Root Cause

The pages were trying to access `$_SESSION['member_id']` without checking if it exists first.

This happens when:
1. An admin tries to access a member-only page
2. A logged-in user doesn't have a member_id in their session
3. The session data is incomplete

The code assumed all logged-in users would have a member_id, but admins don't have one since they're not members.

## Solution

Added proper checks before accessing `$_SESSION['member_id']`:

```php
// Check if user is a member (not admin)
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    redirect('admin.php');  // Redirect admins to admin dashboard
}

// Check if member_id exists in session
if (!isset($_SESSION['member_id']) || empty($_SESSION['member_id'])) {
    redirect('login.php');  // Redirect if no member_id
}

// Now it's safe to use member_id
$member_id = $_SESSION['member_id'];
```

## Files Fixed

✅ **loans.php**
- Line 15: Added member_id check before using it

✅ **index.php** (Dashboard)
- Line 26: Added member_id check before using it

✅ **profile.php**
- Line 16: Added member_id check before using it

✅ **pay_loan.php**
- Line 15: Added member_id check before using it

✅ **reports.php**
- Line 15: Added member_id check before using it

## What Each Fix Does

### 1. Admin Check
```php
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    redirect('admin.php');
}
```
**Purpose:** Redirects admins to the admin dashboard
**Why:** These pages are for members only, not admins

### 2. Member ID Check
```php
if (!isset($_SESSION['member_id']) || empty($_SESSION['member_id'])) {
    redirect('login.php');
}
```
**Purpose:** Ensures member_id exists before using it
**Why:** Prevents undefined index notice
**Result:** Redirects to login if member_id is missing

## How It Works

### Before (Broken)
```
User logs in as admin
  ↓
Tries to access loans.php
  ↓
$member_id = $_SESSION['member_id']  (doesn't exist!)
  ↓
❌ Undefined index notice
```

### After (Fixed)
```
User logs in as admin
  ↓
Tries to access loans.php
  ↓
Check: Is user an admin?
  ↓ (YES)
Redirect to admin.php
  ↓
✅ No error, correct page shown
```

## Test Scenarios

### Scenario 1: Admin Accessing Member Page
```
User: Admin
Action: Click "Loans" link
Before Fix: ❌ Notice: Undefined index
After Fix:  ✅ Redirects to admin.php
```

### Scenario 2: Member Accessing Member Page
```
User: Member
Action: Click "Loans" link
Before Fix: ✅ Works (has member_id)
After Fix:  ✅ Works (has member_id)
```

### Scenario 3: Non-Member User
```
User: Logged in but no member_id
Action: Access any member page
Before Fix: ❌ Undefined index
After Fix:  ✅ Redirects to login.php
```

## Member-Only Pages Now Protected

The following pages now properly protect against undefined index errors:

1. **index.php** - Member dashboard
2. **loans.php** - Loan management
3. **profile.php** - Member profile
4. **pay_loan.php** - Payment interface
5. **reports.php** - Financial reports

All these pages now:
- ✅ Check if user is admin (redirect if yes)
- ✅ Check if member_id exists (redirect if no)
- ✅ Safely access session data
- ✅ Show appropriate error handling

## Security Benefit

This fix also adds a security layer:
- Admins cannot accidentally view member pages
- Incomplete sessions are caught and handled
- Users are redirected to appropriate dashboards

## Testing

### Test 1: Admin Can't Access Member Pages
```
1. Login as admin
2. Try to visit: http://localhost/70k/loans.php
3. Should be redirected to: http://localhost/70k/admin.php
4. ✅ No errors, correct redirect
```

### Test 2: Member Can Access Member Pages
```
1. Login as member
2. Visit: http://localhost/70k/loans.php
3. Should see: Member loan page
4. ✅ Works correctly
```

### Test 3: Non-Logged-In Users
```
1. Log out
2. Try to visit: http://localhost/70k/loans.php
3. Should redirect to: http://localhost/70k/login.php
4. ✅ Works correctly
```

## Error Handling Flow

```
User accesses member page
  ↓
Is user logged in?
  ├─ NO  → Redirect to login.php
  ├─ YES → Continue
  ↓
Is user an admin?
  ├─ YES → Redirect to admin.php
  ├─ NO  → Continue
  ↓
Does session have member_id?
  ├─ NO  → Redirect to login.php
  ├─ YES → Continue
  ↓
Safe to use member_id
  ↓
Load page normally
```

## Code Pattern Used

The fix follows this consistent pattern across all pages:

```php
// 1. Check login
if (!isLoggedIn()) {
    redirect('login.php');
}

// 2. Check role (for pages that need specific role)
if (isAdmin()) {
    redirect('admin.php');
}

// 3. Check required session data
if (!isset($_SESSION['member_id']) || empty($_SESSION['member_id'])) {
    redirect('login.php');
}

// 4. Now safe to use the data
$member_id = $_SESSION['member_id'];
```

## Summary

| Aspect | Before | After |
|--------|--------|-------|
| Undefined index errors | ❌ Yes | ✅ No |
| Admin access to member pages | ❌ Allowed | ✅ Blocked |
| Error handling | ❌ Poor | ✅ Good |
| User experience | ❌ Errors shown | ✅ Redirects |
| Security | ❌ Weak | ✅ Better |

## Conclusion

The undefined index error has been fixed by:
1. Adding proper checks before accessing session data
2. Redirecting admins away from member pages
3. Redirecting users with missing session data to login
4. Providing a consistent error handling pattern across all pages

All member-only pages now safely access session variables without showing errors.

---

**Date Fixed:** March 2026
**Files Modified:** 5 (loans, index, profile, pay_loan, reports)
**Status:** ✅ FIXED
