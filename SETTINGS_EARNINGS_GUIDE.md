# Seller Settings & Earnings Reports - Implementation Guide

## Overview

Two major seller features have been implemented to enhance the seller experience and provide detailed business insights:

### 1. **Seller Settings Page** (`pages/seller/settings.php`)
A modern, tab-based settings dashboard for sellers to manage their store, security, and notification preferences.

### 2. **Earnings & Reports Page** (`pages/seller/earnings.php`)
An advanced analytics and reporting dashboard with interactive charts, forecasting, and detailed earnings breakdown.

---

## 📋 Seller Settings Page

### Features

#### 🏪 Store Information Tab
- **Store Name**: Update your store's display name
- **Contact Information**: Update phone/email for customer inquiries
- **Store Description**: Add a bio or description (for future display)

#### 🔒 Account Security Tab
- **Change Password**: Secure password update with validation
  - Current password verification required
  - Minimum 8-character requirement
  - Password confirmation validation
- **2FA Support**: Placeholder for future two-factor authentication

#### 🔔 Notifications Tab
- **Order Notifications**: Toggle alerts for new orders and tracking updates
- **Review Notifications**: Toggle alerts for customer reviews and ratings
- **Message Notifications**: Toggle alerts for customer inquiries

### Technical Details

**File Location**: `pages/seller/settings.php`

**Database Table**: `seller_settings`
```sql
CREATE TABLE seller_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL UNIQUE,
    notify_orders TINYINT DEFAULT 1,
    notify_reviews TINYINT DEFAULT 1,
    notify_messages TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);
```

**Form Handling**:
- Uses POST method with `action` parameter to distinguish between updates
- All form submissions include validation
- Error and success messages displayed inline
- Forms clear after submission

**Security Features**:
- Input sanitization with `htmlspecialchars()` and `trim()`
- Prepared statements for all database operations
- Password change uses existing `User::changePassword()` method
- CSRF tokens can be added in future versions

### Styling

Modern responsive design with:
- Tab navigation with active state highlighting
- Gradient backgrounds and smooth transitions
- Form validation feedback
- Mobile-optimized layout (breakpoint at 768px)
- Color-coded sections (success/error alerts)

---

## 📊 Earnings & Reports Page

### Features

#### 💰 Summary Cards
Four key metrics displayed prominently:
1. **Gross Sales**: Total revenue from all orders
2. **Platform Commission**: Total fees deducted (5% of sales)
3. **Net Earnings**: Your income after commissions
4. **Total Orders**: Number of completed sales

#### 📈 Interactive Charts (Chart.js)

**Sales Trend Chart**:
- Line chart showing sales over time
- Supports 3 view modes:
  - Last 30 Days (daily breakdown)
  - Last 12 Weeks (weekly breakdown)
  - Last 12 Months (monthly breakdown)
- Interactive tooltips showing exact values
- Hover effects and smooth animations
- Green color coding

**Commission Breakdown Chart**:
- Bar chart showing commission amounts over time
- Same time period options as Sales Trend
- Red color coding
- Hover tooltips with precise values

#### 🏆 Top Products Section
Displays your best-performing products with:
- Product name and console
- Units sold
- Total revenue generated
- Sortable by revenue (highest first, limited to top 5)

#### 📈 7-Day Forecast Section
- Calculates average daily sales from historical data
- Projects next 7 days of expected earnings
- Gradient purple background design
- Disclaimer note about forecast accuracy

#### 💹 Earnings Breakdown Table
Detailed financial summary:
- Gross revenue calculation
- Commission deduction
- Net earnings total
- Informational note about calculation methods

### Technical Details

**File Location**: `pages/seller/earnings.php`

**Chart Library**: [Chart.js 4.4.0](https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js)

**Database Queries**:
- Fetches sales data from `order_items` table
- Retrieves commission data from `commissions` table
- Joins with products for top products report
- Uses date functions for time-period filtering

**Filter Options**:
- GET parameter: `filter` (daily, weekly, monthly)
- Date parameter: `date` (for future multi-month viewing)
- Form-based filtering with auto-submit

**Calculation Logic**:
1. Sales grouped by selected time period
2. Commission amounts summed per period
3. Forecast calculated as average of all sales data
4. Top products ranked by total revenue

### Data Processing

```php
// Daily breakdown (30 days)
// Weekly breakdown (12 weeks)
// Monthly breakdown (12 months)

// Each period calculates:
$sales = SUM(order_items.price * order_items.quantity)
$commission = SUM(commissions.commission_amount)
```

### Styling

Professional analytics dashboard with:
- Color-coded cards (green, red, blue, orange)
- Hover effects with depth
- Responsive grid layouts
- Mobile breakpoints at 768px
- Gradient accents
- Professional typography

---

## 🚀 Setup & Migration

### Step 1: Run Database Migration

Visit the migration script in your browser:
```
http://localhost/retroonline/migrate-add-seller-settings.php
```

