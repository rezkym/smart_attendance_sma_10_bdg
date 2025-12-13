# Database Schema - Schedule/Timetable Management

## Overview
Schema untuk fitur **Schedule/Timetable Management** pada Fase 4 MVP.

**Dependencies:**
- `classrooms` - dari Classroom Management (Fase 2)
- `subjects` - dari Subject Management (Fase 1)
- `teachers` - dari Teacher Management (Fase 2)
- `academic_years` - dari Academic Year Management (Fase 1)

---

## Reference Tables (Already Exist)

### Table: `classrooms`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK |
| name | varchar(50) | required |
| grade_level | tinyint unsigned | required |
| academic_year_id | bigint unsigned | FK → academic_years.id |
| homeroom_teacher_id | bigint unsigned | FK → teachers.id, nullable |
| is_active | tinyint(1) | default: true |

### Table: `subjects`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK |
| code | varchar(20) | unique |
| name | varchar(100) | required |
| is_active | tinyint(1) | default: true |

### Table: `teachers`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK → users.id |
| nip | varchar(30) | nullable |
| is_active | tinyint(1) | default: true |

> **Note:** Data profil guru (name, phone, address, dll) ada di tabel `users`

### Table: `academic_years`
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint unsigned | PK |
| name | varchar(100) | unique |
| start_date | date | required |
| end_date | date | required |
| is_active | tinyint(1) | default: false |

---

## New Table

### Table: `schedules`

**Deskripsi:** Menyimpan jadwal pelajaran per kelas.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint unsigned | PK, auto_increment | Primary key |
| classroom_id | bigint unsigned | FK → classrooms.id, required | Kelas |
| subject_id | bigint unsigned | FK → subjects.id, required | Mata pelajaran |
| teacher_id | bigint unsigned | FK → teachers.id, required | Guru pengajar |
| academic_year_id | bigint unsigned | FK → academic_years.id, required | Tahun ajaran |
| day_of_week | tinyint unsigned | required, 1-6 | Hari (1=Senin, 6=Sabtu) |
| start_time | time | required | Jam mulai (e.g., 07:00) |
| end_time | time | required | Jam selesai (e.g., 08:30) |
| is_active | tinyint(1) | default: true | Status aktif |
| notes | text | nullable | Catatan tambahan |
| created_at | timestamp | nullable | |
| updated_at | timestamp | nullable | |

---

## Indexes

- `PRIMARY KEY (id)`
- `UNIQUE INDEX (classroom_id, academic_year_id, day_of_week, start_time)` - Prevent jadwal bentrok
- `INDEX (classroom_id)`
- `INDEX (subject_id)`
- `INDEX (teacher_id)`
- `INDEX (academic_year_id)`
- `INDEX (day_of_week)`
- `INDEX (is_active)`

---

## Foreign Keys

| Column | References | ON DELETE | Description |
|--------|------------|-----------|-------------|
| classroom_id | classrooms.id | CASCADE | Hapus jadwal jika kelas dihapus |
| subject_id | subjects.id | RESTRICT | Tidak bisa hapus mapel yang punya jadwal |
| teacher_id | teachers.id | RESTRICT | Tidak bisa hapus guru yang punya jadwal |
| academic_year_id | academic_years.id | RESTRICT | Tidak bisa hapus tahun ajaran yang punya jadwal |

---

## Enum: DayOfWeek

| Value | Label (Indonesia) | Label (English) |
|-------|-------------------|-----------------|
| 1 | Senin | Monday |
| 2 | Selasa | Tuesday |
| 3 | Rabu | Wednesday |
| 4 | Kamis | Thursday |
| 5 | Jumat | Friday |
| 6 | Sabtu | Saturday |

---

## Diagram Relasi

```
┌──────────────┐      ┌──────────────┐
│academic_years│      │   subjects   │
├──────────────┤      ├──────────────┤
│ id (PK)      │◄─┐   │ id (PK)      │◄─┐
│ name         │  │   │ code         │  │
│ is_active    │  │   │ name         │  │
│ ...          │  │   │ is_active    │  │
└──────────────┘  │   └──────────────┘  │
                  │                     │
                  │   ┌──────────────┐  │
                  │   │   teachers   │  │
                  │   ├──────────────┤  │
                  │   │ id (PK)      │◄─┼─┐
                  │   │ user_id (FK) │  │ │
                  │   │ nip          │  │ │
                  │   │ is_active    │  │ │
                  │   └──────────────┘  │ │
                  │                     │ │
┌──────────────┐  │   ┌──────────────┐  │ │
│  classrooms  │  │   │  schedules   │  │ │
├──────────────┤  │   ├──────────────┤  │ │
│ id (PK)      │◄─┼───│ classroom_id │  │ │
│ name         │  │   │ subject_id   │──┘ │
│ grade_level  │  │   │ teacher_id   │────┘
│ academic_    │  │   │ academic_    │
│   year_id    │  └───│   year_id    │
│ is_active    │      │ day_of_week  │
│ ...          │      │ start_time   │
└──────────────┘      │ end_time     │
                      │ is_active    │
                      │ notes        │
                      └──────────────┘
```

---

## Validation Rules (Form Request)

| Field | Rules |
|-------|-------|
| classroom_id | required, exists:classrooms,id |
| subject_id | required, exists:subjects,id |
| teacher_id | required, exists:teachers,id |
| academic_year_id | required, exists:academic_years,id |
| day_of_week | required, integer, between:1,6 |
| start_time | required, date_format:H:i |
| end_time | required, date_format:H:i, after:start_time |
| is_active | boolean |
| notes | nullable, string, max:500 |

### Custom Validation
- `end_time` harus lebih besar dari `start_time`
- Kombinasi (classroom_id + academic_year_id + day_of_week + start_time) harus unique (cek time conflict)

---

## Business Rules

1. **No Time Conflict:** Satu kelas tidak boleh punya 2 jadwal di waktu yang sama
2. **Teacher Availability:** Satu guru tidak boleh mengajar di 2 kelas berbeda pada waktu yang sama (optional, bisa ditambah nanti)
3. **Active Only:** Jadwal hanya berlaku untuk classroom, subject, teacher, dan academic_year yang aktif

---

## Sample Data

```json
{
  "id": 1,
  "classroom_id": 5,
  "subject_id": 3,
  "teacher_id": 2,
  "academic_year_id": 1,
  "day_of_week": 1,
  "start_time": "07:00",
  "end_time": "08:30",
  "is_active": true,
  "notes": null,
  "created_at": "2024-01-01 10:00:00",
  "updated_at": "2024-01-01 10:00:00"
}
```

**Display:** Kelas X IPA 1, Senin 07:00-08:30, Matematika, Pak Ahmad

---

## Future Considerations

> **Catatan Fase Berikutnya:**
> - Bisa ditambahkan validasi teacher availability (guru tidak double booking)
> - Bisa ditambahkan field `semester` jika jadwal berbeda per semester
> - Bisa ditambahkan field `room` jika kelas pindah ruangan untuk mapel tertentu
> - Integrasi dengan Attendance Records (Fase 5) untuk presensi per jadwal
