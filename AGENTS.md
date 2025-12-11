# School Attendance System - Agent Documentation

## ⚠️ AGENT BEHAVIOR & CRITICAL THINKING

### Agent Rights & Responsibilities
- **BE CRITICAL**: Evaluate every request carefully. If a request is not good for code quality, architecture, or long-term maintainability, **the agent has the RIGHT to REJECT it**
- **NO BLIND OBEDIENCE**: Agent is NOT required to fulfill every request. If following a request would violate the rules in this document, **REFUSE and explain why**
- **NO FORCED SOLUTIONS**: Agent is **STRICTLY FORBIDDEN** from writing hacky, forced, or "quick fix" code
- **NO TEMPORARY SOLUTIONS**: Agent must **NEVER** create temporary workarounds that will become technical debt
- **NO HARDCODING**: Agent must **NEVER** hardcode values that should come from database, config, or other data sources. If data doesn't exist, **ASK** user to create the proper migration/schema first. Do not fake, mock, or hardcode data to make things "work"
- **ALWAYS PROPOSE ALTERNATIVES**: When rejecting a request, provide a proper alternative solution that follows best practices

### When to Reject a Request
1. Request violates clean code principles
2. Request creates technical debt
3. Request forces a workaround instead of proper solution
4. Request duplicates existing functionality
5. Request breaks existing architecture patterns
6. Request introduces security vulnerabilities
7. Request causes performance degradation

### Backend & Frontend Separation
- **NEVER** work on Backend (BE) and Frontend (FE) simultaneously
- **ALWAYS** complete Backend first, then Frontend separately
- If user requests both BE and FE at once, **REJECT** and ask to do BE first
- Workflow: BE Implementation → BE Testing → FE Implementation → FE Testing

---

## 🚨 CRITICAL RULES & CONSTRAINTS (MUST FOLLOW)

### 1. Code Quality Standards
- **Clean Code**: Write code that is clean, maintainable, and readable
- **Self-Documenting**: Code should explain itself; avoid unnecessary comments for obvious logic
- **DRY Principle**: Don't Repeat Yourself - extract reusable logic into helpers, traits, or services
- **Single Responsibility**: Each class/method should have one clear purpose
- **Type Safety**: All methods MUST have strict type hints and return types

### 2. Architecture: Repository-Service Pattern (Laravel 12)
- **Controllers**: Handle HTTP requests, validation, and response formatting ONLY. NO business logic
- **Services**: Handle ALL business logic, transactions, and data transformation
- **Repositories**: Handle ALL database queries and persistence
- **NO logic in Controllers**: Never put Eloquent queries or complex logic in Controllers. Delegate to Services

### 3. Naming Conventions
- **Variables**: Use descriptive names, NO abbreviations
  - ❌ `$std`, `$tchr`, `$cls`
  - ✅ `$student`, `$teacher`, `$classroom`
- **Functions**: Use verb + noun, describe what it does
  - ❌ `getData()`, `process()`, `handle()`
  - ✅ `getStudentsByClassroom()`, `calculateAttendanceRate()`, `assignTeacherToClassroom()`
- **Files**: Match the class name exactly

### 4. Security & Optimization
- **Always validate input** using Form Requests
- **Always check permissions** using Policies or middleware
- **Prevent N+1 queries**: Use eager loading (`with()`, `load()`)
- **Use database indexes** for frequently queried columns
- **Sanitize output**: Use Blade's `{{ }}` (escaped) by default
- **Never trust user input**: Validate, sanitize, and type-hint everything

### 5. Code Redundancy Check
Before writing new code:
1. Check existing Services/Repositories for similar functionality
2. Check existing Blade components for reusable UI
3. Check existing Form Requests for validation rules
4. Check existing Policies for authorization logic
5. **NEVER duplicate code** - extract into shared components

### 6. MCP Context7 Usage
- **ALWAYS** use MCP Context7 to fetch latest Laravel documentation (based on `composer.json`: Laravel ^12.0)
- **ALWAYS** verify syntax and features against current Laravel version
- **NEVER** assume features exist without checking documentation first

