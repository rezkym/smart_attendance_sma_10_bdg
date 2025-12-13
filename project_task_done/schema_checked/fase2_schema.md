# Database Schema Documentation - Fase 2

## Overview
Schema untuk fitur **Teacher Management** dan **Classroom Management** pada Fase 2 MVP.

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
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

### Table: `academic_years`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK, auto_increment |
| name | varchar(100) | unique |
| start_date | date | required |
| end_date | date | required |
| is_active | tinyint(1) | default: false |
| description | text | nullable |
| created_at | timestamp | nullable |
| updated_at | timestamp | nullable |

---

## New Tables for Fase 2

### Table: `teachers`

**Deskripsi:** Menyimpan data profil guru yang terkait dengan user account.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, auto_increment | Primary key |
| user_id | bigint unsigned | FK → users.id, unique | One-to-one dengan users |
| nip | varchar(30) | unique, nullable | Nomor Induk Pegawai |
| phone | varchar(20) | nullable | Nomor telepon |
| address | text | nullable | Alamat lengkap |
| is_active | tinyint(1) | default: true | Status aktif/tidak |
| created_at | timestamp | nullable | |
| updated_at | timestamp | nullable | |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE INDEX (user_id)` - memastikan relasi one-to-one
- `UNIQUE INDEX (nip)` - NIP harus unik jika diisi
- `INDEX (is_active)`

**Foreign Keys:**
- `user_id` → `users.id` ON DELETE CASCADE

**Relasi:**
- `teachers.user_id` → `users.id` (One-to-One)

---

### Table: `classrooms`

**Deskripsi:** Menyimpan data kelas/ruang kelas.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, auto_increment | Primary key |
| name | varchar(50) | required | Nama kelas (e.g., "X IPA 1") |
| grade_level | tinyint unsigned | required | Tingkat kelas (10, 11, 12) |
| academic_year_id | bigint unsigned | FK → academic_years.id | Tahun ajaran |
| capacity | smallint unsigned | nullable | Kapasitas kelas |
| description | text | nullable | Deskripsi/keterangan |
| is_active | tinyint(1) | default: true | Status aktif/tidak |
| created_at | timestamp | nullable | |
| updated_at | timestamp | nullable | |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE INDEX (name, academic_year_id)` - kombinasi unique per tahun ajaran
- `INDEX (grade_level)`
- `INDEX (academic_year_id)`
- `INDEX (is_active)`

**Foreign Keys:**
- `academic_year_id` → `academic_years.id` ON DELETE RESTRICT

**Relasi:**
- `classrooms.academic_year_id` → `academic_years.id` (Many-to-One)

---

## Future Considerations

> **Catatan Fase 3:** Akan ditambahkan kolom `homeroom_teacher_id` (FK → teachers.id) pada tabel `classrooms` untuk fitur **Classroom Assignment** - menentukan wali kelas.

---

## Diagram Relasi

```
┌──────────────┐         ┌──────────────┐
│    users     │         │academic_years│
├──────────────┤         ├──────────────┤
│ id (PK)      │◄─┐      │ id (PK)      │◄─┐
│ name         │  │      │ name         │  │
│ email        │  │      │ start_date   │  │
│ password     │  │      │ end_date     │  │
│ ...          │  │      │ is_active    │  │
└──────────────┘  │      │ ...          │  │
                  │      └──────────────┘  │
                  │                         │
                  │ 1:1                     │ M:1
                  │                         │
┌──────────────┐  │      ┌──────────────┐  │
│   teachers   │  │      │  classrooms  │  │
├──────────────┤  │      ├──────────────┤  │
│ id (PK)      │  │      │ id (PK)      │  │
│ user_id (FK) │──┘      │ name         │  │
│ nip          │         │ grade_level  │  │
│ phone        │         │academic_year ├──┘
│ address      │         │ _id (FK)     │
│ is_active    │         │ capacity     │
│ ...          │         │ is_active    │
└──────────────┘         │ ...          │
                         └──────────────┘
```
