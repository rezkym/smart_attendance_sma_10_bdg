# School Attendance System - Agent Documentation

## Project Overview

Sistem absensi sekolah berbasis RFID dengan fokus pada manajemen data master untuk MVP (Minimum Viable Product). Sistem ini dibangun menggunakan Laravel 12 dengan arsitektur Repository-Service Pattern untuk memastikan code yang clean, maintainable, dan testable.

## Tech Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Architecture Pattern**: Repository-Service Pattern
- **Authentication**: Laravel Fortify
- **Authorization**: Spatie Laravel Permission
- **Activity Logging**: Spatie Laravel Activity Log (to be implemented)
- **DataTables**: Yajra Laravel DataTables (server-side processing)
- **Development Tools**: Laravel Pail, Laravel Pint, PHPUnit

### Frontend
- **Template**: Materialize Admin Template (Blade-based)
- **CSS Framework**: Bootstrap 5 / Materialize
- **JavaScript**: Vanilla JS + jQuery (untuk DataTables)
- **DataTables**: jQuery DataTables dengan AJAX
- **Notifications**: SweetAlert2
- **Icons**: Material Icons / Font Awesome
- **Build Tool**: Vite

## Project Architecture

### Repository-Service Pattern

```
app/
├── Http/
│   ├── Controllers/     # Handle HTTP requests, delegate to Services
│   ├── Middleware/
│   └── Requests/        # Form request validation
├── Services/            # Business logic layer
│   ├── UserService.php
│   ├── StudentService.php
│   ├── TeacherService.php
│   ├── ClassroomService.php
│   └── SubjectService.php
├── Repositories/        # Data access layer
│   ├── Contracts/       # Repository interfaces
│   │   ├── UserRepositoryInterface.php
│   │   ├── StudentRepositoryInterface.php
│   │   ├── TeacherRepositoryInterface.php
│   │   ├── ClassroomRepositoryInterface.php
│   │   └── SubjectRepositoryInterface.php
│   ├── UserRepository.php
│   ├── StudentRepository.php
│   ├── TeacherRepository.php
│   ├── ClassroomRepository.php
│   └── SubjectRepository.php
└── Models/              # Eloquent models
    ├── User.php
    ├── Student.php
    ├── Teacher.php
    ├── Classroom.php
    └── Subject.php
```

### Responsibility Breakdown

**Controllers**
- Menerima HTTP requests
- Validasi input (via Form Requests)
- Memanggil Services untuk business logic
- Mengembalikan responses (JSON/Views)

**Services**
- Business logic dan orchestration
- Transaction management
- Data transformation
- Koordinasi antar multiple repositories
- Error handling

**Repositories**
- Database queries (CRUD operations)
- Data persistence
- Query optimization
- Implementasi dari Repository Contracts

**Models**
- Eloquent ORM definitions
- Relationships
- Accessors & Mutators
- Model events

## MVP Features

> **Implementation Approach**: Sistem dibangun dengan **Web-First approach** menggunakan Blade templates + AJAX untuk interaksi dinamis. Testing dan dokumentasi API akan dilakukan setelah semua fitur MVP selesai untuk efisiensi development.

### 1. User Management (Kelola Semua User) ✅ **COMPLETED**
- ✅ CRUD operations untuk semua user (Admin, Teacher, Student)
- ✅ Role & Permission management (menggunakan Spatie Permission)
- ✅ User authentication & authorization dengan middleware
- ✅ DataTables server-side processing untuk performance
- ✅ Rich UI dengan modal forms, SweetAlert2 confirmations
- ✅ Role-based navigation dan menu filtering

**Implemented Routes (Web + AJAX):**
```
GET     /admin/users              # List users (DataTables)
POST    /admin/users              # Create new user
GET     /admin/users/{user}       # Get user detail (JSON)
PUT     /admin/users/{user}       # Update user
DELETE  /admin/users/{user}       # Delete user
POST    /admin/users/{user}/roles # Assign roles
```

