# Database Schema - Student Management

## Overview
Schema untuk fitur **Student Management** pada Fase 3 MVP.

**Dependencies:**
- `users` - dari User Management (Fase 1)
- `classrooms` - dari Classroom Management (Fase 2)

---

## Reference Tables (Already Exist)

### Table: `users`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK, auto_increment |
| name | varchar(255) | required |
| email | varchar(255) | unique |
| email_verified_at | timestamp | nullable |
| password | varchar(255) | required |
| remember_token | varchar(100) | nullable |
| timestamps | | |

---

### Table: `classrooms`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK, auto_increment |
| name | varchar(50) | required |
| grade_level | tinyint unsigned | required |
| academic_year_id | bigint unsigned | FK → academic_years.id |
| capacity | smallint unsigned | nullable |
| description | text | nullable |
| is_active | tinyint(1) | default: true |
| timestamps | | |

---

## New Table

### Table: `students`

**Deskripsi:** Menyimpan data siswa yang terdaftar di sekolah.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, auto_increment | Primary key |
| user_id | bigint unsigned | FK → users.id, nullable, unique | Link ke user account (untuk login siswa jika ada) |
| classroom_id | bigint unsigned | FK → classrooms.id, nullable | Kelas saat ini |
| nisn | varchar(20) | unique, required | Nomor Induk Siswa Nasional |
| nis | varchar(20) | unique, required | Nomor Induk Siswa (internal sekolah) |
| full_name | varchar(100) | required | Nama lengkap siswa |
| gender | enum('L', 'P') | required | Jenis kelamin (L=Laki-laki, P=Perempuan) |
| birth_place | varchar(100) | nullable | Tempat lahir |
| birth_date | date | nullable | Tanggal lahir |
| address | text | nullable | Alamat lengkap |
| phone_number | varchar(20) | nullable | Nomor telepon/HP siswa atau orang tua |
| rfid_card_number | varchar(50) | unique, nullable | Nomor kartu RFID untuk absensi |
| photo | varchar(255) | nullable | Path foto siswa |
| enrollment_date | date | nullable | Tanggal masuk/daftar ke sekolah |
| is_active | tinyint(1) | default: true | Status aktif/tidak (aktif, lulus, mutasi, dll) |
| notes | text | nullable | Catatan tambahan |
| created_at | timestamp | nullable | |
| updated_at | timestamp | nullable | |

---

## Indexes

- `PRIMARY KEY (id)`
- `UNIQUE INDEX (nisn)` - NISN harus unik secara nasional
- `UNIQUE INDEX (nis)` - NIS harus unik dalam sekolah
- `UNIQUE INDEX (user_id)` - One-to-One dengan users
- `UNIQUE INDEX (rfid_card_number)` - Kartu RFID unik per siswa
- `INDEX (classroom_id)` - Untuk query siswa per kelas
- `INDEX (full_name)` - Untuk pencarian berdasarkan nama
- `INDEX (gender)` - Untuk filter berdasarkan jenis kelamin
- `INDEX (is_active)` - Untuk filter siswa aktif/non-aktif
- `INDEX (enrollment_date)` - Untuk laporan berdasarkan tanggal masuk

---

## Foreign Keys

| Column | References | ON DELETE | Description |
|--------|------------|-----------|-------------|
| user_id | users.id | SET NULL | Jika user dihapus, relasi di-nullkan |
| classroom_id | classrooms.id | SET NULL | Jika classroom dihapus, relasi di-nullkan |

---

## Relasi

- `students.user_id` → `users.id` (One-to-One, nullable)
- `students.classroom_id` → `classrooms.id` (Many-to-One, nullable)

---

## Diagram Relasi

```
┌──────────────┐
│    users     │
├──────────────┤
│ id (PK)      │◄──────────┐
│ name         │           │
│ email        │           │ 1:1 (nullable)
│ password     │           │
│ ...          │           │
└──────────────┘           │
                           │
┌──────────────┐           │
│   students   │           │
├──────────────┤           │
│ id (PK)      │           │
│ user_id (FK) │───────────┘
│ classroom_id │───────────┐
│   (FK)       │           │
│ nisn         │           │
│ nis          │           │
│ full_name    │           │ M:1 (nullable)
│ gender       │           │
│ birth_place  │           │
│ birth_date   │           │
│ address      │           │
│ phone_number │           │
│ rfid_card_   │           │
│   number     │           │
│ photo        │           │
│ enrollment_  │           │
│   date       │           │
│ is_active    │           │
│ notes        │           │
│ ...          │           │
└──────────────┘           │
                           │
┌──────────────┐           │
│  classrooms  │           │
├──────────────┤           │
│ id (PK)      │◄──────────┘
│ name         │
│ grade_level  │
│ academic_    │
│   year_id    │
│ capacity     │
│ is_active    │
│ ...          │
└──────────────┘
```

---

## Enum Values

### Gender
| Value | Description |
|-------|-------------|
| L | Laki-laki (Male) |
| P | Perempuan (Female) |

---

## Validation Rules (untuk Form Request nanti)

| Field | Rules |
|-------|-------|
| nisn | required, string, max:20, unique:students,nisn |
| nis | required, string, max:20, unique:students,nis |
| full_name | required, string, max:100 |
| gender | required, in:L,P |
| birth_place | nullable, string, max:100 |
| birth_date | nullable, date, before:today |
| address | nullable, string |
| phone_number | nullable, string, max:20 |
| rfid_card_number | nullable, string, max:50, unique:students,rfid_card_number |
| photo | nullable, image, max:2048 |
| enrollment_date | nullable, date |
| classroom_id | nullable, exists:classrooms,id |
| is_active | boolean |

---

## Future Considerations

> **Catatan Fase Berikutnya:**
> - Akan ditambahkan relasi ke `attendance_records` untuk fitur **Attendance Core** (Fase 5)
> - Field `parent_id` atau tabel `student_parents` mungkin diperlukan untuk fitur komunikasi dengan orang tua
> - Fitur **Class Promotion** (naik kelas) mungkin memerlukan history classroom_id per tahun ajaran
> - Integrasi dengan sistem Dapodik mungkin memerlukan field tambahan untuk sinkronisasi data

---

## Sample Data (untuk referensi)

```json
{
  "id": 1,
  "user_id": null,
  "classroom_id": 5,
  "nisn": "0012345678",
  "nis": "12345",
  "full_name": "Ahmad Fauzi",
  "gender": "L",
  "birth_place": "Bandung",
  "birth_date": "2008-05-15",
  "address": "Jl. Merdeka No. 123, Bandung",
  "phone_number": "081234567890",
  "rfid_card_number": "A1B2C3D4E5",
  "photo": "students/2024/ahmad-fauzi.jpg",
  "enrollment_date": "2021-07-15",
  "is_active": true,
  "notes": null,
  "created_at": "2024-01-01 10:00:00",
  "updated_at": "2024-01-01 10:00:00"
}
```
