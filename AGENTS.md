# School Attendance System - Agent Documentation

## Project Overview

Sistem absensi sekolah berbasis RFID dengan fokus pada manajemen data master untuk MVP (Minimum Viable Product). Sistem ini dibangun menggunakan Laravel 12 dengan arsitektur Repository-Service Pattern untuk memastikan code yang clean, maintainable, dan testable.

## Tech Stack

- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Architecture Pattern**: Repository-Service Pattern
- **Authentication**: Laravel Fortify
- **Authorization**: Spatie Laravel Permission
- **Activity Logging**: Spatie Laravel Activity Log
- **DataTables**: Yajra Laravel DataTables
- **Development Tools**: Laravel Pail, Laravel Pint, PHPUnit

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

## MVP Features (Backend Only)

### 1. User Management (Kelola Semua User)
- CRUD operations untuk semua user (Admin, Teacher, Student)
- Role & Permission management (menggunakan Spatie Permission)
- User authentication & authorization
- Activity logging untuk audit trail

**Endpoints:**
```
GET    /api/users              # List all users
GET    /api/users/{id}         # Get user detail
POST   /api/users              # Create new user
PUT    /api/users/{id}         # Update user
DELETE /api/users/{id}         # Delete user
POST   /api/users/{id}/roles   # Assign roles
```

### 2. Student Management (Kelola Pelajar)
- CRUD students
- Assign RFID card to student
- Assign student to classroom
- Student profile management

**Endpoints:**
```
GET    /api/students           # List all students
GET    /api/students/{id}      # Get student detail
POST   /api/students           # Create new student
PUT    /api/students/{id}      # Update student
DELETE /api/students/{id}      # Delete student
PUT    /api/students/{id}/rfid # Assign/Update RFID
```

### 3. Teacher Management (Kelola Guru)
- CRUD teachers
- Teacher profile management
- Assign subjects to teacher
- Assign teacher to classroom (as homeroom teacher)

**Endpoints:**
```
GET    /api/teachers           # List all teachers
GET    /api/teachers/{id}      # Get teacher detail
POST   /api/teachers           # Create new teacher
PUT    /api/teachers/{id}      # Update teacher
DELETE /api/teachers/{id}      # Delete teacher
```

### 4. Classroom Management (Kelola Kelas)
- CRUD classrooms
- Assign students to classroom
- Assign homeroom teacher
- Classroom capacity management

**Endpoints:**
```
GET    /api/classrooms             # List all classrooms
GET    /api/classrooms/{id}        # Get classroom detail
POST   /api/classrooms             # Create new classroom
PUT    /api/classrooms/{id}        # Update classroom
DELETE /api/classrooms/{id}        # Delete classroom
POST   /api/classrooms/{id}/students  # Assign students
```

### 5. Subject Management (Kelola Mata Pelajaran)
- CRUD subjects
- Assign teachers to subjects
- Link subjects to classrooms

**Endpoints:**
```
GET    /api/subjects           # List all subjects
GET    /api/subjects/{id}      # Get subject detail
POST   /api/subjects           # Create new subject
PUT    /api/subjects/{id}      # Update subject
DELETE /api/subjects/{id}      # Delete subject
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

### Phase 1: Foundation Setup (Current)
- [x] Laravel 12 installation
- [x] Fortify authentication setup
- [x] Spatie Permission setup
- [x] Basic directory structure (Repositories, Services)
- [x] Create AGENTS.md documentation

### Phase 2: Database & Models
- [ ] Create all migrations (students, teachers, classrooms, subjects, pivot tables)
- [ ] Create Eloquent models with relationships
- [ ] Setup model factories for testing
- [ ] Database seeders for initial data

### Phase 3: Repository Layer
- [ ] Create Repository Contracts (Interfaces)
- [ ] Implement User Repository
- [ ] Implement Student Repository
- [ ] Implement Teacher Repository
- [ ] Implement Classroom Repository
- [ ] Implement Subject Repository
- [ ] Bind repositories to service container

### Phase 4: Service Layer
- [ ] Create UserService
- [ ] Create StudentService
- [ ] Create TeacherService
- [ ] Create ClassroomService
- [ ] Create SubjectService

### Phase 5: API Controllers & Routes
- [ ] Create Form Request validators
- [ ] Create API Resources for JSON responses
- [ ] Implement UserController
- [ ] Implement StudentController
- [ ] Implement TeacherController
- [ ] Implement ClassroomController
- [ ] Implement SubjectController
- [ ] Define API routes
- [ ] Setup API middleware & authentication

### Phase 6: Testing & Documentation
- [ ] Unit tests for Services
- [ ] Integration tests for Repositories
- [ ] API endpoint tests
- [ ] Postman/OpenAPI documentation

### Phase 7: Future Features (Post-MVP)
- [ ] RFID Attendance API for ESP32
- [ ] Attendance reporting
- [ ] Dashboard with statistics
- [ ] Export functionality (PDF, Excel)
- [ ] Front-end implementation

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

## API Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["validation error"]
    }
}
```

### Paginated Response
```json
{
    "success": true,
    "data": [...],
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
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

## Testing Guidelines

Feature and API coverage belongs in `tests/Feature`, with pure logic isolated in `tests/Unit`.
Run `php artisan test` locally; for focused checks use `phpunit --testsuite=Feature --filter Student`.
Prefer using model factories and `RefreshDatabase` to keep tests hermetic, and include assertions for emitted events or activity logs when relevant.

## Commit & Pull Request Guidelines

Follow Conventional Commit prefixes (`feat:`, `fix:`, `chore:`) as seen in the existing history.
Each pull request should describe the problem, the solution, and include before/after screenshots for UI updates.
Link the related issue card, list any database or env changes, and note the tests run so reviewers can reproduce results quickly.

## Notes for AI Agents

1. **Always follow Repository-Service Pattern**: Never put business logic in Controllers or Repositories
2. **Use Type Hints**: All methods should have proper type hints and return types
3. **Transaction Management**: Use DB::transaction() for operations involving multiple models
4. **Activity Logging**: Log all important actions using Spatie Activity Log
5. **Authorization**: Always check permissions before performing actions
6. **Validation**: Use Form Requests for input validation
7. **API Resources**: Use Laravel API Resources for consistent JSON responses
8. **Testing**: Write tests alongside feature implementation
9. **Code Style**: Follow PSR-12 and Laravel best practices (use Laravel Pint)
10. **Documentation**: Keep this AGENTS.md updated as features are added

## Next Steps

Tunggu instruksi selanjutnya untuk memulai implementasi MVP features. Prioritas:
1. Database migrations
2. Models & relationships
3. Repository layer
4. Service layer
5. API Controllers & Routes
