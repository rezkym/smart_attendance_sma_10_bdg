# MVP Features - School Attendance System (Admin)

## Fase 1: Independen (Tidak ada dependency)
- [x] Role & Permission Management *(independen)* ✅
- [x] User Management *(Role & Permission)* ✅
- [x] Academic Year Management *(independen)* ✅
- [x] Subject Management *(independen)* ✅

## Fase 2: Dependency Tingkat 1
- [x] Teacher Management *(User)* ✅
- [x] Classroom Management *(independen, akan di-link ke Teacher)* ✅

## Fase 3: Dependency Tingkat 2
- [x] Student Management *(User, Classroom)* ✅



## Fase 4: Dependency Tingkat 3
- [ ] Schedule/Timetable Management *(Classroom, Subject, Teacher, Academic Year)*

## Fase 5: Core Feature
- [ ] Attendance Records *(Student, Classroom, Schedule, Academic Year)*
- [ ] Manual Attendance Entry *(Student, Classroom)*

## Fase 6: Reporting & Dashboard
- [ ] Attendance Report *(Attendance Records, Student, Classroom)*
- [ ] Dashboard Statistics *(semua data di atas)*

---

## Legend
- [ ] = Belum dikerjakan
- [x] = Sudah selesai
