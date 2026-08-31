# 03-authentication.md

# Modul

NgeKas — Authentication

## Tujuan

Menyediakan proses authentication sederhana agar hanya admin yang dapat mengakses dan mengelola keuangan NgeKas.

Pada MVP, aplikasi hanya digunakan oleh **satu admin**.

Authentication difokuskan pada:

* Login
* Logout
* Proteksi halaman
* Session authentication

Tidak tersedia registrasi publik.

## Scope

Authentication MVP mencakup:

```text
Login
Logout
Remember Me
Protected Route
Guest Redirect
Authenticated Redirect
```

Tidak termasuk:

```text
Register
Forgot Password
Email Verification
Social Login
Two-Factor Authentication
Multi User Management
Role & Permission
```

Akun admin dibuat secara manual melalui:

```text
Seeder
```

## User Story

Sebagai admin, saya ingin login menggunakan email dan password agar dapat mengakses NgeKas.

Sebagai admin, saya ingin tetap login menggunakan fitur Remember Me agar tidak perlu login setiap kali membuka aplikasi.

Sebagai admin, saya ingin logout setelah selesai menggunakan aplikasi agar session dapat dihentikan.

Sebagai admin, saya ingin halaman keuangan hanya dapat diakses setelah login agar data keuangan tidak dapat dilihat oleh pengguna yang tidak memiliki akses.

## Flow

### Login

```text
Buka NgeKas
    ↓
Belum Login
    ↓
Login Page
    ↓
Input Email + Password
    ↓
Validasi
    ↓
Authentication
    ↓
Dashboard
```

Jika authentication gagal:

```text
Login
  ↓
Credential Salah
  ↓
Tampilkan Error
  ↓
Tetap di Login Page
```

### Protected Page

Contoh admin membuka:

```text
/dashboard
/incomes
/expenses
/reports
```

Flow:

```text
Request
   ↓
Auth Middleware
   ↓
Authenticated?
   ├── Ya    → Halaman
   └── Tidak → Login
```

### Logout

```text
Admin
  ↓
Logout
  ↓
Session Invalidated
  ↓
Login Page
```

## Database

Authentication menggunakan tabel:

```text
users
```

Struktur:

```text
users

id
name
email
password
remember_token
created_at
updated_at
```

Contoh:

```text
name
Angga

email
admin@ngebadmintonyuk.local

password
Hashed Password
```

Password tidak pernah disimpan dalam bentuk plain text.

### Admin Seeder

Karena tidak terdapat registrasi, akun awal dibuat melalui seeder.

Contoh konsep:

```text
AdminSeeder
    ↓
Create Admin User
```

Seeder hanya digunakan untuk menyediakan akun admin awal.

Credential production tidak ditulis secara permanen di source code publik.

## Validation

### Login

Email:

```text
required
email
```

Password:

```text
required
string
```

Remember Me:

```text
boolean
```

Contoh input:

```text
Email
[ admin@ngebadmintonyuk.local ]

Password
[ ••••••••••••••• ]

[✓] Ingat Saya

[ Masuk ]
```

Jika field kosong:

```text
Email wajib diisi.
Password wajib diisi.
```

Jika credential salah:

```text
Email atau password tidak sesuai.
```

Pesan error tidak menjelaskan apakah email atau password yang salah.

## Business Rule

### Single Admin

MVP hanya memiliki satu admin.

Tidak diperlukan:

```text
roles
permissions
user_roles
role_permissions
```

Semua user yang berhasil login dianggap memiliki akses penuh ke NgeKas.

### Registration

Route registrasi tidak tersedia.

Pengunjung tidak dapat membuat akun sendiri.

```text
/register
→ tidak tersedia
```

### Password

Password wajib disimpan menggunakan hashing Laravel.

Tidak diperbolehkan menyimpan:

```text
password = "password123"
```

secara langsung ke database.

### Protected Route

Seluruh halaman NgeKas selain login harus menggunakan authentication middleware.

Contoh:

```text
/dashboard
/categories
/incomes
/expenses
/reports
```

harus membutuhkan authentication.

### Guest

User yang belum login dan mencoba membuka halaman protected diarahkan ke:

```text
/login
```

### Authenticated User

Admin yang sudah login dan membuka:

```text
/login
```

diarahkan ke:

```text
/dashboard
```

### Logout

Logout harus:

