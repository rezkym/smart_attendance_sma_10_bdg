# Database Schema - Classroom Management

## Overview
Schema untuk fitur **Classroom Management** pada Fase 2 MVP.

---

## Reference Tables (Already Exist)

### Table: `academic_years`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK, auto_increment |
| name | varchar(100) | unique |
| start_date | date | required |
| end_date | date | required |
| is_active | tinyint(1) | default: false |
| description | text | nullable |
| timestamps | | |

---

## New Table

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

## Diagram Relasi

```
┌──────────────┐
│academic_years│
├──────────────┤
│ id (PK)      │◄─┐
│ name         │  │
│ start_date   │  │
│ end_date     │  │
│ is_active    │  │
│ ...          │  │
└──────────────┘  │
                  │ M:1
                  │
┌──────────────┐  │
│  classrooms  │  │
├──────────────┤  │
│ id (PK)      │  │
│ name         │  │
│ grade_level  │  │
│academic_year │──┘
│ _id (FK)     │
│ capacity     │
│ is_active    │
│ ...          │
└──────────────┘
```

---

## Future Considerations

> **Catatan Fase 3:** Akan ditambahkan kolom `homeroom_teacher_id` (FK → teachers.id) untuk fitur **Classroom Assignment** - menentukan wali kelas.
