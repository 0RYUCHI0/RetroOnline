# Multi-Variant Product Creation - Quick Start Guide

**For Sellers** | Last Updated: Feb 2, 2026

---

## 🚀 Quick Start (5 minutes)

### Step 1: Access the Add Product Form
1. Login to your Seller Dashboard
2. Click **"Products"** in the menu
3. Click **"Add New Product"**

### Step 2: Enter Basic Information
- **Product Name:** Tetris (same for all conditions)
- **Console:** NES (same for all conditions)
- **Category:** Puzzle (optional, same for all conditions)
- **Image:** Upload product photo (optional, same for all conditions)
- **Demo Video:** Paste YouTube URL (optional, same for all conditions)

### Step 3: Select Conditions & Set Prices/Stock

**Visual Form:**
```
┌─────────────────────────────────────────┐
│  Select Conditions to Create Variants   │
├──────────────┬──────────────┬───────────┤
│    MINT      │     USED     │REFURBISHED│
├──────────────┼──────────────┼───────────┤
│ Price: 99.99 │ Price: 49.99 │ Price: 29 │
│ Stock: 5     │ Stock: 12    │ Stock: 0  │
│ [✓ Selected] │ [✓ Selected] │ [ Not sel]│
└──────────────┴──────────────┴───────────┘
```

**What to do:**
1. **Click card** to select a condition (turns blue)
2. **Enter Price** - must be > 0
3. **Enter Stock** - can be 0 or higher
4. **Repeat** for other conditions or leave unchecked

**Examples:**

✅ **Good Setup:**
- Mint: Price $99.99, Stock 5
- Used: Price $49.99, Stock 12
- Refurbished: (not selected)
→ Creates 2 variants

✅ **Another Example:**
- Mint: Price $89.99, Stock 3
- Used: (not selected)
- Refurbished: Price $39.99, Stock 8
→ Creates 2 variants

❌ **Don't Do:**
- Mint: Price 0, Stock 5 → Price must be > 0
- Used: Price 49.99, Stock -5 → Stock can't be negative
- (no selections) → Must select at least one

### Step 4: Submit & Review

**Click:** "Add Product"

**Result:**
```
✅ Success! Product variants created: Mint ($99.99, 5 units), Used ($49.99, 12 units)
Redirecting...
```

**Then you'll see:**
```
Your Products (2 variants)

Product: "Tetris - NES" (2 variants)
├─ Mint       │ $99.99  │ 5 units  │ [Edit] [Stock] [Delete]
└─ Used       │ $49.99  │ 12 units │ [Edit] [Stock] [Delete]
```

---

## 📋 Condition Guidelines

### MINT Condition
- **What is it?** Game in original packaging, never played, perfect condition
- **Typical Price Range:** $50-$200 (highest)
- **Stock Recommendation:** Usually low (2-5 units)

### USED Condition
- **What is it?** Game works perfectly, may have minor cosmetic wear, original case
- **Typical Price Range:** $30-$80 (medium)
- **Stock Recommendation:** Usually medium (5-20 units)

### REFURBISHED Condition
- **What is it?** Game fully tested & cleaned, works perfectly, may not have original case/manual
- **Typical Price Range:** $15-$40 (lowest)
- **Stock Recommendation:** Usually higher (10-30 units)

---

## ✏️ After Creating Variants

