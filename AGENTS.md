# School Attendance System - Agent Documentation

## CRITICAL RULES & CONSTRAINTS (MUST FOLLOW)
1.  **Architecture**: You MUST strictly follow the **Repository-Service Pattern**.
    -   **Controllers**: Handle HTTP requests, validation, and response formatting ONLY. NO business logic.
    -   **Services**: Handle ALL business logic, transactions, and data transformation.
    -   **Repositories**: Handle ALL database queries and persistence.
2.  **No Logic in Controllers**: Never put Eloquent queries or complex logic in Controllers. Delegate to Services.
3.  **Type Safety**: All methods MUST have strict type hints and return types.
4.  **Transactions**: Use `DB::transaction()` in Services for operations involving multiple steps or models.
5.  **Authorization**: Always check permissions (e.g., `$this->middleware('permission:...')`) in Controllers.
6.  **Validation**: Use **Form Requests** for all input validation. Do not validate in Controllers.
7.  **Frontend**: Use **Blade + jQuery + AJAX**. Do NOT introduce React/Vue unless explicitly requested.
8.  **UI Components**: Use **DataTables (Server-side)** for lists and **SweetAlert2** for notifications/confirmations.
9.  **Laravel 12 Modern Practices**:
    -   **Strict Mode**: Ensure `Model::shouldBeStrict()` is enabled in local/testing environments.
    -   **Anonymous Migrations**: Always use `return new class extends Migration`.
    -   **Route Groups**: Use `Route::controller(XController::class)->group(...)` for cleaner routes.
    -   **Helpers**: Use modern helpers like `str()`, `to_route()`, `blank()`, `filled()` instead of verbose alternatives.
    -   **Enums**: Use PHP 8.1+ Enums for status/type columns and cast them in Models.

---

## Project Overview
**Goal**: RFID-based School Attendance System (MVP).
**Focus**: Master Data Management (User, Student, Teacher, Classroom, Subject).
**Approach**: Web-First (Blade templates), clean architecture, testable code.

## Tech Stack
-   **Backend**: Laravel 12, Fortify, Spatie Permission, Yajra DataTables.
-   **Frontend**: Blade, Bootstrap 5 / Materialize, Vanilla JS + jQuery, SweetAlert2.
-   **Tools**: Vite, Laravel Pint (Code Style), PHPUnit.

---

## Architecture & Directory Structure

### Repository-Service Pattern
```
app/
├── Http/Controllers/    # Request handling, Validation, Response
├── Services/            # Business Logic, Transactions
├── Repositories/        # Database Queries (Contracts + Implementations)
└── Models/              # Eloquent Models
```

### Key Directories
-   `app/Services/`: Business logic (e.g., `UserService`, `StudentService`).
-   `app/Repositories/`: Data access (e.g., `UserRepository`, `StudentRepository`).
-   `original_code/`: **REFERENCE ONLY**. Contains the original standard Laravel structure. Do not edit files here unless migrating them.

---

## Implementation Guide
›
### 1. Repository Layer
**Interface** (`app/Repositories/Contracts/`)
```php
interface EntityRepositoryInterface {
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function datatableQuery(array $filters = []): Builder;
    public function findById(int $id): ?Entity;
    public function create(array $data): Entity;
    public function update(Entity $entity, array $data): Entity;
    public function delete(Entity $entity): void;
}
```

**Implementation** (`app/Repositories/`)
```php
class EntityRepository implements EntityRepositoryInterface {
    public function datatableQuery(array $filters = []): Builder {
        return Entity::query()->when(...);
    }
    // ... implement other methods using Eloquent
}
```

### 2. Service Layer
**Location**: `app/Services/`
```php
class EntityService {
    public function __construct(private readonly EntityRepositoryInterface $repository) {}

    public function create(array $data): Entity {
        return DB::transaction(function () use ($data) {
            // 1. Prepare data
            // 2. Call Repository
            // 3. Log Activity
            return $this->repository->create($data);
        });
    }
}
```

### 3. Controller Layer
**Location**: `app/Http/Controllers/admin/`
```php
class EntityController extends Controller {
    public function __construct(private readonly EntityService $service) {
        $this->middleware('permission:entities.view')->only(['index']);
    }

    public function index(Request $request) {
        if ($request->ajax()) {
            return DataTables::of($this->service->datatableQuery($request->all()))
                ->addColumn('action', '...')
                ->make(true);
        }
        return view('pages.admin.entities.index');
    }
}
```

### 4. JSON Response Formats (AJAX)
**Success**:
```json
{ "success": true, "message": "Operation successful", "data": { ... } }
```
**Error (Validation)**:
```json
{ "message": "The given data was invalid.", "errors": { "field": ["Error message"] } }
```

---

## Database Schema (Core Tables)

-   **users**: `id, name, email, password, roles...`
-   **students**: `id, user_id, student_number, rfid_card_number, classroom_id...`
-   **teachers**: `id, user_id, teacher_number, specialization...`
-   **classrooms**: `id, name, grade_level, homeroom_teacher_id...`
-   **subjects**: `id, name, code, credit_hours...`
-   **pivots**: `classroom_subject`, `teacher_subject`

---

## Project Status

### Current State
-   **Foundation**: Laravel 12, Auth, Permissions, Repo-Service Architecture setup.
-   **User Management**: **COMPLETED** (Reference implementation).
-   **Teacher Management**: **COMPLETED** (Backend + UI).
-   **Database**: Migrations created for all core modules.

### Immediate Priorities
1.  **Student Management**: Implement Service, Repo, Controller, and UI.
2.  **Classroom Management**: Implement Service, Repo, Controller, and UI.
3.  **Subject Management**: Implement Service, Repo, Controller, and UI.
4.  **Seeders**: Complete factories/seeders for remaining models.