Or run the SQL directly:
```sql
CREATE TABLE seller_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL UNIQUE,
    notify_orders TINYINT DEFAULT 1,
    notify_reviews TINYINT DEFAULT 1,
    notify_messages TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES sellers(seller_id) ON DELETE CASCADE
);
```

### Step 2: Test the Pages

**Settings Page**:
- Log in as a seller (seller1@retrohub.com / seller123)
- Click "Settings" in navigation
- Test store information update
- Test password change
- Test notification preferences

**Earnings Page**:
- Log in as a seller
- Click "Earnings" in navigation
- Select different time periods (daily, weekly, monthly)
- View charts and interact with them
- Check top products and forecast

---

## 📝 Integration with Existing Code

### Classes Used

**Settings Page**:
- `SessionManager`: Session handling and role checking
- `User`: Password change functionality
- `Seller`: Seller lookup and details
- `Database`: Connection and prepared statements

**Earnings Page**:
- `SessionManager`: Session handling and role checking
- `Seller`: Seller lookup
- `Order`: Get seller earnings data
- `Database`: Advanced queries for analytics

### Existing Methods Called

**User Class**:
```php
public function changePassword($user_id, $oldPassword, $newPassword)
```

**Order Class**:
```php
public function getSellerEarnings($seller_id)
```

**Seller Class**:
```php
public function getSellerByUserId($user_id)
```

---

## 🎨 UI/UX Features

### Settings Page
- **Tab Navigation**: Click tabs to switch sections (no page reload)
- **Form Validation**: HTML5 + PHP validation
- **Visual Feedback**: Green success messages, red error messages
- **Icons**: Emoji icons for quick visual scanning
- **Mobile Responsive**: Full-width forms on small screens

### Earnings Page
- **Interactive Charts**: Hover to see exact values
- **Responsive Layout**: Adapts from 1-2 columns based on screen size
- **Color Coding**: Different colors for different metrics
- **Gradient Accents**: Professional visual design
- **Filter Dropdown**: Quick period selection

---

## 🔐 Security Considerations

### Settings Page
- ✅ Input validation on all forms
- ✅ Password verification for password changes
- ✅ Prepared statements for database operations
- ✅ Output escaping with `htmlspecialchars()`
- ✅ Session-based authentication checks
- ⚠️ TODO: Add CSRF token validation

### Earnings Page
- ✅ Role-based access control
- ✅ Seller ID verification
- ✅ Prepared statements for all queries
- ✅ No sensitive data exposed in charts
- ✅ Read-only operations (no modifications)

---

## 📱 Responsive Design

### Breakpoints

**Desktop (≥768px)**:
- Multi-column grid layouts
- Side-by-side charts
- Full-width tables

**Mobile (<768px)**:
- Single-column layouts
- Stacked charts
- Vertical product stats
- Touch-friendly inputs

---

## 🐛 Testing Checklist

### Settings Page
- [ ] Store name update works
- [ ] Contact info update works
- [ ] Password change with correct validation
- [ ] Password change rejects weak passwords
- [ ] Password change rejects mismatched passwords
- [ ] Notification preferences save correctly
- [ ] Success messages display
- [ ] Error messages display
- [ ] Tabs switch without page reload
- [ ] Mobile layout responsive

### Earnings Page
- [ ] Charts load correctly
- [ ] Hover tooltips show values
- [ ] Filter dropdown changes period
- [ ] All three filter options work (daily/weekly/monthly)
- [ ] Top products display correctly
- [ ] Forecast calculations appear reasonable
- [ ] Breakdown table shows correct math
- [ ] Mobile layout responsive
- [ ] No JavaScript errors in console

---

## 🔄 Future Enhancements

### Settings Page
- [ ] Add 2FA setup with authenticator apps
- [ ] Add seller profile image/logo upload
- [ ] Add store description display on shop pages
- [ ] Add email notification test button
- [ ] Add connected devices/sessions management
- [ ] Add API key generation for integrations

### Earnings Page
- [ ] Add date range picker for custom periods
- [ ] Add product filter for detailed analysis
- [ ] Add payment history/withdrawal tracking
- [ ] Add tax report generation
- [ ] Add comparison with previous periods
- [ ] Add revenue goals/targets
- [ ] Add customer retention metrics
- [ ] Add best/worst performing days analysis

---

## 📞 Support

For issues or questions about these features:
1. Check the inline code comments
2. Review the integration examples above
3. Verify database migration completed successfully
4. Check browser console for JavaScript errors
5. Check PHP error logs for backend issues

---

## 📄 Files Modified/Created

**Modified Files**:
- `pages/seller/settings.php` - Completely rewritten with full functionality
- `pages/seller/earnings.php` - Completely rewritten with analytics
- `database.sql` - Added seller_settings table definition

**New Files**:
- `migrate-add-seller-settings.php` - Migration script for database setup

**No changes needed**:
- All existing classes work seamlessly
- All existing pages unaffected
- No breaking changes to existing code

---

Generated: 2026-02-02
Version: 1.0