**Implementation Details:**
- Repository: `UserRepository` dengan interface `UserRepositoryInterface`
- Service: `UserService` dengan transaction management
- Controller: `admin/UserController` dengan permission middleware
- Requests: `StoreUserRequest`, `UpdateUserRequest`, `UpdateUserRolesRequest`
- Resource: `UserResource` untuk consistent JSON responses
- Views: `resources/views/content/pages/admin/users/`
- JS: `resources/js/admin/user-management.js` (DataTables + AJAX)

### 2. Student Management (Kelola Pelajar)
- CRUD students
- Assign RFID card to student
- Assign student to classroom
- Student profile management
- DataTables interface dengan search & filter

**Required Routes:**
```
GET     /admin/students              # List students (DataTables)
POST    /admin/students              # Create new student
GET     /admin/students/{student}    # Get student detail (JSON)
PUT     /admin/students/{student}    # Update student
DELETE  /admin/students/{student}    # Delete student
PUT     /admin/students/{student}/rfid # Assign/Update RFID
```

### 3. Teacher Management (Kelola Guru)
- CRUD teachers
- Teacher profile management
- Assign subjects to teacher
- Assign teacher to classroom (as homeroom teacher)
- DataTables interface dengan search & filter

**Required Routes:**
```
GET     /admin/teachers              # List teachers (DataTables)
POST    /admin/teachers              # Create new teacher
GET     /admin/teachers/{teacher}    # Get teacher detail (JSON)
PUT     /admin/teachers/{teacher}    # Update teacher
DELETE  /admin/teachers/{teacher}    # Delete teacher
```

### 4. Classroom Management (Kelola Kelas)
- CRUD classrooms
- Assign students to classroom
- Assign homeroom teacher
- Classroom capacity management
- DataTables interface dengan search & filter

**Required Routes:**
```
GET     /admin/classrooms                 # List classrooms (DataTables)
POST    /admin/classrooms                 # Create new classroom
GET     /admin/classrooms/{classroom}     # Get classroom detail (JSON)
PUT     /admin/classrooms/{classroom}     # Update classroom
DELETE  /admin/classrooms/{classroom}     # Delete classroom
POST    /admin/classrooms/{classroom}/students  # Assign students
```

### 5. Subject Management (Kelola Mata Pelajaran)
- CRUD subjects
- Assign teachers to subjects
- Link subjects to classrooms
- DataTables interface dengan search & filter

**Required Routes:**
```
GET     /admin/subjects              # List subjects (DataTables)
POST    /admin/subjects              # Create new subject
GET     /admin/subjects/{subject}    # Get subject detail (JSON)
PUT     /admin/subjects/{subject}    # Update subject
DELETE  /admin/subjects/{subject}    # Delete subject
```

## Database Schema (Required Migrations)

### Core Tables

```sql
-- users (already exists, need modifications)
users
- id
- name
- email
- password
- email_verified_at
- remember_token
- two_factor_secret
- two_factor_recovery_codes
- two_factor_confirmed_at
- created_at
- updated_at

-- students
students
- id
- user_id (FK to users)
- student_number (unique)
- rfid_card_number (unique, nullable)
- date_of_birth
- gender
- address
- phone_number
- parent_name
- parent_phone
- classroom_id (FK to classrooms, nullable)
- created_at
- updated_at

-- teachers
teachers
- id
- user_id (FK to users)
- teacher_number (unique)
- date_of_birth
- gender
- address
- phone_number
- specialization (keahlian)
- created_at
- updated_at

-- classrooms
classrooms
- id
- name (e.g., "X-IPA-1", "XI-IPS-2")
- grade_level (10, 11, 12)
- academic_year (e.g., "2024/2025")
- homeroom_teacher_id (FK to teachers, nullable)
- capacity
- description
- created_at
- updated_at

-- subjects
subjects
- id
- name
- code (unique)
- description
- credit_hours
- created_at
- updated_at

-- Pivot Tables
classroom_subject (many-to-many)
- id
- classroom_id
- subject_id
- teacher_id
- schedule (JSON field for schedule information)
- created_at
- updated_at

teacher_subject (many-to-many)
- id
- teacher_id
- subject_id
- created_at
- updated_at
```

