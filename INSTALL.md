# NETplus Computers - Installation Guide

## Quick Setup Steps

### Step 1: Database Setup (5 minutes)

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `netplus_computers`
3. Select the database and click "Import"
4. Choose the `database.sql` file from the project folder
5. Click "Go" to import the schema

### Step 2: Configure Project (2 minutes)

1. Copy the `netplus computers` folder to your XAMPP htdocs directory:
   - Windows: `C:\xampp\htdocs\`
   - macOS: `/Applications/XAMPP/htdocs/`
   - Linux: `/opt/lampp/htdocs/`

2. (Optional) Update database credentials in `config/database.php` if your MySQL settings differ

### Step 3: Set Permissions (1 minute)

Ensure the following directory has write permissions:
- `assets/images/products/` - for product image uploads

On Windows: Right-click → Properties → Security → Edit permissions
On Linux/Mac: `chmod -R 755 assets/images/products/`

### Step 4: Access the Website

1. **User Website**: http://localhost/netplus%20computers/
2. **Admin Panel**: http://localhost/netplus%20computers/admin/login.php

### Default Login Credentials

**Admin Panel:**
- Username: `admin`
- Password: `admin123`

**IMPORTANT**: Change the default admin password after first login!

## Features Overview

### User Side
- Homepage with featured products and categories
- Product browsing with filters and search
- Shopping cart functionality
- Checkout with multiple payment options
- Order tracking and history
- User profile management
- Contact form and FAQ

### Admin Panel
- Dashboard with statistics
- Product management (CRUD operations)
- Order management
- User management
- Category management
- System settings

## File Structure

```
netplus computers/
├── index.php              # Homepage
├── products.php           # Product listing
├── product-detail.php     # Product details
├── cart.php              # Shopping cart
├── checkout.php          # Checkout process
├── orders.php            # Order history
├── order-detail.php      # Order details
├── profile.php           # User profile
├── login.php             # User login
├── register.php          # User registration
├── forgot-password.php   # Password reset
├── about.php             # About page
├── contact.php           # Contact page
├── faq.php               # FAQ page
├── admin/                # Admin panel
│   ├── login.php
│   ├── dashboard.php
│   ├── products.php
│   ├── product-form.php
│   └── logout.php
├── assets/               # Static files
│   ├── css/style.css
│   ├── js/main.js
│   └── images/products/
├── config/               # Configuration
│   └── database.php
├── includes/             # Helper functions
│   └── functions.php
├── database.sql          # Database schema
└── README.md             # Full documentation
```

## Troubleshooting

**Issue: Database connection error**
- Solution: Check database credentials in `config/database.php`
- Ensure MySQL is running in XAMPP

**Issue: Images not uploading**
- Solution: Check folder permissions for `assets/images/products/`
- Ensure PHP `upload_max_filesize` is sufficient

**Issue: 404 errors**
- Solution: Ensure mod_rewrite is enabled in Apache
- Check that all files are properly copied

## Next Steps

1. **Add Products**: Login to admin panel and add products with images
2. **Configure Settings**: Update site settings, payment gateways, and contact info
3. **Customize**: Modify colors, logo, and branding in the admin settings
4. **Test**: Place test orders to ensure checkout flow works correctly

## Support

For issues or questions:
- Check the full README.md for detailed documentation
- Review PHP error logs in XAMPP
- Ensure all requirements are met

---

**NETplus Computers** - Ready to launch your online computer store!
