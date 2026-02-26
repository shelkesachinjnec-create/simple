# 🛵 Simple Scooters — Enterprise CRM
## Complete Scooter Showroom Management System

---

## 📋 Prerequisites

- XAMPP (Apache + MySQL + PHP 8.2+)
- PHP Extensions: PDO, PDO_MySQL, fileinfo, mbstring, openssl
- Browser: Chrome / Firefox / Edge

---

## 🚀 Installation Guide (XAMPP)

### Step 1 — Copy Files
```
Copy the `simple-scooter` folder to:
C:\xampp\htdocs\simple-scooter\
```

### Step 2 — Database Setup
1. Open your browser → http://localhost/phpmyadmin
2. Click **"New"** → Create database: `simple_scooter`
3. Select the `simple_scooter` database
4. Click **"Import"** → Upload `database/schema.sql`
5. Click **"Go"**

### Step 3 — Configure Application
Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'simple_scooter');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password if set

define('APP_URL', 'http://localhost/simple-scooter/public');
```

### Step 4 — Enable mod_rewrite
Open `C:\xampp\apache\conf\httpd.conf`:
- Find `#LoadModule rewrite_module` → Remove `#`
- Find `AllowOverride None` (for htdocs) → Change to `AllowOverride All`
- Restart Apache in XAMPP Control Panel

### Step 5 — Upload Folder Permissions
```
Ensure C:\xampp\htdocs\simple-scooter\public\uploads\ is writable
```

### Step 6 — Access the Application
Open: **http://localhost/simple-scooter/public**

---

## 🔑 Default Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@simple.com | Admin@123 |
| Admin | admin@simple.com | Admin@123 |
| Operator | operator@simple.com | Admin@123 |

> ⚠️ **Change passwords immediately after first login!**

---

## 📁 Project Structure

```
simple-scooter/
├── app/
│   ├── Controllers/      # MVC Controllers
│   ├── Models/           # Database Models
│   ├── Middleware/       # Auth & Helpers
│   └── Views/            # HTML Templates
│       ├── layout/       # Header/Footer
│       ├── auth/         # Login page
│       ├── dashboard/    # Main dashboard
│       ├── visitors/     # Visitor management
│       ├── leads/        # CRM & Follow-ups
│       ├── customers/    # Customer management
│       ├── inventory/    # Stock management
│       ├── sales/        # Sales & Invoices
│       ├── reports/      # Analytics reports
│       └── settings/     # System settings
├── config/
│   ├── config.php        # Application config
│   └── database.php      # Database class
├── database/
│   └── schema.sql        # Complete DB schema
├── routes/
│   ├── Router.php        # URL router
│   └── web.php           # Route definitions
├── public/               # Web root
│   ├── index.php         # Front controller
│   ├── .htaccess         # URL rewriting
│   ├── css/app.css       # Main stylesheet
│   ├── js/app.js         # Main JavaScript
│   └── uploads/          # User uploads
└── storage/logs/         # Application logs
```

---

## 🔐 Security Features

✅ BCrypt password hashing (cost 12)
✅ CSRF protection on all forms
✅ SQL Injection prevention (Prepared Statements)
✅ XSS prevention (htmlspecialchars)
✅ Login rate limiting (5 attempts → 15 min lockout)
✅ Session management with regeneration
✅ Role-based access control
✅ Audit trail logging
✅ File upload validation (MIME type)
✅ HTTP security headers

---

## 👥 User Roles

| Feature | Super Admin | Admin | Operator |
|---------|-------------|-------|----------|
| Dashboard | ✅ | ✅ | ✅ |
| Visitors | ✅ | ✅ | ✅ |
| Leads | ✅ | ✅ | ✅ |
| Customers | ✅ | ✅ | ✅ |
| Inventory | ✅ | ✅ | View only |
| Sales | ✅ | ✅ | ❌ |
| Reports | ✅ | ✅ | ❌ |
| Settings | ✅ | ❌ | ❌ |
| User Management | ✅ | ❌ | ❌ |

---

## 🎨 Features

### Dashboard
- Real-time KPI cards (visitors, leads, customers, sales)
- Revenue charts (Chart.js)
- Lead source analytics (pie chart)
- Today's follow-ups
- Low stock alerts
- Top selling models

### CRM
- Visitor management with lead conversion
- Lead pipeline (New → Contacted → Negotiating → Converted/Lost)
- Follow-up scheduling & tracking
- WhatsApp quick-contact integration

### Sales & Invoices
- Professional invoice generation
- Multiple payment modes (Cash/UPI/Card/Bank/EMI)
- Partial payment tracking
- Printable invoices
- Auto stock deduction

### Inventory
- Multi-model, multi-color management
- Low stock alerts
- Profit margin calculation
- Dealer tracking

### Reports
- Daily sales & collection reports
- Monthly revenue analytics
- Operator performance
- Top model rankings

---

## 🔌 API Endpoints (Future)
The system is REST API ready. Add JSON responses to controllers for mobile app integration.

---

## ☁️ Cloud Deployment

### cPanel / Hosting
1. Upload files to `public_html/simple-scooter/`
2. Update `APP_URL` in config
3. Import database via phpMyAdmin
4. Update DB credentials

### Environment Variables (Production)
```php
define('APP_ENV', 'production');
define('DB_PASS', 'your_secure_password');
```

---

## 🆘 Troubleshooting

**404 errors?** → Enable mod_rewrite + AllowOverride All
**Database errors?** → Check credentials in config/config.php
**Upload errors?** → Check uploads/ folder permissions
**White page?** → Enable PHP error display or check storage/logs/

---

## 📞 Support

System built for Simple Scooters Showroom.
Version: 1.0.0 | PHP 8.2+ | MySQL 5.7+
