# RetroGameHub - Setup Checklist & Quick Start Guide

## ✅ Installation Checklist

### Prerequisites
- [x] XAMPP installed with Apache & MySQL
- [x] Project files in `C:\xampp\htdocs\retroonline\`
- [x] PHP 7.4+ running
- [x] MySQL 5.7+ running

### Step 1: Start XAMPP
```
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL
```

### Step 2: Create Database
```
1. Open browser → http://localhost/phpmyadmin
2. Click "New" to create new database
3. Name: RetroGameHub
4. Click "Create"
5. Go to "SQL" tab
6. Open database.sql from project folder
7. Copy all content and paste in SQL tab
8. Click "Go"
```

### Step 3: Verify Configuration
Open `config.php` and verify:
```php
DB_HOST = 'localhost'
DB_USER = 'root'
DB_PASS = '' (or your password)
DB_NAME = 'RetroGameHub'
```

### Step 4: Access the Application
Open browser and go to:
```
http://localhost/retroonline
```

---

## 🔐 Test Login Credentials

| Role | Email | Password | URL |
|------|-------|----------|-----|
| **Admin** | admin@retrohub.com | password123 | /pages/admin/dashboard.php |
| **Seller 1** | seller1@retrohub.com | seller123 | /pages/seller/dashboard.php |
| **Seller 2** | seller2@retrohub.com | seller123 | /pages/seller/dashboard.php |
| **Customer 1** | gamer1@retrohub.com | gamer123 | /pages/customer/shop.php |
| **Customer 2** | gamer2@retrohub.com | gamer123 | /pages/customer/shop.php |

---

## 🎯 Test Scenarios

### 1. Customer Journey
```
1. Login as gamer1@retrohub.com / gamer123
2. Browse products on Shop page
3. Filter by console or price
4. Click on a product to see details
5. Add product to cart
6. Go to checkout
7. Add/select shipping address
8. Place order
9. View order history
10. Leave a review on purchased product
```

### 2. Seller Operations
```
1. Login as seller1@retrohub.com / seller123
2. View Dashboard (sales, earnings, recent orders)
3. Go to Products page
4. Add a new product
5. Edit existing product
6. View Orders
7. Update tracking information
8. Check Earnings breakdown
```

### 3. Admin Tasks
```
1. Login as admin@retrohub.com / password123
2. View Admin Dashboard
3. Check platform statistics
4. Go to Applications
5. Approve a seller application (if pending)
6. Go to Sellers to view all active sellers
7. Monitor Orders across all sellers
```

---

## 📁 Project Files Summary

### Configuration Files
- ✅ `config.php` - Database & session configuration
- ✅ `database.sql` - Database schema + demo data
- ✅ `index.php` - Entry point with role-based routing
- ✅ `README.md` - Complete documentation

### PHP Classes (OOP Design)
- ✅ `classes/User.php` - User authentication & management
- ✅ `classes/Product.php` - Product CRUD & filtering
- ✅ `classes/Order.php` - Order management & tracking
- ✅ `classes/Review.php` - Reviews & ratings
- ✅ `classes/Seller.php` - Seller applications & approval
- ✅ `classes/Address.php` - Address management

### Customer Pages
- ✅ `pages/customer/auth.php` - Login/Register
- ✅ `pages/customer/shop.php` - Product listing with filters
- ✅ `pages/customer/product-detail.php` - Product details & reviews
- ✅ `pages/customer/cart.php` - Shopping cart
- ✅ `pages/customer/checkout.php` - Checkout with address management
- ✅ `pages/customer/orders.php` - Order history
- ✅ `pages/customer/order-detail.php` - Order details & tracking
- ✅ `pages/customer/profile.php` - User profile & settings

### Seller Pages
- ✅ `pages/seller/dashboard.php` - Seller overview & stats
- ✅ `pages/seller/products.php` - Product management (CRUD)
- ✅ `pages/seller/orders.php` - Order management & tracking
- ✅ `pages/seller/earnings.php` - Sales & earnings tracking
- ✅ `pages/seller/settings.php` - Settings (placeholder)
- ✅ `pages/seller/logout.php` - Logout handler

### Admin Pages
- ✅ `pages/admin/dashboard.php` - Admin overview
- ✅ `pages/admin/applications.php` - Seller application approvals
- ✅ `pages/admin/sellers.php` - Seller management
- ✅ `pages/admin/orders.php` - Platform order management
- ✅ `pages/admin/logout.php` - Logout handler

### Styling
- ✅ `assets/css/style.css` - Complete responsive stylesheet (1000+ lines)

### Demo Data Included
- ✅ 1 Admin user
- ✅ 2 Seller accounts with 3 products each
- ✅ 2 Customer accounts
- ✅ 2 Sample orders with items
- ✅ Order tracking data
- ✅ Customer reviews
- ✅ Commission records

---

## 🎨 Design Features

### Responsive Design
- ✅ Mobile-friendly (320px+)
- ✅ Tablet optimized
- ✅ Desktop responsive
- ✅ CSS Grid & Flexbox layout

### Retro-Inspired Aesthetic
- ✅ NES/SNES color palette
- ✅ Classic gaming inspired styling
- ✅ Modern clean interface
- ✅ Smooth transitions & hover effects

### UI Components
- ✅ Product cards with overlays
- ✅ Styled forms & inputs
- ✅ Alert messages (success/error/info)
- ✅ Data tables with proper formatting
- ✅ Navigation bars for each role
- ✅ Modal dialogs
- ✅ Status badges
- ✅ Rating/review displays

---

## 🔒 Security Features

### Password Security
- ✅ Bcrypt hashing with password_hash()
- ✅ Password_verify() for validation
- ✅ Minimum 6 character requirement

### Database Security
- ✅ Prepared statements (prevents SQL injection)
- ✅ Parameterized queries throughout
- ✅ Proper foreign key constraints
- ✅ Cascade delete rules

### Session Management
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Session timeout handling
- ✅ User logout functionality

### Data Validation
- ✅ Input sanitization
- ✅ Email validation
- ✅ Numeric validation
- ✅ Required field checking

---

## 📊 Database Design

### Tables (11 total)
1. **users** - User accounts & authentication
2. **sellers** - Seller store information
3. **seller_applications** - Seller approval workflow
4. **products** - Product catalog
5. **addresses** - Customer shipping addresses
6. **orders** - Customer orders
7. **order_items** - Items in each order
8. **order_tracking** - Shipping & tracking info
9. **reviews** - Product & seller reviews
10. **commissions** - Platform commission tracking
11. **couriers** - Courier/shipping companies

### Key Features
- ✅ Relational design with proper keys
- ✅ JSON storage for flexible roles
- ✅ Timestamps for audit trail
- ✅ Cascade delete for data integrity
- ✅ Proper indexing for performance

---

## 🚀 Ready to Launch!

### Quick Commands

**View the site:**
```
http://localhost/retroonline
```

**Admin Dashboard:**
```
http://localhost/retroonline/pages/admin/dashboard.php
Email: admin@retrohub.com
Password: password123
```

**Browse Products:**
```
http://localhost/retroonline/pages/customer/shop.php
Email: gamer1@retrohub.com
Password: gamer123
```

**Seller Dashboard:**
```
http://localhost/retroonline/pages/seller/dashboard.php
Email: seller1@retrohub.com
Password: seller123
```

---

## 📋 Features Checklist

### ✅ Implemented Features

**Customer Features:**
- [x] User registration/login
- [x] Product browsing & search
- [x] Product filtering (console, condition, price)
- [x] Product detail pages with reviews
- [x] Shopping cart (session-based)
- [x] Checkout process
- [x] Address management
- [x] Order placement & history
- [x] Order tracking
- [x] Review & rating system
- [x] User profile management
- [x] Password change functionality

**Seller Features:**
- [x] Seller application system
- [x] Dashboard with analytics
- [x] Product management (CRUD)
- [x] Inventory tracking
- [x] Order management
- [x] Shipping/tracking updates
- [x] Sales & earnings tracking
- [x] Commission breakdown
- [x] Settings page (placeholder)

**Admin Features:**
- [x] Admin dashboard
- [x] Seller application review/approval
- [x] Seller management
- [x] Order monitoring
- [x] Platform revenue tracking
- [x] Commission management
- [x] User role management

**Backend Features:**
- [x] OOP design with classes
- [x] Prepared SQL statements
- [x] Database transactions
- [x] Error handling
- [x] Session management
- [x] Role-based access control

**Frontend Features:**
- [x] Responsive CSS
- [x] Mobile optimization
- [x] Modern UI design
- [x] Form validation
- [x] Status messages
- [x] Data tables
- [x] Product cards
- [x] Navigation menus

---

## 🎮 Demo Product Data

### Seller 1 - RetroKing
1. **Super Mario Bros** - NES, Mint, $20 (5 in stock)
2. **Pokemon Red** - Gameboy, Used, $15 (3 in stock)
3. **The Legend of Zelda** - NES, Mint, $25 (2 in stock)

### Seller 2 - PixelGames
1. **Final Fantasy VII** - PlayStation, Mint, $30 (4 in stock)
2. **Sonic the Hedgehog** - Sega, Used, $10 (6 in stock)
3. **Street Fighter II** - SNES, Mint, $22 (3 in stock)

---

## 🐛 Troubleshooting

### Database Connection Error
```
Error: "Database connection failed"
Solution: 
1. Check MySQL is running
2. Verify credentials in config.php
3. Ensure RetroGameHub database exists
```

### Login Fails
```
Error: "User not found or invalid password"
Solution:
1. Clear browser cache
2. Check email spelling
3. Verify database populated (run database.sql)
4. Password is case-sensitive
```

### Upload Folder Issues
```
Error: "Upload failed"
Solution:
1. Right-click /uploads folder
2. Properties → Security → Modify
3. Grant write permissions
```

### Session Issues
```
Error: "You have been logged out"
Solution:
1. Check browser cookies enabled
2. Verify PHP session folder writable
3. Check session timeout (config.php)
```

---

## 📞 Support Resources

- Check README.md for detailed documentation
- Review database.sql for schema details
- Examine class files for OOP structure
- Test with provided demo accounts
- Check browser console for JavaScript errors

---

## ✨ You're All Set!

The RetroGameHub marketplace is fully functional and ready to use. Follow the test scenarios above to explore all features.

**Happy Gaming! 🎮**

Created: February 2026  
Version: 1.0.0  
Status: Production Ready ✅