### Permission & Role Structure

**Roles:**
- Super Admin (full access)
- Admin (manage all data)
- Teacher (view students, manage attendance)
- Student (view own attendance)

**Permissions Example:**
```
users.view
users.create
users.update
users.delete

students.view
students.create
students.update
students.delete

teachers.view
teachers.create
teachers.update
teachers.delete

classrooms.view
classrooms.create
classrooms.update
classrooms.delete

subjects.view
subjects.create
subjects.update
subjects.delete
```

## Development Roadmap

### Phase 1: Foundation Setup ✅ **COMPLETED**
- [x] Laravel 12 installation
- [x] Fortify authentication setup
- [x] Spatie Permission setup
- [x] Basic directory structure (Repositories, Services)
- [x] Create AGENTS.md documentation
- [x] Role & Permission seeders with JSON data
- [x] Default user seeder

### Phase 2: User Management (Feature #1) ✅ **COMPLETED**
- [x] User Repository (Interface + Implementation)
- [x] User Service dengan transaction management
- [x] User Controller dengan permission middleware
- [x] Form Request validators (Store, Update, UpdateRoles)
- [x] UserResource untuk JSON responses
- [x] DataTables server-side integration
- [x] Web UI dengan modal forms
- [x] JavaScript module (AJAX + SweetAlert2)
- [x] Role-based navigation system
- [x] Admin routes dan menu integration

### Phase 3: Database & Models (Student, Teacher, Classroom, Subject)
- [x] Create migrations (students, teachers, classrooms, subjects, pivot tables)
- [x] Create Eloquent models with relationships
- [ ] Setup model factories _(Teacher factory added; others pending)_
- [ ] Database seeders untuk initial data _(Subject seeder added)_

### Phase 4: Student Management (Feature #2)
- [ ] Student Repository Layer
- [ ] Student Service Layer
- [ ] Student Controller & Routes
- [ ] Student Form Requests & Resource
- [ ] Student UI (DataTables + Modal)
- [ ] RFID card assignment functionality

### Phase 5: Teacher Management (Feature #3)
- [x] Teacher Repository Layer
- [x] Teacher Service Layer
- [x] Teacher Controller & Routes
- [x] Teacher Form Requests & Resource
- [x] Teacher UI (DataTables + Modal)
- [x] Subject assignment functionality
- [x] Homeroom assignment (classroom) endpoint & UI

### Phase 6: Classroom Management (Feature #4)
- [ ] Classroom Repository Layer
- [ ] Classroom Service Layer
- [ ] Classroom Controller & Routes
- [ ] Classroom Form Requests & Resource
- [ ] Classroom UI (DataTables + Modal)
- [ ] Student assignment functionality
- [ ] Homeroom teacher assignment

### Phase 7: Subject Management (Feature #5)
- [ ] Subject Repository Layer
- [ ] Subject Service Layer
- [ ] Subject Controller & Routes
- [ ] Subject Form Requests & Resource
- [ ] Subject UI (DataTables + Modal)
- [ ] Teacher-Subject linking

### Phase 8: Testing & Documentation (Post-MVP Development)
> Testing dan dokumentasi akan dilakukan setelah semua fitur MVP selesai untuk efisiensi development cycle.

- [ ] Unit tests untuk semua Services
- [ ] Unit tests untuk semua Repositories
- [ ] Feature tests untuk web endpoints
- [ ] Update inline code documentation (PHPDoc)
- [ ] Create comprehensive README