```text
Logout
  ↓
Invalidate Session
  ↓
Regenerate CSRF Token
  ↓
Redirect Login
```

### Session

Authentication menggunakan session bawaan Laravel.

Tidak diperlukan token API untuk MVP.

## UI

### Login Page

Login menggunakan branding NgeBadmintonYuk.

Konsep sederhana:

```text
┌─────────────────────────────────────┐
│                                     │
│         NgeBadmintonYuk             │
│              NgeKas                 │
│                                     │
│  Kelola keuangan komunitas dengan   │
│  sederhana.                         │
│                                     │
│  Email                              │
│  [_______________________________]  │
│                                     │
│  Password                           │
│  [_______________________________]  │
│                                     │
│  [✓] Ingat Saya                    │
│                                     │
│  [            Masuk              ]  │
│                                     │
└─────────────────────────────────────┘
```

Warna primary:

```text
#2455F5
```

Accent:

```text
#FFD23F
```

Background:

```text
#FAF7F0
```

### Password Visibility

Field password dapat menyediakan:

```text
[ Password                     👁 ]
```

untuk menampilkan atau menyembunyikan password.

### Error State

Error ditampilkan dekat dengan field terkait.

Contoh:

```text
Email
[ admin@xxx.com ]

Email atau password tidak sesuai.
```

### Loading State

Ketika proses login berlangsung:

```text
[ Memproses... ]
```

Button tidak dapat ditekan berulang kali selama request berlangsung.

### Logout

Logout tersedia melalui menu profile/admin.

Contoh:

```text
Angga
  ↓
[ Logout ]
```

Logout tidak perlu memiliki halaman khusus.

## Livewire Component

Authentication menggunakan Livewire Component:

```text
Auth
└── Login
```

Tanggung jawab component:

```text
Login

├── Menampung email
├── Menampung password
├── Menampung remember
├── Validation
├── Authentication
└── Redirect
```

State utama:

```text
email
password
remember
```

Tidak diperlukan component:

```text
Register
ForgotPassword
ResetPassword
VerifyEmail
```

pada MVP.

## Service

Authentication sederhana tidak membutuhkan Service khusus.

Proses authentication dapat menggunakan authentication mechanism Laravel secara langsung.

Flow:

```text
Login Component
      ↓
Laravel Auth
      ↓
Session
```

Tidak dibuat:

```text
AuthService
```

selama belum terdapat business logic authentication yang kompleks.

## Repository

Authentication tidak membutuhkan Repository khusus.

Query authentication menggunakan mekanisme Laravel.

Tidak dibuat:

```text
UserRepository
AuthRepository
```

hanya untuk proses login.

Repository baru dipertimbangkan jika pengelolaan user berkembang pada versi berikutnya.

## Testing

Testing authentication minimal mencakup:

### Login

* Admin dapat membuka halaman login.
* Admin dapat login menggunakan credential yang benar.
* Admin tidak dapat login menggunakan password yang salah.
* Admin tidak dapat login menggunakan email yang tidak terdaftar.
* Email wajib diisi.
* Password wajib diisi.
* Remember Me dapat digunakan.

### Protected Route

Guest tidak dapat membuka:

```text
/dashboard
/categories
/incomes
/expenses
/reports
```

Guest diarahkan ke:

```text
/login
```

Admin yang sudah login dapat mengakses seluruh halaman tersebut.

### Login Redirect

Admin yang sudah login tidak perlu kembali ke halaman login.

Jika membuka:

```text
/login
```

admin diarahkan ke:

```text
/dashboard
```

### Logout

* Admin dapat logout.
* Session dihapus setelah logout.
* Admin diarahkan ke login.
* Halaman protected tidak dapat diakses setelah logout.

### Password

* Password tersimpan dalam bentuk hash.
* Password plain text tidak tersimpan di database.

## Future Improvement

Authentication dapat dikembangkan ketika NgeKas mulai dikelola lebih dari satu orang.

Fitur berikutnya dapat mencakup:

```text
Multi User
Role
Permission
Forgot Password
Email Verification
Two-Factor Authentication
Login History
Session Management
```

Contoh role di masa depan:

```text
Owner
Treasurer
Admin
Viewer
```

Contoh permission:

```text
View Dashboard
View Transaction
Create Transaction
Update Transaction
Delete Transaction
View Report
Manage User
```

Fitur tersebut **tidak termasuk scope MVP NgeKas saat ini**.
