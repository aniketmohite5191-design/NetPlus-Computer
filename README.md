# NETplus Computers - E-Commerce Website

A comprehensive computer and computer accessories product selling website built with PHP, MySQL, and Bootstrap.

## Features

### User Features
- **Authentication**: User registration, login, logout, password change
- **Product Catalog**: Browse products by category, search functionality
- **Shopping Cart**: Add/remove items, update quantities
- **Checkout**: Multiple payment options (Cash on Delivery, Razorpay, PayPal)
- **Order Management**: View order history, order details
- **User Profile**: Update personal information, change password
- **Support**: Contact form, FAQ page

### Admin Features
- **Dashboard**: View statistics and recent orders
- **Product Management**: Add, edit, delete products with image upload
- **Category Management**: Manage product categories
- **Order Management**: View and manage customer orders
- **User Management**: View registered users, block/unblock users
- **Settings**: Configure payment gateways, tax rates, contact info

### Responsive Design
- Mobile-first responsive design
- Bottom navigation for mobile devices
- Professional desktop layout with sidebar navigation
- Touch-friendly interface on mobile

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

## Installation

### 1. Database Setup

1. Create a database named `netplus_computers`
2. Import the database schema from `database.sql`

```sql
-- Run in phpMyAdmin or MySQL CLI
mysql -u root -p netplus_computers < database.sql
```

### 2. Configuration

1. Update database credentials in `config/database.php` if needed:
```php
private $host = "localhost";
private $db_name = "netplus_computers";
private $username = "root";
private $password = "";
```

2. Update site settings in the database `settings` table or through admin panel

### 3. Directory Permissions

Ensure these directories have write permissions:
- `assets/images/products/` - For product image uploads
- `assets/images/` - For general uploads

### 4. Default Admin Login

- **Username**: admin
- **Password**: admin123

**IMPORTANT**: Change the default admin password after first login!

### 5. Payment Gateway Configuration

Configure payment gateways in the admin panel:

- **Razorpay**: Add your API key and secret
- **PayPal**: Add your business email

## Directory Structure

```
netplus computers/
├── admin/              # Admin panel files
│   ├── login.php
│   ├── dashboard.php
│   ├── products.php
│   ├── product-form.php
│   └── ...
├── assets/             # Static assets
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
│       └── products/
├── config/             # Configuration files
│   └── database.php
├── includes/           # Helper functions
│   └── functions.php
├── index.php           # Home page
├── products.php        # Product listing
├── product-detail.php  # Product details
├── cart.php            # Shopping cart
├── checkout.php        # Checkout page
├── orders.php          # Order history
├── order-detail.php    # Order details
├── order-confirmation.php # Order confirmation
├── profile.php         # User profile
├── login.php           # User login
├── register.php        # User registration
├── forgot-password.php # Password reset
├── about.php           # About page
├── contact.php         # Contact page
├── faq.php             # FAQ page
├── logout.php          # Logout
├── database.sql        # Database schema
└── README.md           # This file
```

## User Flow

1. **Homepage** → Browse featured products and categories
2. **Product Listing** → Filter and search products
3. **Product Detail** → View details, add to cart
4. **Cart** → Review items, proceed to checkout
5. **Checkout** → Enter details, select payment method
6. **Order Confirmation** → Order success page
7. **Orders** → View order history and status

## Admin Flow

1. **Login** → Admin authentication
2. **Dashboard** → View statistics and recent orders
3. **Products** → Manage product catalog
4. **Orders** → Process and manage orders
5. **Users** → Manage customer accounts
6. **Settings** → Configure website settings

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with output encoding
- Session management
- Admin role-based access control

## Customization

### Branding
Update site branding in the admin panel settings:
- Site name
- Logo
- Contact information
- Social media links

### Categories
Default categories:
- Laptops
- Desktops
- Accessories
- Monitors
- Printers

Add or modify categories through the admin panel.

### Payment Methods
Supported payment methods:
- Cash on Delivery (COD)
- Razorpay (Cards, UPI, Net Banking)
- PayPal

Configure in admin panel under Settings.

## Troubleshooting

### Common Issues

1. **Database connection errors**
   - Verify database credentials in `config/database.php`
   - Ensure MySQL service is running

2. **Image upload errors**
   - Check directory permissions
   - Verify `upload_max_filesize` in PHP configuration

3. **Session issues**
   - Ensure `session_start()` is not blocked
   - Check cookie settings in browser

4. **Email not sending**
   - Configure SMTP settings
   - Check spam folders

## Support

For technical support or feature requests, please contact:
- Email: support@netplus.com
- Phone: +91-9876543210

## License

This project is proprietary software. All rights reserved.

## Credits

- Built with Bootstrap 5
- Icons by Font Awesome
- PHP & MySQL

---

**NETplus Computers** - Your Trusted Computer Store
