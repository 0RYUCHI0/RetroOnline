# RetroGameHub - Complete Feature Documentation

## 📱 Application Overview

RetroGameHub is a fully functional multi-seller retro game marketplace with role-based access control, complete e-commerce functionality, and modern web architecture.

---

## 👤 User Roles & Access Levels

### 1. Customer (Buyer)
**Access Point:** `/pages/customer/auth.php`

**Capabilities:**
- Browse product catalog with real-time search
- Filter products by console, condition, and price range
- View detailed product information with reviews
- Add/remove items from shopping cart
- Manage multiple shipping addresses
- Complete checkout process
- Place orders and track shipments
- View complete order history
- Submit product reviews (1-5 stars)
- Manage account profile
- Change password securely

**Key Pages:**
- Shop (product listing)
- Product Detail (reviews & demos)
- Cart (manage items)
- Checkout (address selection)
- Orders (history & tracking)
- Profile (account settings)

---

### 2. Seller (Merchant)
**Access Point:** `/pages/seller/dashboard.php`

**Capabilities:**
- Apply for seller approval
- Create, read, update, delete products
- Manage inventory/stock levels
- Add product images and demo URLs
- View all incoming orders
- Update order status and tracking information
- Manage shipping with courier details
- Track sales metrics and revenue
- View detailed earnings with commission breakdown
- Monitor product conditions (Mint/Used/Refurbished)
- Set competitive pricing
- Manage store settings

**Dashboard Metrics:**
- Total active products
- Total orders received
- Total sales amount
- Net earnings (after commissions)
- Recent order notifications

**Key Pages:**
- Dashboard (analytics overview)
- Products (inventory management)
- Orders (fulfillment & tracking)
- Earnings (revenue & commissions)
- Settings (profile management)

---

### 3. Admin (Platform Manager)
**Access Point:** `/pages/admin/dashboard.php`

**Capabilities:**
- View platform-wide statistics
- Monitor total sellers and products
- Review seller applications for approval
- Approve or reject seller registrations
- Manage all active sellers
- Monitor all platform orders
- View platform revenue and commissions
- Manage user roles and permissions
- Oversee dispute resolution
- Generate platform reports

**Dashboard Metrics:**
- Total active sellers
- Total orders processed
- Platform total revenue
- Commission tracking

**Key Pages:**
- Dashboard (platform overview)
- Applications (seller approval)
- Sellers (seller management)
- Orders (order oversight)

---

## 🛍️ Shopping Features

### Product Browsing
```
✅ Grid display of all available products
✅ Product cards with image, price, console, condition
✅ Average rating display with review count
✅ In-stock status indicator
✅ Seller information (store name)
✅ Hover effects with "View Details" overlay
```

### Search & Filtering
```
✅ Full-text search by product name
✅ Console filter (NES, SNES, Gameboy, etc.)
✅ Condition filter (Mint, Used, Refurbished)
✅ Price range filter (min-max)
✅ Filter combination support
✅ Real-time search results
✅ Clear filters button
```

### Product Details
```
✅ Full product information display
✅ High-resolution product image
✅ Console and category information
✅ Condition details
✅ Current pricing
✅ Stock availability
✅ Seller information & rating
✅ Embedded demo video (if available)
✅ Customer reviews with ratings
✅ Review submission form
```

### Shopping Cart
```
✅ Add/remove items
✅ Update quantities
✅ Real-time price calculation
✅ Item breakdown display
✅ Subtotal and total display
✅ Free shipping indicator
✅ Continue shopping link
✅ Proceed to checkout button
```

### Checkout Process
```
✅ Address selection/creation
✅ Multiple address support
✅ Complete address form:
   - Recipient name
   - Address lines 1 & 2
   - City & State
   - Postal code & Country
   - Phone number
✅ Order summary review
✅ Final price calculation
✅ Order confirmation
```

### Order Management
```
✅ Order history listing
✅ Order status display (pending/processing/shipped/delivered)
✅ Order date & total amount
✅ Shipping address display
✅ Items in each order
✅ Detailed order view
✅ Shipping tracking information
✅ Courier name & tracking number
```

---

## 📦 Product Management (Seller)

### Product CRUD Operations
```
CREATE:
✅ Add new product with full details
✅ Set product name, console, category
✅ Choose condition (Mint/Used/Refurbished)
✅ Set price and initial stock
✅ Add image URL
✅ Add demo video URL

READ:
✅ View all seller products in table
✅ View product grid on customer shop
✅ View product details on detail page
✅ Check real-time stock levels

UPDATE:
✅ Edit product name & description
✅ Update pricing
✅ Adjust stock quantities
✅ Change condition classification
✅ Update image/demo URLs
✅ Edit console & category

DELETE:
✅ Remove products from catalog
✅ Confirm deletion
✅ Clean removal (no orphaned data)
```

### Inventory Management
```
✅ Track stock quantities
✅ Update stock after orders
✅ View stock availability
✅ Prevent overselling
✅ Automatic stock decrease on order
✅ Display stock status (in-stock/out-of-stock)
```

---

## 📊 Order & Fulfillment

