<<<<<<< HEAD
# RetroGameHub - Multi-Seller Retro Game Marketplace

A complete online multi-seller retro game marketplace built with **PHP, MySQL, HTML, CSS, and JavaScript**.

## Features

### 🛍️ Customer Features
- Browse and search retro games
- Filter by console, condition, and price
- Add products to cart
- Checkout and place orders
- Order tracking and history
- Leave product reviews and ratings
- Profile management
- Address management

### 🏪 Seller Features
- Seller dashboard with sales analytics
- Product management (add, edit, delete)
- Order management and tracking
- Shipping updates and courier management
- Earnings tracking with commission breakdown
- Store information management
- Track sales metrics

### 👨‍💼 Admin Features
- Admin dashboard with platform overview
- Review and approve seller applications
- Manage all sellers
- Monitor all orders
- View platform revenue and commissions
- Order tracking oversight

---

## 📋 Setup Instructions

### Prerequisites
- **XAMPP** (or similar local server with PHP & MySQL)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Git** (optional)

### Step 1: Copy Files

The project files should be in:
```
C:\xampp\htdocs\retroonline\
```

### Step 2: Create Database

1. Start XAMPP (Apache & MySQL)
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin`
3. Click **New** to create a new database
4. Database name: `RetroGameHub`
5. Click **Create**
6. Go to the **SQL** tab
7. Copy the contents of `database.sql` file and paste it
8. Click **Go** to execute

**OR use command line:**
```bash
mysql -u root -p < database.sql
```

### Step 3: Configure Database Connection

Edit `config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Enter your MySQL password if set
define('DB_NAME', 'RetroGameHub');
define('DB_PORT', 3306);
```

### Step 4: Set Folder Permissions

Ensure these folders are writable:
```
/uploads/images
/uploads/demos
```

### Step 5: Access the Application

Open your browser and navigate to:
```
http://localhost/retroonline
```

---

## 👥 Demo User Credentials

### Admin Account
- **Email:** admin@retrohub.com
- **Password:** password123
- **Access:** http://localhost/retroonline/pages/admin/dashboard.php

### Seller 1 (RetroKing)
- **Email:** seller1@retrohub.com
- **Password:** seller123
- **Access:** http://localhost/retroonline/pages/seller/dashboard.php

### Seller 2 (PixelGames)
- **Email:** seller2@retrohub.com
- **Password:** seller123
- **Access:** http://localhost/retroonline/pages/seller/dashboard.php

### Customer 1 (GamerOne)
- **Email:** gamer1@retrohub.com
- **Password:** gamer123
- **Access:** http://localhost/retroonline/pages/customer/shop.php

### Customer 2 (GamerTwo)
- **Email:** gamer2@retrohub.com
- **Password:** gamer123
- **Access:** http://localhost/retroonline/pages/customer/shop.php

---

## 📁 Project Structure

```
retroonline/
├── index.php                    # Entry point
├── config.php                   # Database configuration
├── database.sql                 # Database schema & demo data
├── classes/
│   ├── User.php                # User management
│   ├── Product.php             # Product management
│   ├── Order.php               # Order management
│   ├── Review.php              # Review management
│   ├── Seller.php              # Seller management
│   └── Address.php             # Address management
├── pages/
│   ├── customer/
│   │   ├── auth.php            # Login/Register
│   │   ├── shop.php            # Product browsing
│   │   ├── product-detail.php  # Product details
│   │   ├── cart.php            # Shopping cart
│   │   ├── checkout.php        # Checkout process
│   │   ├── orders.php          # Order history
│   │   ├── order-detail.php    # Order details
│   │   └── profile.php         # User profile
│   ├── seller/
│   │   ├── dashboard.php       # Seller dashboard
│   │   ├── products.php        # Product management
│   │   ├── orders.php          # Order management
│   │   ├── earnings.php        # Earnings tracking
│   │   ├── settings.php        # Settings
│   │   └── logout.php          # Logout
│   └── admin/
│       ├── dashboard.php       # Admin dashboard
│       ├── applications.php    # Seller applications
│       ├── sellers.php         # Seller management
│       ├── orders.php          # Order management
│       └── logout.php          # Logout
├── assets/
│   ├── css/
│   │   └── style.css           # Main stylesheet
│   └── js/
│       └── (placeholder)       # JavaScript files
├── uploads/
│   ├── images/                 # Product images
│   └── demos/                  # Product demos
└── includes/                   # (Placeholder for includes)
```

---

## 🎮 Pre-populated Demo Data

### Products
**Seller 1 - RetroKing:**
1. Super Mario Bros (NES, Mint, $20, 5 in stock)
2. Pokemon Red (Gameboy, Used, $15, 3 in stock)
3. The Legend of Zelda (NES, Mint, $25, 2 in stock)

**Seller 2 - PixelGames:**
1. Final Fantasy VII (PlayStation, Mint, $30, 4 in stock)
2. Sonic the Hedgehog (Sega, Used, $10, 6 in stock)
3. Street Fighter II (SNES, Mint, $22, 3 in stock)

### Sample Orders
- 2 completed orders from customers
- Full order items with pricing
- Shipping tracking information
- Customer reviews and ratings

---

## 🔑 Key Features Implementation

### Security
✅ Password hashing with `password_hash()` (bcrypt)  
✅ Prepared SQL statements (prevents SQL injection)  
✅ Session management  
✅ Role-based access control  

### Database Features
✅ Relational design with proper foreign keys  
✅ Cascade deletes for data integrity  
✅ JSON roles storage for flexibility  
✅ Timestamps for auditing  

### User Roles
- **Customer:** Browse, buy, review, manage orders
- **Seller:** Manage products, fulfill orders, track earnings
- **Admin:** Approve sellers, manage platform, view analytics

### Shopping Features
✅ Shopping cart (session-based)  
✅ Multi-seller orders  
✅ Address management  
✅ Order tracking with couriers  
✅ Commission calculation (5% platform fee)  

### Seller Features
✅ Product CRUD operations  
✅ Inventory management  
✅ Order fulfillment  
✅ Earnings tracking  
✅ Seller rating system  

---

## 🎨 Styling

The application uses a **retro-inspired modern design** with:
- Responsive CSS grid layouts
- Mobile-friendly design
- Consistent color scheme (NES/SNES inspired)
- Smooth transitions and hover effects
- Clean typography with proper hierarchy
- Professional cards and tables

### Color Palette
- Primary: #1a1a2e (Dark Navy)
- Secondary: #16213e (Darker Navy)
- Accent: #e94560 (Retro Red)
- Success: #2ecc71 (Green)
- Info: #3498db (Blue)
- Warning: #f39c12 (Orange)

---

## 🚀 Getting Started

### Quick Start
1. Place project in `C:\xampp\htdocs\retroonline\`
2. Create `RetroGameHub` database via phpMyAdmin
3. Import `database.sql`
4. Visit `http://localhost/retroonline`
5. Login with demo credentials above

