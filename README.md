# College ERP System

A comprehensive Enterprise Resource Planning (ERP) system designed specifically for educational institutions. Built with PHP and following modern development practices, this system provides a complete solution for managing all aspects of college administration.

## 🚀 Features

### 👥 User Management
- Multi-role authentication system (Admin, Faculty, Student, Parent)
- Role-based access control with granular permissions
- User profile management with document uploads
- Password reset and account security features

### 🎓 Student Management
- Complete student lifecycle management
- Admission process automation
- Student registration and enrollment
- Parent/Guardian information management
- Document management system
- Transfer and alumni tracking

### 👨‍🏫 Faculty Management
- Faculty profile and qualification management
- Department and designation management
- Salary and payroll processing
- Leave management system
- Performance tracking

### 📚 Academic Management
- Course and subject management
- Class and section organization
- Timetable scheduling
- Academic session management
- Syllabus management
- Academic calendar

### 📝 Examination System
- Exam scheduling and management
- Question bank management
- Grade and mark entry
- Result processing and publishing
- Certificate generation
- Hall ticket generation

### 📊 Attendance Management
- Student attendance tracking
- Faculty attendance management
- Biometric device integration
- Attendance reports and analytics
- Leave application processing

### 💰 Fee Management
- Flexible fee structure configuration
- Online payment integration (Razorpay, Stripe, PayTM)
- Receipt generation
- Scholarship management
- Due tracking and reminders
- Discount management

### 📖 Library Management
- Book catalog management
- Issue and return tracking
- Fine calculation
- Member management
- Category and author management
- Digital library support

### 🏠 Hostel Management
- Room allocation and management
- Mess management
- Warden assignment
- Visitor management
- Complaint tracking
- Maintenance scheduling

### 🚌 Transport Management
- Vehicle and route management
- Driver management
- Stop management
- Transport fee collection
- Maintenance tracking

### 📦 Inventory Management
- Asset and equipment tracking
- Vendor management
- Purchase order processing
- Stock management
- Category-wise organization

### 💬 Communication System
- Internal messaging system
- Notification management
- Announcement broadcasting
- Event management
- Circular distribution
- Email and SMS integration

### 👔 Human Resources
- Employee management
- Payroll processing
- Recruitment tracking
- Performance evaluation
- Training management
- Policy management

### 💼 Accounts Management
- Income and expense tracking
- Voucher management
- Ledger maintenance
- Budget planning
- Financial reporting

### 📈 Reports & Analytics
- Comprehensive reporting system
- Student performance analytics
- Financial reports
- Attendance analytics
- Custom report builder
- Data export capabilities

### 🔌 API & Integration
- RESTful API for mobile apps
- Third-party integrations
- Biometric device support
- Payment gateway integration
- SMS and email services
- Cloud storage integration

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ with custom MVC framework
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Libraries**: 
  - PHPMailer for email
  - DOMPDF for PDF generation
  - PhpSpreadsheet for Excel operations
  - Intervention Image for image processing
  - Firebase JWT for API authentication
  - Monolog for logging

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ or Nginx 1.18+
- Composer for dependency management
- Node.js 14+ (for asset compilation)
- Git for version control

### PHP Extensions Required
- PDO
- JSON
- mbstring
- OpenSSL
- cURL
- GD
- ZIP
- XML

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-repo/college-erp.git
cd college-erp
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
```

Edit the `.env` file with your configuration:
```env
APP_NAME="Your College Name"
APP_URL=http://localhost
DB_HOST=127.0.0.1
DB_DATABASE=college_erp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE college_erp"

# Run migrations
php scripts/deployment/database_migrate.php

# Seed initial data
php scripts/deployment/database_seed.php
```

### 5. Set Permissions
```bash
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod -R 644 .env
```

### 6. Web Server Configuration

#### Apache
Ensure mod_rewrite is enabled and point document root to `public/` directory.

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/college-erp/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔧 Configuration

### Payment Gateways
Configure payment gateways in `.env`:
```env
# Razorpay
RAZORPAY_KEY_ID=your_key_id
RAZORPAY_KEY_SECRET=your_key_secret

# Stripe
STRIPE_PUBLIC_KEY=your_public_key
STRIPE_SECRET_KEY=your_secret_key
```

### Email Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
```

### SMS Configuration
```env
SMS_DRIVER=twilio
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=your_phone_number
```

## 👤 Default Login Credentials

After installation, use these credentials to access the system:

**Super Admin**
- Email: admin@college.edu
- Password: admin123

**Demo Student**
- Email: student@college.edu
- Password: student123

**Demo Faculty**
- Email: faculty@college.edu
- Password: faculty123

> ⚠️ **Important**: Change these default passwords immediately after installation!

## 📱 Mobile App

The system includes API endpoints for mobile applications:

- **Student App**: Attendance, results, fees, timetable
- **Faculty App**: Attendance marking, student management
- **Parent App**: Child's progress tracking, communication

API documentation is available at `/api/documentation`

## 🔒 Security Features

- CSRF protection
- SQL injection prevention
- XSS protection
- File upload security
- Rate limiting
- Session security
- Password hashing
- Role-based access control
- Audit logging

## 🧪 Testing

Run the test suite:
```bash
composer test
```

Run with coverage:
```bash
composer test-coverage
```

## 📊 Performance

- Database query optimization
- Caching system (File/Redis/Memcached)
- Image optimization
- Asset minification
- Gzip compression
- Browser caching

## 🔄 Backup & Maintenance

### Automated Backups
```bash
# Database backup
php scripts/maintenance/backup_database.php

# Full system backup
php scripts/maintenance/full_backup.php
```

### Maintenance Tasks
```bash
# Clear cache
php scripts/maintenance/clear_cache.php

# Clean logs
php scripts/maintenance/cleanup_logs.php

# Optimize database
php scripts/maintenance/optimize_database.php
```

## 🌐 Multi-language Support

The system supports multiple languages:
- English (default)
- Hindi
- Spanish
- French
- German

Add new languages by creating translation files in `/resources/lang/`

## 📈 Monitoring & Analytics

- Application performance monitoring
- Error tracking and logging
- User activity analytics
- System resource monitoring
- Custom dashboard metrics

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

- **Documentation**: [docs.college-erp.com](https://docs.college-erp.com)
- **Issues**: [GitHub Issues](https://github.com/your-repo/college-erp/issues)
- **Email**: support@college-erp.com
- **Community**: [Discord Server](https://discord.gg/college-erp)

## 🎯 Roadmap

### Version 2.0
- [ ] AI-powered analytics
- [ ] Mobile app improvements
- [ ] Advanced reporting
- [ ] Integration marketplace
- [ ] Multi-tenant support

### Version 2.1
- [ ] Blockchain certificates
- [ ] IoT device integration
- [ ] Advanced security features
- [ ] Performance optimizations
- [ ] Cloud deployment tools

## 🏆 Acknowledgments

- Bootstrap team for the UI framework
- PHP community for excellent libraries
- Contributors and testers
- Educational institutions for feedback

## 📞 Contact

For enterprise support and customization:
- **Website**: [www.college-erp.com](https://www.college-erp.com)
- **Email**: enterprise@college-erp.com
- **Phone**: +1-234-567-8900

---

**Made with ❤️ for educational institutions worldwide**