### Phase 9: Future Features (Post-MVP)
- [ ] REST API endpoints (untuk mobile/third-party integration)
- [ ] RFID Attendance API untuk ESP32
- [ ] Attendance reporting & analytics
- [ ] Dashboard dengan statistics
- [ ] Export functionality (PDF, Excel)
- [ ] Advanced search & filtering

## Code Standards & Best Practices

### Service Layer Example

```php
<?php

namespace App\Services;

use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository
    ) {}

    public function getAllStudents(array $filters = [])
    {
        return $this->studentRepository->getAll($filters);
    }

    public function createStudent(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Create user first
            $user = User::create([...]);
            $user->assignRole('student');

            // Create student profile
            $student = $this->studentRepository->create([
                'user_id' => $user->id,
                ...
            ]);

            // Log activity
            activity()->log('Student created');

            return $student;
        });
    }
}
```

### Repository Layer Example

```php
<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;

class StudentRepository implements StudentRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Student::with(['user', 'classroom']);

        if (!empty($filters['classroom_id'])) {
            $query->where('classroom_id', $filters['classroom_id']);
        }

        return $query->paginate(15);
    }

    public function findById(int $id): ?Student
    {
        return Student::with(['user', 'classroom'])->find($id);
    }

    public function create(array $data): Student
    {
        return Student::create($data);
    }
}
```

