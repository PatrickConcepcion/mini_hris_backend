# AI Coding Agent Instructions for Mini HRIS Backend

## Architecture Overview
This is a **Laravel 12-based** mini Human Resource Information System (HRIS) backend focused on employee data management. The application uses **JWT authentication** for API access and follows **RESTful API design** with versioned endpoints (`/api/v1/`).

**Key Components:**
- **Authentication**: JWT-based API auth with login/refresh/logout endpoints
- **Employee Management**: Full CRUD operations for employee records with search and filtering
- **Data Model**: Users linked to Employees (`User` **belongsTo** `Employee`, `Employee` **hasOne** `User`)
- **Frontend**: Vite-built SPA with Tailwind CSS (separate from this backend repo)

**Data Flow**:  
API requests → Controllers (with Form Request validation) → Eloquent Models → Database

---

## Critical Workflows

- **Setup**
  - `composer run setup`
  - Installs dependencies, generates app key, runs migrations, and builds the frontend.

- **Development**
  - `composer run dev`
  - Runs:
    - `php artisan serve`
    - Queue worker (`php artisan queue:listen`)
    - Log tailing via `php artisan pail`
    - Vite dev server

- **Testing**
  - `php artisan test` for PHPUnit test suite.

- **Debugging**
  - Check `storage/logs/laravel.log` for errors.
  - Use `php artisan pail` for real-time log monitoring.

---

## Project-Specific Patterns

- **IDs**
  - All models use UUIDs via the `HasUuids` trait, not auto-incrementing integers.

- **Validation**
  - Use **Form Request classes** in `app/Http/Requests/` for all input validation.
  - Examples: `StoreEmployeeRequest`, `UpdateEmployeeRequest`.
  - Controllers should never manually validate request data with `validate()` for these endpoints if a dedicated Form Request exists.

- **Logging**
  - Controllers perform extensive logging for:
    - Creation
    - Updates
    - Deletions
    - Authentication events
  - Use appropriate log levels (`info`, `warning`, `error`), detailed further below.

- **API Responses**
  - Consistent JSON structure:
    - On success: `{"data": ..., "message": "..."}` where `data` may be a resource, collection, or `null`.
    - On errors: 4xx/5xx with a `{"message": "..."}` body (no `data` key required).
  - Do **not** leak sensitive or internal technical details in response messages.

- **Middleware**
  - Protected routes use the `auth:api` **JWT guard**.
  - Public routes (e.g., login, register if present) must be explicitly whitelisted.

- **Employee Deletion Rules**
  - An employee **must not** be deleted if they have an associated `User` account.
  - Instead, return a proper error response explaining that deletion is blocked.

- **Search & Filter**
  - Employee index supports:
    - `?search=` (by name/email/employee_no)
    - `?gender=` filtering
  - Searching and filtering should be implemented using:
    - Local Eloquent scopes, or
    - Dedicated query classes (see DB rules below).

---

## Key Files to Reference

- `routes/api.php` – API route definitions and middleware.
- `app/Models/User.php` & `app/Models/Employee.php` – Core data models and relationships.
- `app/Http/Controllers/Api/V1/AuthController.php` – JWT authentication logic.
- `app/Http/Controllers/Api/V1/EmployeeController.php` – Employee CRUD operations and logging.
- `database/migrations/2025_11_01_172439_create_employee_table.php` – Employee schema (UUID primary key, extensive nullable fields).
- `composer.json` – Scripts for setup and dev workflows.

---

## Integration Points

- **JWT Auth**
  - Configured in `config/auth.php` (`api` guard).
  - Requires `JWT_SECRET` in `.env`.
  - Respect token TTL and refresh logic already in place.

- **Queue System**
  - Queue is configured and available; current app does not yet depend on it in production flows.
  - Dev script runs `php artisan queue:listen` to keep future async tasks ready.

- **Database**
  - Standard Laravel migrations.
  - No external APIs or services are integrated yet; the app is self-contained.

---

## Coding Principles