### Order Processing Flow
```
1. Customer places order → Order created with PENDING status
2. Seller reviews order → Can mark as PROCESSING
3. Seller ships items → Adds tracking info, marks as SHIPPED
4. Customer receives → Tracked as IN_TRANSIT then DELIVERED
5. Order complete → Customer can review products
```

### Order Tracking
```
✅ Order item-level tracking
✅ Courier selection (FedEx, UPS, DHL, etc.)
✅ Tracking number entry
✅ Tracking status updates:
   - Pending (awaiting shipment)
   - Shipped (picked up)
   - In Transit (on the way)
   - Delivered (completed)
✅ Real-time status display
✅ Customer visibility
```

### Commission System
```
✅ Automatic commission calculation (5% default)
✅ Commission per order item
✅ Net earnings calculation (gross - commissions)
✅ Commission tracking in database
✅ Seller earnings dashboard
✅ Detailed commission breakdown
```

---

## ⭐ Reviews & Ratings

### Product Reviews
```
✅ 1-5 star rating system
✅ Optional comment/description
✅ Display reviewer name
✅ Show review date
✅ Average rating calculation
✅ Review count tracking
✅ Prevent duplicate reviews
✅ Only verified purchases can review
```

### Seller Ratings
```
✅ Aggregate ratings from products
✅ Average seller rating
✅ Total review count
✅ Rating display on product cards
✅ Rating display on seller info
```

---

## 🔐 Authentication & Security

### Registration
```
✅ Email validation
✅ Required field validation
✅ Password confirmation
✅ Minimum 6 character password
✅ Phone number optional
✅ Auto-role assignment (customer)
✅ Bcrypt password hashing
```

### Login
```
✅ Email & password validation
✅ Active status check
✅ Session creation on success
✅ Role loading from database
✅ Redirect to appropriate dashboard
✅ Password verification (bcrypt)
```

### Session Management
```
✅ SessionManager class for handling
✅ Session variables: user_id, email, name, roles
✅ Has role checking
✅ User ID retrieval
✅ Logout functionality
✅ Session timeout handling
```

### Password Security
```
✅ Bcrypt hashing algorithm
✅ Password_hash() for new passwords
✅ Password_verify() for validation
✅ Change password functionality
✅ Old password verification required
✅ Password confirmation matching
```

### SQL Injection Prevention
```
✅ Prepared statements throughout
✅ Parameterized queries (bind_param)
✅ No string concatenation in queries
✅ Input sanitization
✅ Type casting where appropriate
```

---

## 📋 Database Architecture

### Core Tables

**users**
```
- user_id (PRIMARY KEY)
- name
- email (UNIQUE)
- password (hashed)
- phone
- roles (JSON array)
- status (active/inactive/banned)
- timestamps
```

**sellers**
```
- seller_id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- store_name
- contact_info
- payment_info
- created_at
```

**products**
```
- product_id (PRIMARY KEY)
- seller_id (FOREIGN KEY)
- name, console, category
- condition (enum)
- price, stock
- image_url, demo_url
- timestamps
```

**orders**
```
- order_id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- total_amount
- status (enum)
- shipping_address_id (FOREIGN KEY)
- timestamps
```

**order_items**
```
- order_item_id (PRIMARY KEY)
- order_id (FOREIGN KEY)
- product_id (FOREIGN KEY)
- seller_id (FOREIGN KEY)
- quantity, price
```

**order_tracking**
```
- tracking_id (PRIMARY KEY)
- order_item_id (FOREIGN KEY)
- courier_name
- tracking_number
- status (enum)
```

**reviews**
```
- review_id (PRIMARY KEY)
- product_id, user_id, seller_id (FOREIGN KEYS)
- rating (1-5)
- comment
- created_at
```

**commissions**
```
- commission_id (PRIMARY KEY)
- order_item_id, seller_id (FOREIGN KEYS)
- commission_percent
- commission_amount
```

**addresses**
```
- address_id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- recipient_name
- address_line1, address_line2
- city, state, postal_code, country
- phone
```

**seller_applications**
```
- application_id (PRIMARY KEY)
- user_id (FOREIGN KEY)
- store_name, contact_info, payment_info
- status (pending/approved/rejected)
- applied_at
```

**couriers**
```
- courier_id (PRIMARY KEY)
- name
- phone, email
```

---

## 🎨 Frontend Design

### Responsive Design
```
✅ Mobile-first approach
✅ Breakpoints: 480px, 768px, 1024px
✅ Flexible grid layouts
✅ Mobile navigation
✅ Touch-friendly buttons
✅ Responsive tables
✅ Mobile-optimized forms
```

### UI Components
```
✅ Navigation bars (per role)
✅ Product cards with hover effects
✅ Forms with proper validation
✅ Alert messages (4 types)
✅ Status badges
✅ Action buttons
✅ Data tables
✅ Modal dialogs
✅ Footer
✅ Rating stars
```

### Color Scheme
```
Primary: #1a1a2e (Dark Navy)
Secondary: #16213e (Darker Navy)
Accent: #e94560 (Retro Red)
Success: #2ecc71 (Green)
Info: #3498db (Blue)
Warning: #f39c12 (Orange)
Danger: #e74c3c (Red)
Light: #f8f9fa (Off-white)
```