### First Actions
1. Login as **admin@retrohub.com** to review seller applications
2. Login as **seller1@retrohub.com** to add products
3. Login as **gamer1@retrohub.com** to browse and shop

---

## 📝 API/Architecture Notes

### OOP Design
- Classes in `/classes/` folder
- Singleton pattern for Database connection
- Prepared statements throughout
- Exception handling for error management

### File Organization
- Modular class structure
- Separation of concerns
- Reusable components
- Clean includes structure

### Database Design
- Normalized relational schema
- Proper indexing on common queries
- Foreign key constraints
- Cascade delete rules

---

## 🔧 Troubleshooting

### Database Connection Error
- Verify MySQL is running in XAMPP
- Check `config.php` credentials
- Ensure `RetroGameHub` database exists

### Login Issues
- Clear browser cache/cookies
- Verify user exists in database
- Check password spelling (case-sensitive)

### File Permission Issues
- Right-click `/uploads` folder → Properties
- Grant write permissions for uploads

### Session Issues
- Ensure PHP sessions folder is writable
- Check browser accepts cookies

---

## 📚 File Descriptions

### Core Classes
- **User.php** - Authentication, user management, role handling
- **Product.php** - Product CRUD, search, filtering, stock management
- **Order.php** - Order creation, tracking, seller earnings
- **Review.php** - Product/seller reviews and ratings
- **Seller.php** - Seller applications, approvals, info
- **Address.php** - Customer address management

### Configuration
- **config.php** - Database connection, global settings, SessionManager
- **database.sql** - Full schema with demo data

---

## 🎯 Future Enhancements

- [ ] Payment gateway integration (Stripe/PayPal)
- [ ] Email notifications
- [ ] Wishlist functionality
- [ ] Advanced search with facets
- [ ] Seller ratings and reviews
- [ ] Bundle deals and discounts
- [ ] Affiliate program
- [ ] Dashboard charts (sales graphs)
- [ ] API for mobile app
- [ ] Image upload functionality

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review demo user accounts
3. Verify database is properly imported
4. Check XAMPP services are running

---

**Created:** February 2026  
**Framework:** PHP (Vanilla)  
**Database:** MySQL  
**Frontend:** HTML5, CSS3, Vanilla JavaScript  

**Version:** 1.0.0

Enjoy your RetroGameHub marketplace! 🎮
=======
# RetroOnline
>>>>>>> c8899e551c310491ae4d929336be72ed80c76ca9
