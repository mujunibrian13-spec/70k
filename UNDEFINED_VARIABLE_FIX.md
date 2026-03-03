# Undefined Variable Error - Fixed

## Problem

Errors:
```
Notice: Undefined variable: can_save in savings.php on line 47
Notice: Undefined variable: next_save_date in savings.php on line 48
```

These errors occurred when accessing the savings page.

## Root Cause

The variables `$can_save` and `$next_save_date` were only initialized inside an `if` block (lines 43-44), but were being used outside of that block (lines 47-48).

### Before (Broken)
```php
if ($member_id === 0) {
    $error = 'No members found...';
    $member = null;
    $current_savings = 0;
    $week_savings = null;
    // NOTE: $can_save and $next_save_date NOT initialized here
} else {
    $member = getMemberDetails($conn, $member_id);
    $week_savings = getCurrentWeekSavings($conn, $member_id);
    $can_save = !$week_savings;                    // Only initialized here
    $next_save_date = getNextSavingsDate($conn, $member_id);  // Only here
}

// ERROR: Using undefined variables!
if (!$can_save) {  // ❌ Undefined!
    $warning = '...';
}
```

## Solution

Initialize all variables with default values BEFORE the if/else block:

### After (Fixed)
```php
// Initialize variables with defaults FIRST
$member = null;
$current_savings = 0;
$week_savings = null;
$can_save = true;                    // Default: can save
$next_save_date = date('Y-m-d');    // Default: today

if ($member_id === 0) {
    $error = 'No members found...';
} else {
    $member = getMemberDetails($conn, $member_id);
    $week_savings = getCurrentWeekSavings($conn, $member_id);
    $can_save = !$week_savings;                    // Override if needed
    $next_save_date = getNextSavingsDate($conn, $member_id);
}

// Now variables are always defined
if (!$can_save && $week_savings) {  // ✅ Defined!
    $warning = '...';
}
```

## Changes Made

### 1. Initialize Default Values
```php
// Initialize variables with defaults
$member = null;
$current_savings = 0;
$week_savings = null;
$can_save = true;
$next_save_date = date('Y-m-d');
```

Placed BEFORE the if/else block so they're always defined.

### 2. Added Safety Check
```php
if (!$can_save && $week_savings) {
    $warning = '...';
}
```

Changed from `if (!$can_save)` to `if (!$can_save && $week_savings)` to ensure `$week_savings` exists before accessing it.

### 3. Simplified Control Flow
Moved the variable initialization outside the if/else block for clarity and consistency.

## How It Works Now

### Scenario 1: No Members in System
```
$member_id = 0
↓
Variables initialized with defaults:
- $can_save = true
- $next_save_date = today
↓
if ($member_id === 0) block executes:
- Sets $error message
- Variables remain as defaults
↓
if (!$can_save && $week_savings) block:
- $can_save = true, so condition is false
- Warning is NOT shown (correct)
↓
✅ No undefined variable errors
```

### Scenario 2: Member Has Savings This Week
```
$member_id = 15
↓
Variables initialized with defaults
↓
else block executes:
- Gets member details
- Gets week savings (exists!)
- Sets $can_save = !$week_savings = false
- Sets $next_save_date = future date
↓
if (!$can_save && $week_savings) block:
- $can_save = false AND $week_savings exists
- Condition is true
- Warning IS shown (correct)
↓
✅ Proper behavior, no errors
```

### Scenario 3: Member Has No Savings This Week
```
$member_id = 15
↓
Variables initialized with defaults
↓
else block executes:
- Gets member details
- Gets week savings (null)
- Sets $can_save = !$week_savings = true
- Sets $next_save_date = today
↓
if (!$can_save && $week_savings) block:
- $can_save = true, so condition is false
- Warning is NOT shown (correct)
↓
✅ Proper behavior, no errors
```

## Variables Initialized

| Variable | Default Value | Overridden In | Purpose |
|----------|---------------|---------------|---------|
| `$member` | `null` | else block | Store member details |
| `$current_savings` | `0` | else block | Store member's total savings |
| `$week_savings` | `null` | else block | Store current week's savings |
| `$can_save` | `true` | else block | Flag: can member save again? |
| `$next_save_date` | `date('Y-m-d')` | else block | When can member save next? |

## Error Prevention

### Before Fix
```
Variables undefined in certain code paths
  ↓
Using undefined variables
  ↓
❌ PHP Notice shown to user
  ↓
Errors in browser console
```

### After Fix
```
All variables initialized with defaults
  ↓
Variables always defined (at least with default)
  ↓
✅ No PHP Notice
  ↓
Clean browser console
```

## Testing

### Test 1: No Members
```
1. Setup: Delete all members from database
2. Access: savings.php
3. Expected: No undefined variable errors
4. Result: ✅ Works (shows "No members found" message)
```

### Test 2: Member Without This Week's Savings
```
1. Setup: Login as admin, select a member with no savings this week
2. Access: savings.php
3. Expected: No warnings, can add savings
4. Result: ✅ Works
```

### Test 3: Member With This Week's Savings
```
1. Setup: Member already has savings recorded this week
2. Access: savings.php
3. Expected: Warning shown, can update savings
4. Result: ✅ Works (shows warning message)
```

## Summary

| Aspect | Before | After |
|--------|--------|-------|
| Undefined variables | ❌ Yes | ✅ No |
| Variable initialization | ❌ Conditional | ✅ Always |
| Error notices | ❌ Shown | ✅ None |
| Code clarity | ❌ Mixed | ✅ Clear |
| Robustness | ❌ Weak | ✅ Strong |

## Code Quality Improvements

1. **Always Initialize Variables**
   - Variables defined before use
   - Default values provided
   - No undefined variable notices

2. **Clear Default State**
   - All variables have sensible defaults
   - Behavior predictable
   - Easy to understand flow

3. **Proper Error Handling**
   - Check for existence before use
   - Safe condition checks
   - No edge case crashes

---

**Date Fixed:** March 2026  
**File:** savings.php  
**Lines Changed:** 29-53  
**Status:** ✅ FIXED