### Add Stock Later
1. Find your product in the list
2. Click **"Stock"** button
3. Enter quantity to ADD (not total)
4. Example: If Mint has 5, enter 3 to make it 8
   - NOT: 8 (that would set it to 8 if you're adding)
   - YES: 3 (adds 3 to existing)

### Update Price
1. Click **"Edit"** on variant
2. Change price (stock is changed via Stock button, NOT here)
3. Can't change condition (defines the variant)

### Delete Variant
1. Click **"Delete"** on variant
2. Confirm deletion
3. Only that condition variant is deleted
4. Other variants stay (e.g., if you delete Mint, Used/Refurbished remain)

---

## ❓ Common Mistakes & Fixes

### Problem: "Price must be greater than 0"
**Why?** You entered 0, blank, or negative number
**Fix:** Enter a number > 0 (e.g., 19.99, not 0)

### Problem: "Stock cannot be negative"
**Why?** You entered a negative number
**Fix:** Enter 0 or a positive number (e.g., 0, 5, 10)

### Problem: "This variant already exists"
**Why?** You already created this condition for this product
**Fix:** 
- Use "Stock" button to add inventory instead
- Or delete the old variant and create a new one

### Problem: Variant appears with $0 price
**Why?** Form submission failed silently, database had partial data
**Fix:**
- Delete all variants for that product
- Start over with fresh "Add Product"

---

## 💡 Pro Tips

### Tip 1: Price Strategy
```
Recommended pricing for same game:
├─ Mint:       $99.99 (100%)
├─ Used:       $49.99 (50%)
└─ Refurb:     $29.99 (30%)
```

### Tip 2: Stock Strategy
```
Stock by condition (example):
├─ Mint:       2-5 units    (rare, high value)
├─ Used:       10-20 units  (common)
└─ Refurb:     15-30 units  (bulk inventory)
```

### Tip 3: Restock Smart
```
Monthly check:
├─ If Mint stock = 0 → Mark as out, consider deleting
├─ If Used stock ≤ 3 → ⚡ Low stock warning shows to customers
├─ If Refurb stock = 0 → Consider deleting this variant
```

### Tip 4: Customer Experience
- Always set at least USED variant (most popular)
- Mint is premium (smaller stock, higher price)
- Refurbished is value option (larger stock, lower price)

---

## 🔍 Customers See

When a customer looks at your product:

```
┌──────────────────────────────────────────┐
│         Select Condition:                │
│  ┌─────────┬────────┬──────────────┐    │
│  │  MINT   │  USED  │ REFURBISHED  │    │
│  │$99.99   │$49.99  │   $29.99     │    │
│  │[Radio]  │[Radio] │   [Radio]    │    │
│  └─────────┴────────┴──────────────┘    │
│                                          │
│  Selected: MINT                          │
│  Price: $99.99                           │
│  Stock: ✓ In Stock (5 available)         │
│                                          │
│  [🛒 Add to Cart]                        │
└──────────────────────────────────────────┘
```

**Customer clicks "Used":**
```
  Selected: USED
  Price: $49.99                    ← Updates!
  Stock: ✓ In Stock (12 available) ← Updates!
```

**Customer clicks "Refurbished":**
```
  Selected: REFURBISHED
  Price: $29.99
  Stock: ⚠️ Out of Stock           ← Disabled!
```

---

## ✅ Checklist Before Submitting

- [ ] Product Name entered (required)
- [ ] Console selected (required)
- [ ] At least one condition selected (required)
- [ ] Price > 0 for each selected condition
- [ ] Stock ≥ 0 for each selected condition
- [ ] Image uploaded (optional but recommended)
- [ ] Demo video added (optional)

---

## 📊 Example: Real Product

**Product:** Super Mario Bros
**Console:** NES
**Category:** Platformer

**Variants Created:**

| Condition | Price | Stock | Why? |
|-----------|-------|-------|------|
| Mint | $299.99 | 2 | Very rare, sealed, premium price |
| Used | $79.99 | 10 | Most customers buy this |
| Refurbished | $49.99 | 15 | Budget option, tested & working |

**Result:**
- Total units: 27
- Total potential revenue: $299.99×2 + $79.99×10 + $49.99×15 = $1,749.75

---

## 🆘 Stuck?

1. **Form won't submit?**
   - Check that at least one condition is selected
   - Check that prices > 0 for selected conditions
   - Check that stock ≥ 0 for selected conditions

2. **Can't see variants after creation?**
   - Scroll down to "Your Products" section
   - Variants are grouped by product name + console

3. **Wrong price or stock?**
   - Click "Edit" to fix price
   - Click "Stock" to add more inventory
   - Can't edit condition (delete & recreate if needed)

---

## 📞 Need Help?

- Check your variant appears in "Your Products" list
- Verify customer can see all conditions on product page
- Ask support if getting duplicate error but can't find variant

---

**Last Updated:** February 2, 2026  
**System Version:** Multi-Variant v1.0
