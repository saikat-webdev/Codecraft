# CodeCraft - Laravel + React Coding Education Platform

A modern, full-stack coding education platform built with Laravel (backend) and React (frontend). CodeCraft provides an interactive learning experience with real-time code execution, sudden tests, gamification, and a comprehensive admin panel.

![CodeCraft](https://img.shields.io/badge/CodeCraft-v1.0.0-22d3ee?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel)
![React](https://img.shields.io/badge/React-18.x-61DAFB?style=for-the-badge&logo=react)

---

## 📋 Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Project Structure](#project-structure)
- [Admin Panel](#admin-panel)
- [API Documentation](#api-documentation)
- [Use Cases](#use-cases)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

---

## ✨ Features

### For Learners

- **Interactive Lessons** - Structured learning paths with theory and practical exercises
- **Code Playground** - Write, run, and test code in multiple languages (Python, JavaScript, Java, C++, etc.)
- **Real-time Execution** - Powered by Judge0 for instant code evaluation
- **Sudden Tests** - Random pop-up quizzes to test knowledge retention
- **Gamification** - XP points, levels, streaks, and achievement badges
- **Profile Customization** - Upload avatars or choose from 7 unique avatar styles
- **Progress Tracking** - Visual progress bars and detailed statistics
- **Dark/Light Theme** - Toggle between themes for comfortable learning

### For Admins

- **Dashboard Analytics** - Real-time stats on users, courses, and engagement
- **User Management** - View, edit, suspend, and manage user roles
- **Course Management** - Create, edit, and publish courses and lessons
- **Sudden Test Management** - Configure pop-up quiz settings and question pool
- **Activity Logs** - Monitor system activity and user actions
- **Settings Panel** - Configure platform settings, feature toggles, and Judge0 integration

---

## 🛠 Tech Stack

### Backend
- **Laravel 11** - PHP framework
- **PostgreSQL** - Database
- **Laravel Sanctum** - API authentication
- **Judge0 API** - Code execution engine

### Frontend
- **React 18** - UI library
- **React Router** - Client-side routing
- **Axios** - HTTP client
- **Vite** - Build tool
- **Tailwind CSS** - Utility-first CSS framework

---

## 📦 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- PostgreSQL 14+ or MySQL 8+
- Git

### Step 1: Clone the Repository

```bash
git clone https://github.com/saikat-webdev/Codecraft.git
cd Codecraft
```

### Step 2: Backend Setup

```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env file
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=codecraft_db
# DB_USERNAME=your_username
# DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Create storage link for avatars
php artisan storage:link

# Clear config cache
php artisan config:clear
```

### Step 3: Frontend Setup

```bash
# Navigate to frontend directory (from project root)
cd ../frontend

# Install Node dependencies
npm install

# Copy environment file (if exists)
cp .env.example .env
```

---

## 🚀 Getting Started

### Starting the Development Servers

Open two terminal windows:

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
# Server runs at http://localhost:8000
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
# Frontend runs at http://localhost:5173
```

### Creating an Admin Account

Use the built-in seeder to create an admin account:

```bash
cd backend
php artisan db:seed --class=AdminUserSeeder
```

**Default Admin Credentials:**
- Email: `admin@codecraft.com`
- Password: `admin123`

**Test User Credentials:**
- Email: `user@codecraft.com`
- Password: `user123`

---

## 📁 Project Structure

```
CodeCraft/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   │   ├── AdminDashboardController.php
│   │   │   │   ├── AdminUserController.php
│   │   │   │   ├── AdminSettingsController.php
│   │   │   │   ├── ProfileController.php
│   │   │   │   ├── SuddenTestController.php
│   │   │   │   └── ...
│   │   │   └── Middleware/
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   ├── SuddenTestSetting.php
│   │   │   └── ...
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   │   └── api.php
│   └── .env
│
├── frontend/                   # React Application
│   ├── src/
│   │   ├── components/
│   │   │   ├── AvatarStyleSelector.jsx
│   │   │   ├── CodingPlayground.jsx
│   │   │   ├── ProfileMenu.jsx
│   │   │   └── ...
│   │   ├── pages/
│   │   │   ├── AdminDashboard.jsx
│   │   │   ├── AdminUsers.jsx
│   │   │   ├── AdminSettings.jsx
│   │   │   ├── AdminActivity.jsx
│   │   │   ├── Profile.jsx
│   │   │   ├── Lesson.jsx
│   │   │   └── ...
│   │   ├── services/
│   │   │   ├── admin.js
│   │   │   ├── profile.js
│   │   │   └── ...
│   │   ├── context/
│   │   │   └── AuthProvider.jsx
│   │   ├── hooks/
│   │   │   └── useSuddenTest.js
│   │   ├── App.jsx
│   │   └── index.css
│   └── package.json
│
└── README.md
```

---

## 👨‍💼 Admin Panel

Access the admin panel by logging in with admin credentials and navigating to:

- **Dashboard**: `http://localhost:5173/admin`
- **User Management**: `http://localhost:5173/admin/users`
- **Sudden Tests**: `http://localhost:5173/admin/sudden-tests`
- **Settings**: `http://localhost:5173/admin/settings`
- **Activity Logs**: `http://localhost:5173/admin/activity`

### Admin Features

| Feature | Description |
|---------|-------------|
| Dashboard Stats | Total users, active users, executions, courses, challenges |
| User Management | Search, filter, suspend/activate, assign roles |
| Sudden Tests | Enable/disable, configure timing, manage question pool |
| Settings | Feature toggles, Judge0 configuration, branding |
| Activity Logs | View all system activities with filtering |

---

## 📡 API Documentation

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new user |
| POST | `/api/login` | User login |
| POST | `/api/logout` | User logout (requires auth) |
| GET | `/api/user` | Get current user (requires auth) |

### Profile Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/profile` | Get user profile |
| PUT | `/api/profile` | Update profile |
| POST | `/api/profile/avatar` | Upload avatar image |
| POST | `/api/profile/avatar/style` | Select avatar style |
| GET | `/api/profile/avatar/styles` | Get available avatar styles |
| GET | `/api/profile/stats` | Get user statistics |
| GET | `/api/profile/achievements` | Get achievements |

### Lesson Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/lessons` | Get all lessons |
| GET | `/api/lessons/{slug}` | Get lesson by slug |
| GET | `/api/modules` | Get all modules |
| GET | `/api/modules/{slug}` | Get module by slug |

### Code Execution Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/code/run` | Execute code |
| POST | `/api/exercises/{id}/submit` | Submit exercise solution |

### Sudden Test Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/sudden-tests/config` | Get sudden test configuration |
| GET | `/api/sudden-tests/challenge` | Get random challenge (requires auth) |
| POST | `/api/sudden-tests/{id}/submit` | Submit challenge answer |

### Admin Endpoints (requires admin role)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/dashboard/stats` | Dashboard statistics |
| GET | `/api/admin/dashboard/activity` | Recent activity |
| GET | `/api/admin/dashboard/leaderboard` | User leaderboard |
| GET | `/api/admin/users` | List all users |
| GET | `/api/admin/users/{id}` | Get user details |
| PUT | `/api/admin/users/{id}` | Update user |
| POST | `/api/admin/users/{id}/suspend` | Suspend user |
| POST | `/api/admin/users/{id}/activate` | Activate user |
| POST | `/api/admin/users/{id}/role` | Assign role |
| GET | `/api/admin/settings` | Get platform settings |
| PUT | `/api/admin/settings` | Update settings |
| GET | `/api/admin/sudden-tests/questions` | Get sudden test questions |
| POST | `/api/admin/sudden-tests/questions` | Create question |
| PUT | `/api/admin/sudden-tests/questions/{id}` | Update question |
| DELETE | `/api/admin/sudden-tests/questions/{id}` | Delete question |

---

## 💡 Use Cases

### For Students

1. **Learn Programming** - Follow structured lessons from beginner to advanced
2. **Practice Coding** - Write and execute code in the playground
3. **Take Quizzes** - Complete sudden tests to reinforce learning
4. **Track Progress** - Monitor XP, levels, and achievements
5. **Customize Profile** - Upload avatar or choose from preset styles

### For Instructors/Admins

1. **Manage Courses** - Create and organize learning content
2. **Monitor Users** - Track student progress and engagement
3. **Configure Sudden Tests** - Set up pop-up quizzes with custom questions
4. **Analyze Analytics** - View platform-wide statistics
5. **Manage Settings** - Configure platform features and integrations

---

## 🔧 Troubleshooting

### Common Issues

**1. "Route not found" errors**
- Ensure Laravel server is running: `php artisan serve`
- Check that `APP_URL` in `.env` matches the server URL

**2. Avatar images not loading**
- Run `php artisan storage:link`
- Ensure `APP_URL` is set correctly in `.env`

**3. Database connection errors**
- Verify database credentials in `.env`
- Ensure PostgreSQL/MySQL service is running

**4. Frontend API calls failing**
- Check that backend server is running on port 8000
- Verify CORS settings in `backend/config/cors.php`

**5. Sudden tests not appearing**
- Ensure sudden tests are enabled in admin settings
- Add at least one active question in the question pool
- Wait 60-120 seconds on a lesson page for the first popup

### Clear Caches

If you encounter unexpected behavior, try clearing caches:

```bash
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [React](https://react.dev) - A JavaScript library for building user interfaces
- [Judge0](https://judge0.com) - Online code execution system
- [DiceBear](https://dicebear.com) - Avatar API for default avatars
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework

---

## 📞 Support

For support, please open an issue in the GitHub repository or contact the development team.

---

**Built with ❤️ by the CodeCraft Saikat**