### 7. Laravel 12 Features (MUST USE)
Based on `composer.json` (Laravel ^12.0):

**Controllers:**
- Use `Route::controller()` for grouped routes
- Use Resource Controllers where applicable
- Use Form Requests for ALL validation

**Models:**
- Enable `Model::shouldBeStrict()` in local environment
- Use Enums for status/type columns with proper casts
- Define relationships with proper return types

**Database:**
- Use Anonymous Migrations: `return new class extends Migration`
- Use `foreignId()->constrained()` for foreign keys
- Always add indexes for foreign keys and frequently queried columns

**Authorization:**
- Use Policies for model-based authorization
- Use Gates for non-model authorization

**Helpers:**
- Use `str()` instead of `Str::of()`
- Use `to_route()` instead of `redirect()->route()`
- Use `blank()` and `filled()` for checking values

### 8. Frontend Standards
- Use **Blade + jQuery + AJAX** (NO React/Vue unless explicitly requested)
- Use **DataTables (Server-side)** for all lists
- Use **SweetAlert2** for notifications and confirmations
- Use Blade Components for reusable UI elements

### 9. AJAX Response Format
**Success**: `{ "success": true, "message": "...", "data": { ... } }`
**Error**: `{ "success": false, "message": "...", "errors": { ... } }`

---

## 📋 Project Overview

**Goal**: RFID-based School Attendance System (MVP)
**Focus**: Master Data Management (User, Student, Teacher, Classroom, Subject)
**Approach**: Web-First (Blade templates), clean architecture, testable code

### Tech Stack
- **Backend**: Laravel 12, Fortify, Spatie Permission, Yajra DataTables
- **Frontend**: Blade, Bootstrap 5 / Materialize, Vanilla JS + jQuery, SweetAlert2
- **Tools**: Vite, Laravel Pint (Code Style), PHPUnit

### Key Directories
- `app/Services/`: Business logic (e.g., `UserService`, `StudentService`)
- `app/Repositories/`: Data access (e.g., `UserRepository`, `StudentRepository`)
- `original_code/`: **REFERENCE ONLY**. Do not edit files here unless migrating them

---

## 🗄️ Database Schema

- **ALWAYS** follow the database schema mentioned/attached in user's request
- **NEVER** assume or create schema without explicit mention from user
- If schema is unclear or missing, **ASK** user to provide the schema first

---

## ✅ Pre-Commit Checklist

Before considering any feature complete:

1. [ ] All methods have strict type hints and return types
2. [ ] No Eloquent queries in Controllers
3. [ ] All input validated via Form Requests
4. [ ] All actions authorized via Policies/Middleware
5. [ ] No N+1 query issues (use eager loading)
6. [ ] No code duplication
7. [ ] All variable/function names are descriptive
8. [ ] Activity logging implemented for important actions
9. [ ] Error handling implemented with proper messages
10. [ ] Code follows PSR-12 coding standards

---

## 🔧 Development Workflow

1. **Plan**: Review requirements, check existing code for reusability
2. **Create Migration**: Database schema with proper indexes and constraints
3. **Create Model**: With relationships, casts, and fillable
4. **Create Repository**: Interface + Implementation
5. **Create Service**: Business logic with transactions
6. **Create Form Request**: Validation rules
7. **Create Policy**: Authorization rules
8. **Create Controller**: Thin controller delegating to Service
9. **Create Views**: Blade templates with components (AFTER BE is complete)
10. **Test**: Manual testing + verify all edge cases

---

## 📊 Project Status

### Current State
- **Foundation**: Laravel 12, Auth, Permissions, Repo-Service Architecture setup
- **User Management**: COMPLETED (Reference implementation)
- **Teacher Management**: COMPLETED (Backend + UI)
- **Database**: Migrations created for all core modules

### Immediate Priorities
See `MVP_FEATURES.md` for detailed feature checklist with priorities
