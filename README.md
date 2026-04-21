# ByteClass — Learn · Build · Grow

<div align="center">

![ByteClass Banner](<img width="1363" height="684" alt="Homepage" src="https://github.com/user-attachments/assets/75700e92-404e-4fc2-b821-bf512c05bcbc" />
)

**Kenya's premier online technology education platform**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-10.4+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=for-the-badge)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0-green?style=for-the-badge)](CHANGELOG.md)

[Live Demo](#) · [Documentation](#) · [Report Bug](#) · [Request Feature](#)

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Screenshots](#-screenshots)
- [Tech Stack](#-tech-stack)
- [Features](#-features)
- [Architecture](#-architecture)
- [Folder Structure](#-folder-structure)
- [Database Schema](#-database-schema)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [User Roles](#-user-roles)
- [Payment Gateways](#-payment-gateways)
- [AI Tutor — LearnPulse](#-ai-tutor--learnpulse)
- [Points & Gamification](#-points--gamification)
- [Demo Course Content](#-demo-course-content)
- [API Reference](#-api-reference)
- [Security](#-security)
- [Cron Jobs](#-cron-jobs)
- [Files to Delete (Legacy React)](#-files-to-delete-legacy-react)
- [Roadmap](#-roadmap)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🌍 Overview

ByteClass is a full-featured, production-ready **Learning Management System (LMS)** built for African tech education. It connects ambitious learners with expert instructors across disciplines including IT Support, Cybersecurity, Networking, Cloud Computing, and more.

### Core Philosophy

> *"Talent is evenly distributed, but opportunity is not. ByteClass exists to change that."*

ByteClass provides:
- **Students** with structured, industry-aligned courses, AI-powered tutoring, certificates, and a gamified learning experience
- **Lecturers** with complete tools to create content, schedule sessions, manage students, and track their performance
- **Administrators** with full platform control — from user management to payment processing, HR, and system analytics

---

## 📸 Screenshots

| Page | Description |
|------|-------------|
| **Homepage** | Hero with course catalog, AI tutor preview, testimonials, statistics |
| **Admin Dashboard** | Real-time stats, recent registrations, payment overview |
| **Student Dashboard** | Enrolled courses with progress, leaderboard, upcoming sessions |
| **Lecturer Dashboard** | Course management, student roster, session scheduling, reviews |
| **LearnPulse AI** | 24/7 AI chat tutor powered by Google Gemini |
| **Course Player** | Video lessons, written content, quizzes with progress tracking |

---

## 🛠️ Tech Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.0+ | Core application logic |
| **MariaDB / MySQL** | 10.4+ | Primary database |
| **PDO** | Built-in | Database abstraction layer |
| **PHPMailer** | 6.x | Email delivery (SMTP) |
| **mPDF** | 8.x | PDF certificate generation |
| **endroid/qr-code** | 4.x | QR code generation for certificates |
| **intervention/image** | 2.x | Image processing & resizing |
| **firebase/php-jwt** | 6.x | JWT token management |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Tailwind CSS** | 3.x (CDN) | Utility-first styling |
| **Lucide Icons** | Latest (CDN) | Icon system |
| **Inter Font** | Google Fonts | Typography |
| **Vanilla JavaScript** | ES6+ | UI interactions |

### Payment Gateways
| Gateway | Region | Type |
|---------|--------|------|
| **M-Pesa (Daraja API)** | Kenya | Mobile money |
| **Stripe** | Global | Card payments |
| **PayPal** | Global | Online wallet |
| **Paystack** | Africa | Multi-method |

### AI Integration
| Provider | Model | Purpose |
|----------|-------|---------|
| **Google Gemini** | gemini-1.5-flash | LearnPulse AI tutor |
| **xAI Grok** | grok-beta | Alternative AI provider |

### Development Environment
- **XAMPP** (Windows) — Apache + PHP + MariaDB
- **phpMyAdmin** — Database management
- **VS Code** — Code editor

---

## ✨ Features

### 🎓 Student Features
- **Account Management** — Registration with email verification, profile photo upload, email & password updates
- **Course Enrollment** — Browse, search, filter courses by category/difficulty/price; free & paid enrollment
- **Lesson Player** — Video embeds (YouTube), written content, downloadable resources
- **Quiz System** — Multiple-choice quizzes with instant scoring and passmark tracking
- **Progress Tracking** — Per-lesson and per-module completion tracking with visual progress bars
- **LearnPulse AI** — 24/7 AI tutor (Google Gemini / Grok) for course-related questions
- **Live Sessions** — Join scheduled Zoom/Google Meet classes from the dashboard
- **Certificates** — Auto-generated PDF certificates with QR codes upon course completion
- **Points & Leaderboard** — Gamified points system with a student leaderboard
- **Community Chat** — Platform-wide chat (messages auto-delete after 24 hours)
- **Support Tickets** — Create and track support tickets with threaded replies
- **Payment Processing** — M-Pesa, Stripe, PayPal, Paystack — secure enrollment payments

### 👨‍🏫 Lecturer Features
- **Dashboard** — Overview of students, courses, upcoming sessions, performance reviews
- **Course Content** — Create modules and lessons (video + written content + duration)
- **Lesson Management** — Draft/publish workflow for lessons; students only see published content
- **Session Scheduling** — Schedule live classes with platform, meeting link, duration, auto-notification to students
- **Student Management** — View all enrolled students across courses with progress
- **Performance Reviews** — View admin-submitted reviews with star ratings and feedback
- **Payslips** — View payment history recorded by administrators
- **HR Forms** — Submit leave requests, complaints, and general HR forms
- **Community** — Participate in platform-wide community chat
- **Support** — Submit support tickets to administrators
- **Profile** — Update personal info, bio, profile photo, password

### 🛡️ Admin Features
- **Dashboard** — Real-time stats: students, lecturers, active courses, revenue, open tickets, pending approvals
- **User Management** — View/search all users; create lecturers; activate/deactivate; unlock locked accounts; reset passwords
- **Course Management** — Create/edit/publish/unpublish/archive courses; assign lecturers; thumbnail upload; free or paid pricing
- **Finance** — Full payment history with gateway filters; record lecturer payslips; trigger retry payment banners
- **Announcements** — Create pinnable announcements with audience targeting, offer/discount flags, expiry timers
- **HR & Approvals** — Review and approve/reject leave requests with notes; respond to HR forms; upload lecturer contracts
- **Lecturer Reviews** — Submit star-rated performance reviews with strengths, improvements, and comments
- **Activity Logs** — Full audit trail of all platform actions; filter by user, action, date range; email log viewer
- **Support Tickets** — Split-pane ticket management with thread view, reply, priority, assignment, status updates
- **System Settings** — General, SMTP, Security, Payments, AI/LearnPulse, Maintenance mode, Social links
- **Profile** — Update name, email, profile photo (auto-resized), password

### 🌐 Public Pages
- **Homepage** — Professional hero, statistics, features, course catalog, AI tutor preview, testimonials, CTA
- **Courses** — Full course catalog with search, category filter, difficulty filter, price filter, pagination
- **About** — Mission, values, statistics, platform features — no team section
- **FAQ** — Categorized expandable FAQ covering all aspects of the platform
- **Contact** — Contact form with email notification to admin, quick links
- **Terms & Conditions** — Comprehensive, detailed legal terms (12 sections)
- **Privacy Policy** — GDPR-aligned detailed privacy policy (11 sections)
- **Floating Chat Widget** — 24/7 public support chat on all public pages; conversations deleted on close

---

## 🏗️ Architecture

```
ByteClass/
├── HTTP Request
│       ↓
├── Apache (.htaccess routing)
│       ↓
├── PHP Pages (admin/ student/ lecturer/ pages/)
│       ↓
├── Middleware (auth-check.php → role verification)
│       ↓
├── Helpers (response, mailer, upload, points, jwt, otp)
│       ↓
├── Config (database, session, constants, cors)
│       ↓
└── MariaDB (byteclass_db — 34 tables)
```

### Request Flow
1. User hits a URL (e.g., `/admin/dashboard.php`)
2. `.htaccess` passes to Apache/PHP
3. Page includes `auth-check.php` → verifies `$_SESSION` and DB session token
4. Role is synced from DB (prevents tampering)
5. If unauthorized → redirect to login
6. Page queries DB via PDO singleton (`Database::getInstance()`)
7. HTML rendered server-side with Tailwind CSS

### Session Architecture
- **PHP Sessions** store: `user_id`, `role`, `full_name`, `email`, `photo`, `token`
- **Database Sessions** (`user_sessions` table) store: JWT token, device type, IP, user agent
- Sessions are **cross-validated** on every protected page load
- Maximum 2 simultaneous device sessions per user (configurable)
- Sessions are invalidated on password change, account deactivation, and manual logout

---

## 📁 Folder Structure

```
C:\xampp\htdocs\ByteClass\
│
├── admin/                          # Admin dashboard pages
│   ├── dashboard.php               # Admin home with stats
│   ├── users.php                   # User management
│   ├── courses.php                 # Course management
│   ├── finance.php                 # Payment history & payslips
│   ├── announcements.php           # Platform announcements
│   ├── hr.php                      # Leave requests, HR forms, contracts
│   ├── reviews.php                 # Lecturer performance reviews
│   ├── logs.php                    # Activity audit logs
│   ├── settings.php                # System settings (SMTP, payments, AI, etc.)
│   └── support.php                 # Support ticket management
│
├── api/                            # REST API endpoints
│   ├── auth/
│   │   ├── login.php               # Login (session + JWT)
│   │   └── logout.php              # Session destruction
│   ├── ai/
│   │   └── chat.php                # LearnPulse AI (Gemini / Grok)
│   ├── chat/
│   │   └── public.php              # Public floating chat API
│   ├── notifications/
│   │   └── mark_all_read.php       # Mark notifications as read
│   ├── payments/
│   │   ├── mpesa.php               # M-Pesa Daraja STK Push
│   │   ├── stripe.php              # Stripe checkout
│   │   ├── paypal.php              # PayPal order creation
│   │   └── paystack.php            # Paystack transaction init
│   └── students/
│       └── dashboard.php           # Student dashboard data API
│
├── assets/
│   ├── css/
│   │   └── app.css                 # Custom CSS overrides
│   └── js/
│       └── app.js                  # Global JS utilities
│
├── config/
│   ├── constants.php               # APP_URL, MAX_DEVICES, TICKET_PREFIX, etc.
│   ├── cors.php                    # CORS headers for API routes
│   ├── database.php                # PDO singleton connection
│   ├── mail.php                    # PHPMailer base config
│   └── session.php                 # Session initialization
│
├── cron/                           # Scheduled tasks (run via Task Scheduler)
│   ├── delete-messages.php         # Delete community messages > 24hrs
│   ├── expire-announcements.php    # Close expired announcements
│   ├── contract-expiry.php         # Alert admins of expiring contracts
│   └── reset-attempts.php          # Reset login attempt counters
│
├── database/
│   ├── schema.sql                  # Full 34-table database schema
│   └── seeds.sql                   # Admin accounts + system settings
│
├── helpers/
│   ├── jwt.php                     # JWT encode/decode
│   ├── mailer.php                  # All email functions (verification, OTP, temp password, etc.)
│   ├── otp.php                     # OTP generation, storage, verification (for 2FA)
│   ├── pdf.php                     # Certificate PDF generation (mPDF)
│   ├── points.php                  # Points award system
│   ├── response.php                # respond_success(), respond_error(), sanitize(), etc.
│   └── upload.php                  # Profile photo upload with GD resizing
│
├── includes/                       # Shared UI components
│   ├── auth-check.php              # Session guard + role enforcement
│   ├── floating-chat.php           # Public chat widget
│   ├── footer.php                  # Public footer with social links
│   ├── footer-minimal.php          # Dashboard footer (copyright only)
│   ├── head.php                    # HTML head (Tailwind CDN, Lucide, fonts)
│   ├── navbar.php                  # Public navbar with mobile menu
│   ├── navbar-admin.php            # Admin navbar (bell, profile photo, notifications)
│   ├── navbar-lecturer.php         # Lecturer navbar
│   ├── navbar-student.php          # Student navbar
│   ├── scripts.php                 # toggleSidebar(), api(), toast(), auto-logout
│   ├── sidebar-admin.php           # Admin sidebar (gradient, ByteClass brand)
│   ├── sidebar-lecturer.php        # Lecturer sidebar
│   └── sidebar-student.php         # Student sidebar
│
├── lecturer/                       # Lecturer dashboard pages
│   ├── dashboard.php
│   ├── courses.php                 # Assigned courses overview
│   ├── lessons.php                 # Module & lesson management
│   ├── students.php                # Enrolled students
│   ├── sessions.php                # Schedule & manage live sessions
│   ├── reviews.php                 # View performance reviews
│   ├── payslips.php                # View payment history
│   ├── hr.php                      # Leave requests & HR forms
│   ├── community.php               # Community chat
│   ├── support.php                 # Support tickets
│   └── profile.php                 # Profile management
│
├── middleware/
│   ├── auth.php                    # authenticate() — JWT validation for API
│   └── ratelimit.php               # Login rate limiting (5 attempts → lock)
│
├── pages/
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── verify-email.php
│   │   ├── forgot-password.php
│   │   └── reset-password.php
│   ├── errors/
│   │   ├── 404.php                 # Smart 404 (redirects logged-in users)
│   │   ├── 500.php                 # Server error
│   │   └── maintenance.php         # Maintenance mode page
│   └── public/
│       ├── index.php               # Homepage
│       ├── courses.php             # Public course catalog
│       ├── about.php               # About ByteClass
│       ├── faq.php                 # FAQ (5 categories, 20+ questions)
│       ├── contact.php             # Contact form
│       ├── terms.php               # Terms & Conditions
│       └── privacy.php             # Privacy Policy
│
├── student/                        # Student dashboard pages
│   ├── dashboard.php
│   ├── courses.php                 # My enrolled courses
│   ├── explore.php                 # Course catalog (enrolled view)
│   ├── enroll.php                  # Free course enrollment
│   ├── payment.php                 # Payment gateway selection
│   ├── payment-gateway.php         # Gateway redirect & callback handler
│   ├── lesson-player.php           # Video + content + quiz player
│   ├── sessions.php                # Upcoming & past class sessions
│   ├── submissions.php             # Quiz attempts & scores
│   ├── leaderboard.php             # Full student leaderboard with podium
│   ├── certificates.php            # Earned certificates
│   ├── community.php               # Community chat
│   ├── learnpulse.php              # LearnPulse AI chat
│   ├── support.php                 # Support tickets
│   └── profile.php                 # Profile management
│
├── uploads/                        # User-generated files (gitignored)
│   ├── profile_photos/             # User profile images (200×200px)
│   ├── course_images/              # Course thumbnails
│   ├── announcements/              # Announcement images
│   └── contracts/                  # Lecturer contract PDFs
│
├── .htaccess                       # Apache URL & security rules
├── composer.json                   # PHP dependencies
├── index.php                       # Root redirect → homepage
└── README.md                       # This file
```

---

## 🗄️ Database Schema

ByteClass uses **34 tables** in MariaDB (`byteclass_db`).

### Core Tables

| Table | Description |
|-------|-------------|
| `users` | All users (admin, lecturer, student) with roles, status, points |
| `user_sessions` | Active login sessions (JWT token, device, IP) |
| `admin_profiles` | Admin-specific data (title: System Administrator / Principal) |
| `lecturer_profiles` | Lecturer department, bio, contract dates |
| `student_profiles` | Student-specific profile data |

### Course Tables

| Table | Description |
|-------|-------------|
| `courses` | Course catalog (name, overview, category, difficulty, pricing, status) |
| `course_lecturers` | Many-to-many: courses ↔ lecturers |
| `modules` | Course sections/chapters |
| `lessons` | Individual lessons (video_url, content, duration, sort_order) |
| `lesson_progress` | Per-student lesson completion tracking |
| `enrollments` | Student ↔ course enrollments with completion timestamp |

### Assessment Tables

| Table | Description |
|-------|-------------|
| `quizzes` | Quiz definitions linked to lessons |
| `quiz_questions` | Individual quiz questions |
| `quiz_options` | Answer choices (with is_correct flag) |
| `quiz_attempts` | Student quiz submissions (score, passed, attempted_at) |

### Financial Tables

| Table | Description |
|-------|-------------|
| `payments` | All payment records (gateway, amount, status, receipt_id) |
| `payslips` | Lecturer salary payment records |

### Communication Tables

| Table | Description |
|-------|-------------|
| `community_messages` | Platform chat (auto-deleted after 24h) |
| `public_chat_sessions` | Floating chat widget messages (deleted on close) |
| `notifications` | In-app notifications (bell icon) |
| `support_tickets` | Support request tickets |
| `ticket_replies` | Threaded replies on support tickets |

### HR & Admin Tables

| Table | Description |
|-------|-------------|
| `announcements` | Platform announcements (pinnable, audience-targeted, expirable) |
| `leave_requests` | Lecturer leave applications |
| `hr_forms` | General HR form submissions |
| `lecturer_reviews` | Admin performance reviews for lecturers |
| `certificates` | Earned certificates (with PDF path and QR code) |
| `class_sessions` | Scheduled live classes |
| `activity_logs` | Full audit trail of all platform actions |
| `password_resets` | Secure password reset tokens |
| `system_settings` | All configurable platform settings (key/value) |

---

## 🚀 Installation

### Prerequisites

- **XAMPP** 8.0+ (or any PHP 8.0+ + MySQL environment)
- **Composer** (PHP dependency manager)
- Internet access (for Tailwind CDN, Lucide CDN, Unsplash images)

### Step-by-Step Setup

#### 1. Clone / Copy the Project

```bash
# Place project in XAMPP htdocs
C:\xampp\htdocs\ByteClass\
```

#### 2. Install PHP Dependencies

```powershell
cd C:\xampp\htdocs\ByteClass
composer install
```

Required packages (from `composer.json`):
```json
{
  "require": {
    "phpmailer/phpmailer": "^6.8",
    "mpdf/mpdf": "^8.2",
    "endroid/qr-code": "^4.8",
    "intervention/image": "^2.7",
    "firebase/php-jwt": "^6.10"
  }
}
```

#### 3. Create the Database

Open **phpMyAdmin** → Create database `byteclass_db` (Collation: `utf8mb4_unicode_ci`)

Then import:
```sql
-- Step 1: Run schema
SOURCE C:/xampp/htdocs/ByteClass/database/schema.sql;

-- Step 2: Run seeds (creates admin accounts + 57 system settings)
SOURCE C:/xampp/htdocs/ByteClass/database/seeds.sql;
```

#### 4. Set Admin Passwords

```bash
# Generate bcrypt hash for your admin password
php -r "echo password_hash('YourSecurePassword123!', PASSWORD_BCRYPT, ['cost'=>12]);"
```

```sql
-- Update both admin accounts
UPDATE users 
SET password_hash = 'PASTE_HASH_HERE', 
    status = 'active', 
    email_verified = 1 
WHERE role = 'admin';
```

#### 5. Configure Constants

Edit `config/constants.php`:

```php
define('APP_URL',    'http://localhost/ByteClass');
define('APP_NAME',   'ByteClass');
define('DB_HOST',    'localhost');
define('DB_NAME',    'byteclass_db');
define('DB_USER',    'root');
define('DB_PASS',    '');              // Your MySQL password
define('JWT_SECRET', 'CHANGE_THIS_TO_A_LONG_RANDOM_STRING_AT_LEAST_64_CHARS');
define('TWO_FA_ENABLED', false);       // Set to true in production with SMTP configured
define('MAX_DEVICES',    2);
define('TICKET_PREFIX',  'BC');
```

#### 6. Create Upload Directories

```powershell
mkdir C:\xampp\htdocs\ByteClass\uploads\profile_photos
mkdir C:\xampp\htdocs\ByteClass\uploads\course_images
mkdir C:\xampp\htdocs\ByteClass\uploads\announcements
mkdir C:\xampp\htdocs\ByteClass\uploads\contracts
mkdir C:\xampp\htdocs\ByteClass\uploads\certificates
```

#### 7. Configure SMTP (Email)

Login as admin → **System Settings → SMTP Email**:

| Setting | Value |
|---------|-------|
| SMTP Host | `smtp.gmail.com` |
| SMTP Port | `587` |
| Gmail Address | `your@gmail.com` |
| App Password | *(Generate in Google Account → Security → 2-Step → App Passwords)* |
| From Name | `ByteClass` |
| From Email | `noreply@byteclass.io` |

#### 8. Verify Installation

Visit `http://localhost/ByteClass/` — you should see the ByteClass homepage.

Login at `http://localhost/ByteClass/pages/auth/login.php`:
- **Admin 1:** `admin1@byteclass.io`
- **Admin 2:** `admin2@byteclass.io`

---

## ⚙️ Configuration

### System Settings (Admin Dashboard)

All settings are stored in the `system_settings` table and managed via **Admin → System Settings**.

| Tab | Settings |
|-----|---------|
| **General** | Platform name, tagline, contact email |
| **SMTP Email** | Host, port, username, password, from name/email |
| **Security** | Auto-logout minutes, max login attempts, max devices, 2FA toggle, reset link expiry |
| **Payments** | Enable/disable gateways; M-Pesa, Stripe, PayPal, Paystack API keys |
| **AI Tutor** | Enable LearnPulse, select provider (Gemini/Grok), API keys |
| **Maintenance** | Enable maintenance mode, custom message |
| **Social Links** | Website, WhatsApp, Telegram, X/Twitter, Facebook, Instagram |

### Environment Constants (`config/constants.php`)

```php
// App
define('APP_URL',           'http://localhost/ByteClass');
define('APP_NAME',          'ByteClass');

// Database
define('DB_HOST',           'localhost');
define('DB_NAME',           'byteclass_db');
define('DB_USER',           'root');
define('DB_PASS',           '');

// Security
define('JWT_SECRET',        'your-64-char-secret-here');
define('SESSION_LIFETIME',  3600);              // 1 hour
define('TWO_FA_ENABLED',    false);             // true in production
define('MAX_DEVICES',       2);                 // Max simultaneous logins

// Platform
define('TICKET_PREFIX',     'BC');              // Ticket IDs: BC-XXXXXXXX
define('MAX_FILE_SIZE',     5 * 1024 * 1024);  // 5MB upload limit
define('PROFILE_PHOTO_MAX', 200);              // px — profile photo size
```

---

## 👤 User Roles

### Admin (Maximum 2 accounts)

| Account | Email | Title |
|---------|-------|-------|
| Admin 1 | `admin1@byteclass.io` | System Administrator |
| Admin 2 | `admin2@byteclass.io` | Principal |

Admins are pre-seeded. Additional admins cannot be created through the UI — only via direct DB insertion.

### Lecturer

- Created **only by Admin** through User Management → Add Lecturer
- Credentials are emailed with a temporary password
- Must change password on first login
- Cannot self-register

### Student

- Self-registers at `/pages/auth/register.php`
- Must verify email before accessing the platform
- Limited to enrolled course content

---

## 💳 Payment Gateways

### M-Pesa (Safaricom Daraja API)

**How it works:**
1. Student selects M-Pesa on the payment page
2. System initiates STK Push to student's registered phone number
3. Student enters M-Pesa PIN
4. Safaricom sends callback to `api/payments/mpesa.php?action=callback`
5. Callback verifies payment → enrolls student → awards 500 points

**Required credentials (Admin → Settings → Payments):**
- Business Shortcode
- Consumer Key
- Consumer Secret
- Callback URL: `https://yourdomain.com/ByteClass/api/payments/mpesa.php?action=callback`

**Daraja API Setup:**
1. Register at [developer.safaricom.co.ke](https://developer.safaricom.co.ke)
2. Create app → Get Consumer Key & Secret
3. Apply for Go-Live with Safaricom (for production)

### Stripe (Global Cards)

**How it works:**
1. Student selects Stripe
2. System creates a Payment Intent via Stripe API
3. Student completes payment on Stripe's secure page
4. Stripe sends webhook to `api/payments/stripe.php?action=webhook`
5. Webhook verifies → enrolls student

**Required credentials:**
- Public Key (`pk_live_...`)
- Secret Key (`sk_live_...`)
- Webhook Secret (from Stripe Dashboard → Webhooks)

### PayPal

**How it works:**
1. System creates PayPal order
2. Student redirected to PayPal to approve
3. On return, system captures the order
4. Success → enrollment

**Required credentials:**
- Client ID
- Client Secret
- Mode: `sandbox` (test) or `live` (production)

### Paystack

**How it works:**
1. System initializes transaction with Paystack
2. Student redirected to Paystack checkout
3. Payment confirmed via webhook
4. Enrollment triggered

**Required credentials:**
- Public Key (`pk_live_...`)
- Secret Key (`sk_live_...`)

> **Note:** To activate payments, add your API keys in **Admin → System Settings → Payments** and enable the desired gateways. No code changes required.

---

## 🤖 AI Tutor — LearnPulse

LearnPulse is ByteClass's built-in AI tutor, available 24/7 to students.

### Setup

1. **Get API Key:**
   - **Gemini:** [aistudio.google.com/apikey](https://aistudio.google.com/apikey) — Free tier: 1,500 requests/day
   - **Grok:** [console.x.ai](https://console.x.ai) — Paid API

2. **Configure in Admin:**
   - Go to **Admin → System Settings → AI Tutor**
   - Enable LearnPulse
   - Select provider (Gemini recommended for free tier)
   - Paste API key
   - Save

3. **Students access at:** `student/learnpulse.php`

### System Prompt

LearnPulse is pre-configured with a system prompt that:
- Identifies it as a ByteClass tech education assistant
- Keeps responses educational, concise, and friendly
- Covers all tech domains taught on the platform
- Redirects platform-specific questions (pricing, enrollment) to administrators

### API Endpoint

```
POST /api/ai/chat.php
Content-Type: application/json
Authorization: Bearer {session_token}

{
  "message": "What is a firewall and how does it work?"
}
```

Response:
```json
{
  "success": true,
  "message": "AI response generated.",
  "data": {
    "reply": "A firewall is a network security device..."
  }
}
```

---

## 🏆 Points & Gamification

### Points Earning Rules

| Action | Points Awarded |
|--------|---------------|
| First login ever (welcome bonus) | +1,000 |
| Daily login | +50 |
| Lesson completed | +100 |
| Quiz passed | +100 |
| Module completed | +500 |
| Course enrolled | +500 |
| Course completed | +1,000 |

### Leaderboard

- Displays top 50 students ranked by total points
- Podium display for top 3 (gold, silver, bronze)
- Student's own position always shown at the bottom
- Visible to all students at `student/leaderboard.php`
- Also shown as a widget on the student dashboard

### Points Function (PHP)

```php
// Award points to a student
award_points($user_id, 500, 'Enrolled in: IT Support Fundamentals');

// Award daily login bonus (once per calendar day)
award_daily_login($user_id);
```

---

## 📚 Demo Course Content

### IT Support Fundamentals (Free Course)

This course has been seeded into the database as a demo.

**Course Details:**
- **Category:** IT Support
- **Difficulty:** Beginner
- **Price:** KES 0 (Free)
- **Status:** Published

#### Module Structure

**Module 1: Introduction to Computer Basics**
- *Lesson 1.1:* What is a Computer? — Hardware vs Software
- *Lesson 1.2:* Operating Systems — Windows, Linux & macOS Overview

**Module 2: Networking Fundamentals**
- *Lesson 2.1:* What is a Network? — LAN, WAN, and the Internet
- *Lesson 2.2:* IP Addresses & DNS — How the Internet Finds Your Computer

#### To Seed the Demo Content

Run in phpMyAdmin:

```sql
-- Get the IT Support course ID
SET @course_id = (SELECT id FROM courses WHERE slug LIKE 'intro-computer%' LIMIT 1);

-- Module 1
INSERT INTO modules (course_id, title, description, sort_order)
VALUES (@course_id, 'Introduction to Computer Basics', 'Start here — the absolute fundamentals of computing.', 1);
SET @mod1 = LAST_INSERT_ID();

-- Lesson 1.1
INSERT INTO lessons (module_id, title, content, video_url, duration_min, sort_order, status)
VALUES (@mod1,
'What is a Computer? — Hardware vs Software',
'In this lesson we break down the two fundamental components of every computer system: hardware (the physical parts) and software (the programs and operating systems that run on the hardware).\n\n**Hardware** includes:\n- CPU (Central Processing Unit) — the "brain"\n- RAM (Memory) — temporary storage for active programs\n- Storage (HDD/SSD) — permanent data storage\n- Motherboard — connects everything\n- Input devices — keyboard, mouse, microphone\n- Output devices — monitor, speakers, printer\n\n**Software** includes:\n- Operating System (Windows, Linux, macOS)\n- Applications (Word, Chrome, VS Code)\n- Drivers — software that lets hardware communicate with the OS\n\n**Key Takeaway:** Hardware is what you can touch; software is what runs on it.',
'https://www.youtube.com/embed/Bv7b7RHN7pU',
20, 1, 'published');

-- Lesson 1.2
INSERT INTO lessons (module_id, title, content, video_url, duration_min, sort_order, status)
VALUES (@mod1,
'Operating Systems — Windows, Linux & macOS Overview',
'An Operating System (OS) is the software that manages all hardware resources and provides the foundation for all other software to run.\n\n**Windows**\n- Most popular desktop OS (~70% market share)\n- Great for gaming and business software\n- Used widely in corporate IT environments\n- GUI-first, user-friendly\n\n**Linux**\n- Open source and free\n- Powers most web servers and cloud infrastructure\n- Multiple distributions (Ubuntu, Kali, CentOS, Debian)\n- Essential for IT professionals and cybersecurity\n- Command-line focused (though desktop versions exist)\n\n**macOS**\n- Apple-exclusive operating system\n- Unix-based (similar to Linux)\n- Popular with developers and creatives\n\n**For IT Support:** You will work with all three. Start with Windows (most corporate environments), then learn Linux basics.',
'https://www.youtube.com/embed/pTdSs8kQqSA',
25, 2, 'published');

-- Module 2
INSERT INTO modules (course_id, title, description, sort_order)
VALUES (@course_id, 'Networking Fundamentals', 'Understand how computers communicate with each other.', 2);
SET @mod2 = LAST_INSERT_ID();

-- Lesson 2.1
INSERT INTO lessons (module_id, title, content, video_url, duration_min, sort_order, status)
VALUES (@mod2,
'What is a Network? — LAN, WAN, and the Internet',
'A computer network is two or more computers connected together to share resources and communicate.\n\n**Types of Networks:**\n\n**LAN (Local Area Network)**\n- Covers a small area: home, office, school building\n- Connected via Ethernet cables or Wi-Fi\n- Fast speeds (100 Mbps to 10 Gbps)\n- Example: Your home Wi-Fi network\n\n**WAN (Wide Area Network)**\n- Covers large geographic areas: cities, countries\n- Uses telephone lines, fiber optic cables, satellites\n- Slower than LAN due to distance\n- Example: The corporate network connecting offices in Nairobi and Mombasa\n\n**The Internet**\n- The world''s largest WAN\n- Connects billions of devices globally\n- Uses TCP/IP protocol suite\n\n**Network Devices:**\n- **Router** — connects your network to the internet\n- **Switch** — connects devices within your LAN\n- **Access Point (AP)** — provides Wi-Fi coverage\n- **Modem** — converts ISP signal to usable internet\n\n**Key Takeaway:** LAN = local, WAN = wide area, Internet = global WAN.',
'https://www.youtube.com/embed/3QhU9jd03a0',
22, 1, 'published');

-- Lesson 2.2
INSERT INTO lessons (module_id, title, content, video_url, duration_min, sort_order, status)
VALUES (@mod2,
'IP Addresses & DNS — How the Internet Finds Your Computer',
'Every device on a network needs an address — just like every house needs a street address. That''s what an IP address is.\n\n**IP Address (Internet Protocol Address)**\n- A unique numerical label assigned to each device\n- Example: `192.168.1.105` (IPv4) or `2001:db8::1` (IPv6)\n\n**IPv4 vs IPv6:**\n- IPv4: 32-bit, allows ~4.3 billion addresses (running out!)\n- IPv6: 128-bit, allows 340 undecillion addresses (future-proof)\n\n**Public vs Private IP:**\n- **Private:** Used inside your LAN (e.g., 192.168.x.x, 10.x.x.x)\n- **Public:** Used on the internet — assigned by your ISP\n\n**DNS (Domain Name System)**\n- Translates human-readable domain names into IP addresses\n- Example: `google.com` → `142.250.190.78`\n- Without DNS, you''d need to remember IP addresses for every website!\n\n**How DNS Works:**\n1. You type `byteclass.io` in your browser\n2. Your computer asks the DNS resolver: "What is the IP for byteclass.io?"\n3. DNS resolver returns the IP address\n4. Your browser connects to that IP\n5. The website loads\n\n**Common DNS Servers:**\n- Google: `8.8.8.8` and `8.8.4.4`\n- Cloudflare: `1.1.1.1`\n- Your ISP''s DNS (default)\n\n**Key Takeaway:** IP addresses are device addresses; DNS translates domain names to IPs.',
'https://www.youtube.com/embed/27r4Bzuj5NQ',
28, 2, 'published');

SELECT 'Demo content seeded successfully!' AS result;
```

---

## 📡 API Reference

### Authentication

All protected API endpoints require a valid session token passed as `Authorization: Bearer {token}` header.

### Key Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/login.php` | User login |
| `POST` | `/api/auth/logout.php` | Logout |
| `GET`  | `/api/students/dashboard.php` | Student dashboard data |
| `POST` | `/api/ai/chat.php` | LearnPulse AI query |
| `POST` | `/api/notifications/mark_all_read.php` | Mark notifications read |
| `GET`  | `/api/chat/public.php?action=load` | Load public chat messages |
| `POST` | `/api/chat/public.php?action=send` | Send public chat message |
| `POST` | `/api/payments/mpesa.php` | Initiate M-Pesa STK push |
| `POST` | `/api/payments/stripe.php` | Create Stripe payment intent |
| `POST` | `/api/payments/paypal.php` | Create PayPal order |
| `POST` | `/api/payments/paystack.php` | Initialize Paystack transaction |

### Standard Response Format

```json
{
  "success": true,
  "message": "Operation completed.",
  "data": { }
}
```

```json
{
  "success": false,
  "message": "Error description.",
  "data": null
}
```

---

## 🔒 Security

### Measures Implemented

| Feature | Implementation |
|---------|---------------|
| **Password Hashing** | bcrypt with cost factor 12 |
| **SQL Injection** | PDO prepared statements throughout |
| **XSS Prevention** | `htmlspecialchars()` on all user output |
| **CSRF** | Session-based forms; API uses JWT |
| **Rate Limiting** | 5 failed logins → account locked |
| **Session Security** | DB-validated sessions; IP/UA logging |
| **JWT Tokens** | Signed with 64-char secret; device-bound |
| **2FA** | Email OTP (enable in Settings → Security) |
| **Input Sanitization** | `sanitize()` helper on all user inputs |
| **File Upload Security** | MIME-type checking; filename randomization |
| **Account Locking** | Auto-lock after 5 failed attempts |
| **Multi-device Control** | Max 2 simultaneous sessions per user |

### Security Headers (.htaccess)

```apache
# Prevent directory listing
Options -Indexes

# Protect config files
<FilesMatch "\.(php|json|lock|sql|env|log)$">
  <RequireAll>
    Require all denied
    Require not ip 127.0.0.1
  </RequireAll>
</FilesMatch>
```

---

## ⏰ Cron Jobs

Set up Windows Task Scheduler to run these scripts daily:

| Script | Frequency | Purpose |
|--------|-----------|---------|
| `cron/delete-messages.php` | Every hour | Delete community chat messages older than 24 hours |
| `cron/expire-announcements.php` | Daily at midnight | Mark expired announcements as inactive |
| `cron/contract-expiry.php` | Daily at 8am | Notify admins of contracts expiring within 30 days |
| `cron/reset-attempts.php` | Daily at midnight | Reset locked accounts after 24-hour lockout |

### Windows Task Scheduler Setup

```powershell
# Example: Run delete-messages.php every hour
$action = New-ScheduledTaskAction -Execute "php" -Argument "C:\xampp\htdocs\ByteClass\cron\delete-messages.php"
$trigger = New-ScheduledTaskTrigger -RepetitionInterval (New-TimeSpan -Hours 1) -Once -At "00:00"
Register-ScheduledTask -TaskName "ByteClass-DeleteMessages" -Action $action -Trigger $trigger
```

---

## 🗑️ Files to Delete (Legacy React)

The project initially scaffolded a React/Vite frontend that was later replaced with server-side PHP. The following files/folders are **safe to delete** — they are not used:

```
ByteClass/
├── frontend/               ← ENTIRE FOLDER — delete completely
│   ├── src/
│   ├── public/
│   ├── node_modules/
│   ├── package.json
│   ├── vite.config.js
│   └── ...
│
├── about.php               ← Root-level duplicate — delete (use pages/public/about.php)
├── courses.php             ← Root-level duplicate — delete (use pages/public/courses.php)
├── course-detail.php       ← Unused — delete
│
├── helpers/otp.php         ← Empty placeholder — needs to be populated (see below)
├── helpers/pdf.php         ← Empty placeholder — needs to be populated (see below)
├── middleware/guest.php    ← Unused — delete (was for React auth guard)
├── middleware/role.php     ← Unused — delete (auth-check.php handles this now)
│
└── logs/                  ← Keep but add to .gitignore
```

### Why Are Some Helpers Empty?

| File | Status | Action Needed |
|------|--------|---------------|
| `helpers/otp.php` | **Empty** — placeholder created early in build | Contains `generate_and_store_otp()`, `verify_otp()`, `should_trigger_2fa()` — needed for 2FA. Populate when enabling 2FA. |
| `helpers/pdf.php` | **Empty** — placeholder | Contains `generate_certificate_pdf()` — needed for certificate downloads. Implement with mPDF. |
| `middleware/guest.php` | **Empty** — was for React's protected routes | Not needed in PHP architecture. Safe to delete. |
| `middleware/role.php` | **Empty** — same reason | Not needed. Role checking is done in `includes/auth-check.php`. |

---

## 🗺️ Roadmap

### Version 1.1 (Next)
- [ ] **Lesson Player** — Full video player with progress tracking and quiz integration
- [ ] **Quiz Builder** — Admin/Lecturer quiz creation interface
- [ ] **PDF Certificates** — Auto-generated with mPDF and QR code verification
- [ ] **Live Payment Gateways** — M-Pesa, Stripe, PayPal, Paystack fully integrated
- [ ] **Cron Jobs** — Message cleanup, contract expiry alerts

### Version 1.2
- [ ] **Course Ratings** — Students rate courses (1-5 stars) after completion
- [ ] **Discussion Forums** — Per-course discussion boards
- [ ] **Offline Mode** — Downloaded lesson content for offline viewing
- [ ] **Mobile App** — React Native companion app

### Version 2.0
- [ ] **Multi-language** — Swahili (sw) support
- [ ] **Bulk Certificate** — Generate certificates for entire cohort
- [ ] **Analytics Dashboard** — Revenue charts, enrollment trends, student retention
- [ ] **Zoom Integration** — Direct Zoom meeting creation from admin

---

## 🤝 Contributing

ByteClass is currently a private/proprietary project. If you have been given access:

1. Follow the coding style (PHP 8.x, Tailwind utility classes, no React)
2. All database changes must update `database/schema.sql`
3. Test all payment flows in sandbox mode before deploying
4. Never commit API keys, passwords, or `.env` files
5. Run all PHP files through phpcs before committing

---

## 📄 License

**Proprietary — All Rights Reserved**

© 2026 ByteClass Ltd. Nairobi, Kenya.

This software is proprietary and confidential. Unauthorized copying, distribution, modification, or use of this software, in whole or in part, is strictly prohibited. For licensing inquiries: `legal@byteclass.io`

---

## 📞 Contact & Support

| Channel | Details |
|---------|---------|
| **Email** | support@byteclass.io |
| **Legal** | legal@byteclass.io |
| **Security** | security@byteclass.io |
| **Privacy** | privacy@byteclass.io |
| **Location** | Nairobi, Kenya |

---

<div align="center">

**Built with ❤️ in Nairobi, Kenya**

[ByteClass.io](https://byteclass.io) · [Terms](https://byteclass.io/terms) · [Privacy](https://byteclass.io/privacy)

*Learn · Build · Grow*

</div>