### Controller Layer Example

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Services\StudentService;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService
    ) {}

    public function index(Request $request)
    {
        $students = $this->studentService->getAllStudents(
            $request->only(['classroom_id', 'search'])
        );

        return StudentResource::collection($students);
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->createStudent(
            $request->validated()
        );

        return new StudentResource($student);
    }
}
```

## JSON Response Format

> Semua AJAX endpoints mengembalikan JSON responses dengan format konsisten

### Success Response (CRUD Operations)
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {
        "id": 1,
        "name": "User Name",
        "email": "user@example.com",
        "roles": ["admin"],
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Success Response (Delete Operation)
```json
{
    "success": true,
    "message": "User deleted successfully."
}
```

### Error Response (Validation)
```json
{
    "message": "The email field is required.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

### DataTables Response Format
```json
{
    "draw": 1,
    "recordsTotal": 100,
    "recordsFiltered": 50,
    "data": [
        {
            "id": 1,
            "name": "User Name",
            "email": "user@example.com",
            "roles": "admin, teacher",
            "email_verified_at": "2024-01-01 00:00",
            "actions": "<button>...</button>"
        }
    ]
}
```

## Environment Configuration

Required `.env` variables:
```
APP_NAME="School Attendance System"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_attendance
DB_USERNAME=root
DB_PASSWORD=

# For future RFID integration
ESP32_API_KEY=your-secret-key
```

## Build, Test, and Development Commands

Run `composer install` and `npm install` after cloning to sync PHP and Node dependencies.
Use `composer run dev` for a full development loop (Laravel server, queue listener, log tail, and Vite).
Execute `php artisan migrate --seed` to provision a fresh database, and `npm run build` to produce production assets.

## Coding Style & Naming Conventions

Adhere to PSR-12 with 4-space indentation; format PHP with `./vendor/bin/pint`.
Name controllers and jobs in PascalCase (e.g., `StudentController`, `CreateStudentJob`), while database tables and migrations use snake_case timestamps.
Front-end scripts follow the Airbnb ESLint rules; run `npx eslint resources/js --fix` and `npx stylelint "resources/css/**/*.scss"` before pushing.

## Testing Strategy

> **Deferred Testing Approach**: Untuk efisiensi development cycle, comprehensive testing akan dilakukan di Phase 8 setelah semua fitur MVP (User, Student, Teacher, Classroom, Subject Management) selesai diimplementasikan.

### Testing Plan (Phase 8)

**Unit Tests** (`tests/Unit/`)
- Repository tests: Verify database operations, filtering, eager loading
- Service tests: Business logic, transaction management, data transformation

**Feature Tests** (`tests/Feature/`)
- Web endpoint tests: Full request/response cycle untuk semua CRUD operations
- Permission tests: Verify authorization middleware
- DataTables tests: Server-side processing endpoints

**Testing Commands:**
```bash
php artisan test                              # Run all tests
php artisan test --testsuite=Unit            # Unit tests only
php artisan test --testsuite=Feature         # Feature tests only
php artisan test --filter UserServiceTest    # Specific test class
```

**Best Practices:**
- Use `RefreshDatabase` trait untuk clean state
- Use model factories untuk test data
- Test both happy path dan error scenarios
- Include assertions untuk database state changes
- Mock external services jika diperlukan

## Commit & Pull Request Guidelines

Follow Conventional Commit prefixes (`feat:`, `fix:`, `chore:`) as seen in the existing history.
Each pull request should describe the problem, the solution, and include before/after screenshots for UI updates.
Link the related issue card, list any database or env changes, and note the tests run so reviewers can reproduce results quickly.

## Notes for AI Agents

1. **Always follow Repository-Service Pattern**: Never put business logic in Controllers or Repositories
2. **Use Type Hints**: All methods should have proper type hints and return types
3. **Transaction Management**: Use DB::transaction() for operations involving multiple models
4. **Authorization**: Always check permissions before performing actions using middleware
5. **Validation**: Use Form Requests for input validation
6. **Resources**: Use Laravel API Resources for consistent JSON responses
7. **Code Style**: Follow PSR-12 and Laravel best practices (use Laravel Pint)
8. **Documentation**: Keep this AGENTS.md updated as features are added
9. **Testing Approach**: Unit tests dan feature tests akan ditulis setelah semua fitur MVP selesai (Phase 8) untuk efisiensi development cycle
10. **UI Pattern**: Gunakan DataTables server-side + Modal forms + SweetAlert2 untuk consistency dengan User Management feature

## Implementation Pattern (Follow for All Features)

Berdasarkan User Management implementation yang sudah completed, ikuti pattern ini untuk fitur selanjutnya:

### 1. Repository Layer
```php
// Interface di app/Repositories/Contracts/
interface EntityRepositoryInterface {
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function datatableQuery(array $filters = []): Builder;
    public function findById(int $id): ?Entity;
    public function create(array $data): Entity;
    public function update(Entity $entity, array $data): Entity;
    public function delete(Entity $entity): void;
}

// Implementation di app/Repositories/
class EntityRepository implements EntityRepositoryInterface { ... }
```

### 2. Service Layer
```php
// Di app/Services/
class EntityService {
    public function __construct(private readonly EntityRepositoryInterface $repository) {}

    public function create(array $data): Entity {
        return DB::transaction(function () use ($data) {
            // Business logic dengan transaction
        });
    }
}
```

### 3. Controller Layer
```php
// Di app/Http/Controllers/admin/
class EntityController extends Controller {
    public function __construct(private readonly EntityService $service) {
        $this->middleware('permission:entities.view')->only(['index', 'show']);
        // ... other permissions
    }

    public function index(Request $request) {
        if ($request->ajax()) {
            // Return DataTables JSON
        }
        return view('...', ['data' => $data]);
    }
}
```

### 4. Validation & Resources
- Form Requests: `StoreEntityRequest`, `UpdateEntityRequest`
- Resource: `EntityResource` untuk consistent JSON structure

### 5. UI & JavaScript
- View: `resources/views/content/pages/admin/entities/index.blade.php`
- JS Module: `resources/js/admin/entity-management.js`
- Pattern: DataTables + Modal forms + SweetAlert2 confirmations

## Next Steps

**Current Status**: User Management ✅ Completed; Teacher Management backend + UI ✅ (DataTables, assign subjects/homeroom); Phase 3 migrations/models ✅ (factories/seeders pending).

**Next Priority**:
1. Lengkapi factories & seeders yang tersisa (students/teachers/classrooms/subjects) untuk Phase 3.
2. Mulai Phase 4 (Student Management) mengikuti pattern User/Teacher modules.
