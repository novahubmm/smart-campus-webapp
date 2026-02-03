# jQuery and Select2 CDN Fix

## Issue
The academic-setup page was loading jQuery and Select2 from CDN, which was causing connection errors:
```
GET https://code.jquery.com/jquery-3.6.0.min.js net::ERR_CONNECTION_REFUSED
Uncaught ReferenceError: jQuery is not defined
Uncaught ReferenceError: $ is not defined
```

## Solution
Downloaded jQuery and Select2 libraries locally to avoid CDN dependency and connection issues.

## Files Downloaded
1. **jQuery 3.6.0** → `scp/public/js/jquery-3.6.0.min.js` (87KB)
2. **Select2 JS** → `scp/public/js/select2.min.js` (71KB)
3. **Select2 CSS** → `scp/public/css/select2.min.css` (16KB)

## Files Modified
- `scp/resources/views/academic/academic-setup.blade.php`
  - Changed CSS: `https://cdn.jsdelivr.net/.../select2.min.css` → `/css/select2.min.css`
  - Changed JS: `https://code.jquery.com/jquery-3.6.0.min.js` → `/js/jquery-3.6.0.min.js`
  - Changed JS: `https://cdn.jsdelivr.net/.../select2.min.js` → `/js/select2.min.js`

## Select2 Usage
Select2 is used in academic-setup for multi-select dropdowns:
- Grade classes selection (multiple classes per grade)
- Room facilities selection (multiple facilities per room)

## Benefits
✅ No external CDN dependencies
✅ Works offline
✅ Faster loading (no external requests)
✅ No connection refused errors
✅ More reliable in restricted networks

## Testing
- [ ] Academic setup page loads without console errors
- [ ] Grade classes multi-select dropdown works
- [ ] Room facilities multi-select dropdown works
- [ ] Select2 styling appears correctly
- [ ] No jQuery errors in console