### Animations
```
✅ Hover effects on cards
✅ Smooth transitions
✅ Button animations
✅ Nav link highlights
✅ Loading states
✅ Color transitions
```

---

## 🔄 Application Flow

### Customer Flow
```
1. Enter site → Redirected to login
2. Login/Register → Choose customer role
3. Browse shop → Search/filter products
4. View product → Read reviews, see demos
5. Add to cart → Save in session
6. Checkout → Select/add address
7. Place order → Create order in database
8. View orders → Track status
9. Leave review → Submit rating & comment
10. Profile → Manage account settings
```

### Seller Flow
```
1. Register account → Choose seller role
2. Apply as seller → Submit application
3. Wait for approval → Admin reviews
4. Login dashboard → View analytics
5. Add products → Create inventory
6. Manage products → Edit/delete items
7. View orders → See customer orders
8. Update tracking → Add courier info
9. View earnings → Check commission breakdown
10. Manage settings → Update store info
```

### Admin Flow
```
1. Login → Access admin panel
2. View dashboard → See platform stats
3. Review applications → Approve/reject sellers
4. Manage sellers → View seller list
5. Monitor orders → Track all orders
6. View revenue → Check commissions
7. Manage users → Control access
```

---

## 📈 Metrics & Analytics

### Customer Metrics
```
✅ Total orders placed
✅ Average order value
✅ Order history
✅ Product reviews submitted
✅ Account creation date
```

### Seller Metrics
```
✅ Total orders received
✅ Total revenue (gross)
✅ Commission charges
✅ Net earnings
✅ Product count
✅ Product ratings
✅ Stock levels
```

### Platform Metrics
```
✅ Total sellers (active)
✅ Total orders
✅ Total platform revenue
✅ Commission collected
✅ Seller applications (pending/approved/rejected)
```

---

## 🎯 Key Implementation Details

### Database Transactions
```
Used in: Order creation
- Create order record
- Create order items
- Create tracking records
- Create commission records
- Update product stock
- Rollback if any step fails
```

### Password Hashing
```
Algorithm: Bcrypt (PASSWORD_BCRYPT)
Cost Factor: 10 (default)
Usage: All passwords hashed before storage
Verification: password_verify() used for login
```

### Session Management
```
Storage: PHP sessions (server-side)
Variables: user_id, email, name, roles
Timeout: 3600 seconds (configurable)
Security: Session regeneration on login
```

### Error Handling
```
✅ Try-catch blocks in critical operations
✅ Database errors captured
✅ User-friendly error messages
✅ Input validation
✅ Required field checking
```

---

## 📚 Code Architecture

### OOP Design Patterns
```
✅ Singleton Pattern (Database connection)
✅ Static Methods (SessionManager, DatabaseConnection)
✅ Prepared Statements (security)
✅ Exception handling (error management)
```

### File Organization
```
- /config.php → Configuration & Database class
- /index.php → Entry point with routing
- /classes/ → All model classes
- /pages/customer/ → Customer pages
- /pages/seller/ → Seller pages
- /pages/admin/ → Admin pages
- /assets/css/ → Stylesheets
- /uploads/ → User uploads
```

### Naming Conventions
```
✅ Classes: PascalCase (User, Product, Order)
✅ Methods: camelCase (getProductById, createOrder)
✅ Variables: snake_case ($_POST, $product_id)
✅ Constants: UPPER_CASE (DB_HOST, SITE_URL)
✅ Database columns: snake_case (user_id, store_name)
```

---

## ✨ Special Features

### Multi-Seller Support
```
✅ Products linked to specific sellers
✅ Order items track seller
✅ Commissions per seller
✅ Seller dashboards isolated
✅ Seller-specific reports
```

### Commission System
```
✅ Automatic calculation (5% default)
✅ Per-order-item basis
✅ Net earnings tracking
✅ Commission breakdown report
✅ Configurable percentage
```

### Flexible Address Management
```
✅ Multiple addresses per customer
✅ Add new address at checkout
✅ Select from saved addresses
✅ Complete address information
✅ Edit/delete addresses
```

### Product Condition Classification
```
✅ Mint (like new, original packaging)
✅ Used (well-maintained, fully functional)
✅ Refurbished (professionally restored)
```

---

## 🚀 Performance Considerations

### Database Optimization
```
✅ Foreign key relationships
✅ Proper indexing on common queries
✅ Prepared statements (faster)
✅ Minimal data fetch
✅ Cascade deletes (referential integrity)
```

### Frontend Optimization
```
✅ CSS classes reusable
✅ Responsive images
✅ Minimal JavaScript
✅ Session-based cart (no database hits)
✅ Efficient database queries
```

---

## 📝 Conclusion

RetroGameHub is a production-ready e-commerce platform with comprehensive features for customers, sellers, and administrators. All functionality has been implemented using secure coding practices, proper OOP design patterns, and modern web standards.

**Ready for deployment and customization!** 🎮
