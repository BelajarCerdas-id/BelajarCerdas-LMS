# BelajarCerdas LMS

**BelajarCerdas** is a comprehensive Learning Management System (LMS) built with Laravel 12, designed to provide a complete digital learning solution for schools and educational institutions in Indonesia.

<p align="center">
<a href="https://laravel.com" target="_blank"><img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel Version"></a>
<a href="https://www.php.net/" target="_blank"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP Version"></a>
<a href="https://pusher.com/" target="_blank"><img src="https://img.shields.io/badge/Pusher-Realtime-300D4F?style=flat-square&logo=pusher&logoColor=white" alt="Pusher"></a>
<img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

---

## 📚 Table of Contents

- [Features](#-features)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [LMS Modules](#-lms-modules)
- [Learning Features](#-learning-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### Core LMS Features

#### 🏢 **Administrator Management**
- **School Subscription Management** - Manage school subscriptions and access
- **Account Management** - Create and manage user accounts with role-based access
- **Class Management** - CRUD operations for school classes
- **Major Management** - Manage school majors/specializations
- **Student Management** - Manage student enrollment and class assignments
- **Academic Management** - Manage academic years and terms
- **Assessment Type Management** - Configure assessment types and grading
- **Content Management** - Review and approve learning content
- **Question Bank Management** - Oversee question banks across schools
- **Subject-Teacher Management** - Assign teachers to subjects

#### 👨‍🏫 **Teacher Features**
- **Content Management** - Create, edit, and manage learning content
- **Content Release** - Schedule and release content to students
- **Question Bank Management** - Create and manage questions (PG/Uraian)
- **Assessment Management** - Create and manage assignments and exams
- **Bulk Upload** - Upload questions via Word/Excel files
- **Real-time Updates** - Pusher-powered live notifications

#### 🎓 **Student Features**
- **Learning Content Access** - Access assigned learning materials
- **Assessment Participation** - Take quizzes and assignments
- **Progress Tracking** - Monitor learning progress
- **Public Library** - Access digital library resources

### 🧠 Learning Features

#### **1. TKA Exam (Tes Kemampuan Akademik)**
Academic ability assessment with:
- Multiple choice questions
- Timed examinations
- Automatic grading
- Attempt tracking and history

#### **2. Practice Exam**
Practice testing system featuring:
- Self-paced practice questions
- Instant answer feedback
- Progress tracking
- Topic-based categorization

#### **3. Virtual Lab**
Interactive virtual laboratory with:
- 3D/2D lab simulations
- Step-by-step experiment guides
- Lab report submissions
- Review and rating system
- View tracking

#### **4. Learning Resources**
Digital resource management:
- Multi-format support (PDF, Video, PPTX, etc.)
- Thumbnail previews
- Search and filter capabilities
- Resource categorization by curriculum

#### **5. Public Library**
Digital library system:
- File upload and management
- Preview before download
- Search with filters
- Category organization

### 🇮🇩 **Indonesia Region Support**
- Province, City, District, Village data seeder
- Localized address management
- Indonesian education system alignment

---

## 💻 System Requirements

| Requirement | Version |
|------------|---------|
| PHP | ^8.2 |
| Laravel | ^12.0 |
| Database | MySQL 8.0+ / PostgreSQL |
| Node.js | 18.x+ |
| Composer | Latest |
| NPM/PNPM | Latest |

### Required PHP Extensions
- `bcmath`
- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `gd` / `imagick`
- `json`
- `mbstring`
- `openssl`
- `pdo`
- `tokenizer`
- `xml`
- `zip`

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/BelajarCerdas-id/BelajarCerdas-LMS.git
cd BelajarCerdas-LMS
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
# or
pnpm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=belajarcerdas
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Seed Indonesia regions (optional)
php artisan db:seed IndonesiaRegionSeeder

# Seed learning features
php artisan db:seed LearningFeaturesSeeder

# Seed dummy data for testing (optional)
php artisan db:seed TkaExamDummySeeder
php artisan db:seed LearningResourceDummySeeder
```

### 6. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Storage Link

```bash
php artisan storage:link
```

---

## ⚙️ Configuration

### Pusher Configuration (Real-time Events)

```env
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=ap1
```

### File Upload Configuration

```env
# Maximum file upload size (in MB)
MAX_UPLOAD_SIZE=50

# Allowed file types
ALLOWED_DOCUMENT_TYPES=pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,png,mp4,webm
```

---

## 📖 Usage

### Development Mode

Run the development server with all workers:

```bash
composer dev
```

This starts:
- Laravel development server
- Queue worker
- Log viewer (Pail)
- Vite development server

### Manual Start

```bash
# Terminal 1: Web server
php artisan serve

# Terminal 2: Queue worker
php artisan queue:work

# Terminal 3: Vite
npm run dev
```

### Testing

```bash
# Run all tests
composer test

# Run with coverage
php artisan test --coverage
```

---

## 🏫 LMS Modules

### Administrator Modules

| Module | Description |
|--------|-------------|
| School Subscription | Manage school subscriptions and features |
| Account Management | User accounts and role assignment |
| Class Management | Create and manage school classes |
| Major Management | Manage academic majors |
| Student Management | Student enrollment and assignments |
| Academic Management | Academic year/term configuration |
| Assessment Type | Grading system configuration |
| Content Management | Review and approve content |
| Question Bank | Centralized question management |
| Teacher Assignment | Subject-teacher mapping |

### Teacher Modules

| Module | Description |
|--------|-------------|
| Content Management | Create and manage learning content |
| Content Release | Schedule content for students |
| Question Bank | Create/edit questions (PG/Uraian) |
| Assessment | Create assignments and exams |
| Bulk Upload | Mass upload via Word/Excel |

---

## 🎯 Learning Features

### TKA Exam System

```php
// Models
TkaExam, TkaExamQuestion, TkaExamAnswer, TkaExamAttempt
```

**Features:**
- Timed examinations
- Random question shuffling
- Auto-grading for multiple choice
- Attempt history tracking
- Score analytics

### Practice Exam System

```php
// Models
PracticeExam, PracticeExamQuestion, PracticeExamAnswer, PracticeExamAttempt
```

**Features:**
- Self-paced practice
- Instant feedback
- Topic categorization
- Progress tracking

### Virtual Lab

```php
// Models
VirtualLab, VirtualLabView, VirtualLabReview
```

**Features:**
- Interactive lab simulations
- View tracking
- Review and rating system
- Lab report submission

### Learning Resources

```php
// Models
LearningResource
```

**Features:**
- Multi-format support (PDF, Video, PPTX)
- Thumbnail generation
- Search and filtering
- Curriculum alignment

---

## 🛠 Technology Stack

### Backend
- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Database:** MySQL / PostgreSQL
- **Cache:** Redis / File
- **Queue:** Database / Redis
- **Real-time:** Pusher

### Frontend
- **Build Tool:** Vite
- **CSS:** Bootstrap 5 + Custom CSS
- **JavaScript:** Vanilla JS + jQuery
- **Icons:** Bootstrap Icons / FontAwesome
- **Charts:** Chart.js

### Packages
- **maatwebsite/excel** - Excel/CSV import-export
- **pusher/pusher-php-server** - Real-time events
- **laravel/pail** - Log viewer
- **phpunit/phpunit** - Testing framework

---

## 📁 Project Structure

```
BelajarCerdas-LMS/
├── app/
│   ├── Events/              # Broadcast events (Pusher)
│   ├── Http/
│   │   ├── Controllers/     # Request handlers
│   │   └── Middleware/      # Request middleware
│   ├── Imports/             # Excel/Word imports
│   ├── Models/              # Eloquent models
│   ├── Providers/           # Service providers
│   └── Services/            # Business logic services
├── bootstrap/
├── config/                  # Configuration files
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── public/
│   ├── assets/              # CSS, JS, images
│   ├── uploads/             # User uploads
│   └── lms-contents/        # LMS content files
├── resources/
│   ├── css/
│   ├── js/
│   └── views/               # Blade templates
├── routes/
│   ├── web.php              # Web routes
│   └── console.php          # Console routes
├── storage/
└── tests/
```

---

## 🤝 Contributing

Thank you for considering contributing to BelajarCerdas LMS!

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'feat: Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Commit Message Convention

We follow a simple commit message format:

```
feat: Add new feature
fix: Fix bug
docs: Update documentation
style: Format code
refactor: Refactor code
test: Add tests
chore: Update dependencies
```

---

## 📄 License

The BelajarCerdas LMS is proprietary software. All rights reserved.

For licensing inquiries, please contact the development team.

---

## 📞 Support

For support and inquiries:
- Email: support@belajarcerdas.id
- Documentation: [Internal Wiki](#)

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The web framework used
- [Pusher](https://pusher.com) - Real-time messaging
- [Maatwebsite Excel](https://docs.laravel-excel.com) - Excel handling
- All contributors and supporters

---

<p align="center">Made with ❤️ by BelajarCerdas Team</p>
