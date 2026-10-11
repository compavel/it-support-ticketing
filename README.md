# IT Support Ticketing System

Sistem ticketing IT support untuk lingkungan sekolah. Memungkinkan user melaporkan masalah IT, teknisi menangani, dan admin memantau semuanya secara terpusat.

## Problem Statement

Di lingkungan sekolah, ketika ada masalah IT (laptop error, jaringan putus, proyektor rusak, dll), siswa/guru biasanya lapor lewat WhatsApp atau lisan. Masalahnya:

- Laporan gampang hilang atau ketuker
- Tidak ada tracking status (sudah diperbaiki belum?)
- Tidak ada prioritas (urgent sama biasa diperlakukan sama)
- Tidak ada riwayat untuk analisis masalah berulang
- Teknisi tidak tahu harus mengerjakan yang mana duluan

## Features

### Core Features
- **Authentication** - Login dengan role-based access
- **Dashboard** - Statistik dan overview per role (Admin, Technician, User)
- **Ticket Management** - CRUD ticket dengan status tracking
- **Assignment** - Admin dapat menugaskan teknisi ke ticket
- **Priority** - 4 level prioritas (Low, Medium, High, Urgent)
- **Status** - 4 status (Open, In Progress, Resolved, Closed)
- **Comments** - Komentar pada ticket
- **History** - Riwayat perubahan pada ticket
- **Filter & Search** - Filter berdasarkan status, prioritas, kategori

### Roles & Permissions

| Action | Admin | Technician | User |
|--------|:-----:|:----------:|:----:|
| Login | ✅ | ✅ | ✅ |
| Buat ticket | ✅ | ✅ | ✅ |
| Lihat semua ticket | ✅ | ❌ | ❌ |
| Lihat ticket sendiri | ✅ | ✅ | ✅ |
| Assign technician | ✅ | ❌ | ❌ |
| Ubah status ticket | ✅ | ✅ | ❌ |
| Ubah prioritas ticket | ✅ | ❌ | ❌ |
| Lihat dashboard admin | ✅ | ❌ | ❌ |
| Lihat dashboard technician | ❌ | ✅ | ❌ |
| Kelola user | ✅ | ❌ | ❌ |
| Komentar pada ticket | ✅ | ✅ | ✅ |

## Tech Stack

- **Backend**: Laravel 10 + PHP 8.1
- **Frontend**: Blade + Tailwind CSS + Alpine.js
- **Database**: MySQL (MariaDB)
- **Build Tool**: Vite

## Screenshots

### Login Page
Halaman login dengan desain bersih dan profesional.

### Dashboard Admin
Menampilkan statistik ticket (total, open, in progress, resolved), daftar ticket terbaru, dan performa teknisi.

### Dashboard Technician
Menampilkan ticket yang ditugaskan dengan prioritas, dan ticket yang baru diselesaikan.

### Ticket List
Daftar ticket dengan filter dan pencarian. Setiap ticket menampilkan judul, kategori, teknisi, status, dan prioritas.

### Ticket Detail
Detail ticket dengan komentar, riwayat perubahan, dan aksi (edit, assign, ubah status, hapus).

### User Management
Halaman admin untuk mengelola user (CRUD).

## Installation

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/MariaDB
- Node.js & npm

### Steps

1. Clone repository
```bash
git clone https://github.com/tegarraditya/it-support-ticketing.git
cd it-support-ticketing
```

2. Install PHP dependencies
```bash
composer install
```

3. Install JS dependencies
```bash
npm install
```

4. Copy environment file
```bash
cp .env.example .env
```

5. Generate application key
```bash
php artisan key:generate
```

6. Configure database in `.env`
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=it_support_ticketing
DB_USERNAME=root
DB_PASSWORD=
```

7. Run migrations and seeder
```bash
php artisan migrate:fresh --seed
```

8. Build assets
```bash
npm run build
```

9. Start development server
```bash
php artisan serve
```

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sekolah.sch.id | password |
| Technician | budi@sekolah.sch.id | password |
| Technician | andi@sekolah.sch.id | password |
| User | siti@sekolah.sch.id | password |
| User | dewi@sekolah.sch.id | password |

## Database Schema

### users
- id, name, email, password, role (admin/technician/user), is_active, timestamps

### tickets
- id, user_id (pelapor), technician_id (yang ditugaskan), title, description, category, priority, status, resolved_at, closed_at, timestamps

### ticket_comments
- id, ticket_id, user_id, comment, timestamps

### ticket_histories
- id, ticket_id, user_id, field, old_value, new_value, timestamps

## Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/LoginController.php
│   │   ├── DashboardController.php
│   │   ├── TicketController.php
│   │   ├── CommentController.php
│   │   └── Admin/UserController.php
│   └── Middleware/
│       ├── RoleMiddleware.php
│       └── IsActiveMiddleware.php
├── Models/
│   ├── User.php
│   ├── Ticket.php
│   ├── TicketComment.php
│   └── TicketHistory.php
```

## Security

- Authentication menggunakan Laravel Auth
- Password hashing dengan bcrypt
- Role-based access control dengan Middleware
- CSRF protection otomatis
- Input validation pada setiap form
- Mass assignment protection dengan $fillable
- Authorization check pada setiap aksi

## Future Improvements

- Email notification saat ticket diassign atau status berubah
- Export ticket ke PDF
- Dashboard dengan chart/graph
- Kategori ticket yang lebih detail
- File attachment pada ticket
- SLA tracking
- Multi-level approval

## Author

**Tegar Raditya**
- IT Support Internship Experience
- Web Development Portfolio Project

## License

This project is open-sourced software licensed under the MIT license.