- **SOLID & DRY**
  - Always follow SOLID principles to maintain clean, maintainable code:
    - **S**ingle Responsibility: Each class/method should have one clear purpose. A controller shouldn't handle both validation and business logic.
    - **O**pen-Closed: Code should be extensible without modification. Use interfaces and inheritance to add new functionality.
    - **L**iskov Substitution: Subtypes must be substitutable for base types. Child classes should behave like their parents.
    - **I**nterface Segregation: Prefer small, focused interfaces over large, general-purpose ones.
    - **D**ependency Inversion: Depend on abstractions, not concretions. Use interfaces in constructor injection.
  - **DRY (Don't Repeat Yourself)**: Avoid duplication; extract reusable logic into services, actions, or helpers. If you find yourself copying code, refactor it into a shared component.

- **Imports**
  - Prefer using `use` statements at the top:
    - ✅ `use App\Models\Employee;`
    - ❌ `\App\Models\Employee::` scattered inline.

- **Services & Dependency Injection**
  - **Business Logic**: Belongs in Controllers - keep controllers focused on orchestrating business logic
  - **Repeated Business Logic and Reusable Logic**: Move to Services in `app/Services/` (e.g., `EmployeeService`)
  - **Cross-Controller Reuse**: If logic is being repeated across multiple controllers, extract it to a service so other controllers can use it
  - **Same-Controller Reuse**: If repeated only within the same controller, move to its own private method within the controller to maintain readability and adhere to DRY principle
  - Inject services into controllers via constructor injection, not via facades or static calls where DI is more appropriate.

- **Graceful Failure**
  - Ensure code fails gracefully instead of breaking:
    - Catch exceptions where appropriate.
    - Log the exception (without sensitive data).
    - Return user-friendly error messages.
  - Use transactions for multi-step operations that must succeed or fail together.

- **Variable Assignment**
  - Do not assign values to variables unnecessarily:
    - ✅ `User::create($request->validated());`
    - Use a variable only if the value will be reused:
      - ✅
        ```php
        $data = $request->validated();
        $this->employeeService->syncSomething($data);
        User::create($data);
        ```

---

## Code Style & Structure

- Follow **PSR-12** for PHP coding style.
- For new PHP files, prefer `declare(strict_types=1);` at the top where feasible.
- Always type-hint parameters and return types in:
  - Controllers
  - Services
  - Actions
  - Helpers
- Prefer **early returns** over deeply nested conditionals for readability.
- API Controllers should be thin:
  - Validation in **Form Requests**
  - Business logic in **Services/Actions**
  - Database interaction through **Eloquent models** or dedicated query/Repository classes.
- For complex responses, prefer **Laravel API Resources** in `app/Http/Resources/` to keep formatting concerns out of controllers.

---

## API Behavior & Conventions

- **Status Codes**
  - `201 Created` – Successful resource creation.
  - `200 OK` – Successful read/update with a response body.
  - `204 No Content` – Successful deletion with no body.
  - `400` / `422` – Validation or request errors.
  - `401 Unauthorized` – Not authenticated.
  - `403 Forbidden` – Authenticated but not authorized.
  - `404 Not Found` – Resource truly does not exist or is not accessible to the user.

- **Validation Errors**
  - Let Form Requests handle validation.
  - Do not manually duplicate validation rules inside controllers.
  - Error responses should be clear but not leak internal structure.

- **Pagination**
  - Index endpoints should be paginated.
  - Standard query params: `?page=` and optionally `?per_page=`.
  - Include pagination metadata:
    ```json
    {
      "data": [...],
      "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 123
      }
    }
    ```

- **Sorting & Filtering**
  - Only allow whitelisted filters & sort fields.
  - Do **not** directly accept arbitrary column names from the client for sorting/filtering.

---

## Security Rules

- **Authentication & Authorization**
  - All protected endpoints must use `auth:api` (JWT).
  - For sensitive data (employee details, user accounts, HR-related fields), ensure:
    - Authorization via policies/gates or explicit checks.
  - Do not weaken or remove auth checks without explicit instruction.

- **Sensitive Data Handling**
  - Do not log:
    - Passwords
    - Tokens
    - Secrets
    - Full request bodies containing PII
  - Use `$hidden` on models to hide sensitive attributes (e.g., `password`, `remember_token`).

- **Input Handling**
  - Validate all incoming IDs as UUIDs.
  - Use route model binding where appropriate; handle `ModelNotFoundException` gracefully.
  - Never construct raw SQL using string concatenation with user input; always use bindings.

- **JWT Handling**
  - Respect token TTL and refresh behavior.
  - Logout/refresh flows should invalidate or rotate tokens properly if configured to do so.
  - Never expose raw JWT tokens in logs or error messages.

---

## Database & Eloquent Conventions

- Prefer **Eloquent** and the query builder over raw SQL.
- Avoid **N+1 queries**:
  - Use eager loading (`->with([...])`) where related records are needed.
- Common filters & search behavior:
  - Implement as **local scopes** on models (e.g., `scopeSearch`, `scopeGender`), or
  - Dedicated query classes in `app/Queries/`.
- Use database transactions (`DB::transaction()`) for multi-step changes that must be atomic.
- When adding new searchable/filterable columns:
  - Add appropriate **indexes** in new migrations.

---

## Logging Conventions

- Use log levels consistently:
  - `Log::info()` – Normal business events (created/updated/deleted, successful logins).
  - `Log::warning()` – Suspicious or abnormal but non-fatal events.
  - `Log::error()` – Exceptions, failed operations, or anything that indicates a bug or system failure.
- When catching exceptions:
  - Log the exception message and relevant context (but not secrets/PII).
  - Return a generic error response to the client.
- Avoid logging entire request payloads that may contain sensitive data.

---

## Folder Layout & Reuse

- `app/Services/` – Reusable domain logic (e.g., employee creation/updates, HR rules).
- `app/Actions/` – Single-purpose operations (e.g., `CreateEmployeeAction`, `DeactivateEmployeeAction`).
- `app/Http/Resources/` – API Resources for response formatting.
- `app/Queries/` (if created) – Encapsulated query logic for more complex filtering/searching.
- If controller methods grow too large or mix concerns:
  - Extract into services/actions.
  - Controllers should mostly handle orchestration, not business logic.

---

## Testing Practices & Rules

- When implementing new features:
  - Always create tests.

- When revising a feature:
  - Always run tests to ensure no regressions (`php artisan test`).

- **Feature Tests**
  - For every new endpoint, add tests under `tests/Feature/Api/V1/` that cover:
    - Happy path
    - Validation failures
    - Authorization failures where applicable

- **Unit Tests**
  - For services/actions with meaningful logic, add **Unit tests** in `tests/Unit/`.

- Use `RefreshDatabase` trait to ensure a clean state for each test.
- Prefer realistic data using model factories rather than hardcoded arrays where it improves clarity.

---

## AI Instructions (Behavior of the Coding Agent)

- **Ask Questions Before Implementation**
  - Before implementing non-trivial changes (e.g., new controllers, endpoints, or refactors):
    - Ask clarifying questions instead of guessing.
    - Confirm edge cases, expected behaviors, and constraints.

- **Double-Back / Challenge Assumptions**
  - Do not assume that the user’s initial idea is always ideal.
  - Politely highlight potential issues or better alternatives.
  - Ask for confirmation when a requested change might:
    - Break existing APIs
    - Affect security
    - Require a data migration

- **Follow Coding Standards**
  - Always follow the coding conventions being implemented in this project unless they constitute bad practices.
  - When encountering potentially harmful patterns, refer to industry standards and politely point out bad practices by the user.
  - Prioritize industry best practices over project-specific conventions when they conflict.
  - Follow this project's established style and patterns unless they clearly conflict with best practices; if they do, explain the concern.
  - **Ensure UUID Usage**: All new models must use UUIDs via the `HasUuids` trait instead of auto-incrementing integers.

- **Explain Implementations Clearly**
  - For major changes:
    - Describe the **existing behavior** or structure first.
    - Explain the **new implementation** and why it is better:
      - What changed
      - Why it changed
      - How it affects performance, maintainability, or security

---

## AI Guardrails (Safety for Destructive Changes)

- **Never, without explicit confirmation:**
  - Drop database tables or columns in existing migrations.
  - Modify existing migrations that have already been run in a way that would break environments.
    - Instead, create **new** migrations to evolve the schema.
  - Remove or weaken authentication or authorization checks.
  - Introduce breaking changes to API contracts (URLs, response shapes, or HTTP status codes).

- **Before Large Refactors**
  - Summarize the proposed refactor:
    - Files to touch
    - Patterns to introduce
    - Risks or trade-offs
  - Wait for explicit approval before generating or applying large-scale changes.

- **Destructive Operations**
  - If a request might cause data loss (e.g., bulk deletes, irreversible changes), explicitly warn the user and ask for confirmation.

