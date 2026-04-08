# Task Workflow Management System API

A backend REST API built with **Laravel 11** for managing task lifecycles with JWT authentication, Role-Based Access Control (RBAC), workflow state management, and automated audit tracking.

---

## Core Features

- **JWT Authentication** — Secure stateless login and registration
- **RBAC** — Differentiated access for `ADMIN` and `USER` roles
- **Task Workflow** — Enforced state transitions: `PENDING` → `IN_PROGRESS` → `COMPLETED` → `APPROVED` / `REJECTED`
- **Audit Tracking** — Automatic logging of all actions via Eloquent Observers
- **Soft Deletes** — Deleted tasks remain recoverable with `deleted_at` timestamp
- **Pagination & Filtering** — Filter by status, date range with paginated results

---

## Tech Stack

- PHP 8.2 or 8.3
- Laravel 11
- MySQL 8
- JWT Auth (`php-open-source-saver/jwt-auth`)

---

## Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- MySQL

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/your-username/task-workflow.git
cd task-workflow
```

**2. Install dependencies**
```bash
composer install
```

**3. Environment configuration**
```bash
cp .env.example .env
```
Open `.env` and set:
```
DB_DATABASE=workflow
DB_USERNAME=root
DB_PASSWORD=your_password
```

**4. Generate keys**
```bash
php artisan key:generate
php artisan jwt:secret
```

**5. Run migrations**
```bash
php artisan migrate
```

**6. Start the server**
```bash
php artisan serve
```

---

## API Testing with Postman

### Import Collection

1. Find `Workflow.postman_collection.json` in the root of this repository
2. Open Postman → click **Import** → drag and drop the file
3. Set collection variable `base_url` to `http://127.0.0.1:8000/api`

### Collection Variables

| Variable | Value |
|----------|-------|
| `base_url` | `http://127.0.0.1:8000/api` |
| `token` | User JWT token (auto-set after login) |
| `admin_token` | Admin JWT token (set after admin login) |

### Testing Flow

| Step | Request | Role |
|------|---------|------|
| 1 | `POST /register` | Public |
| 2 | `POST /login` → copy token | Public |
| 3 | `POST /tasks` | USER |
| 4 | `PATCH /tasks/{id}/start` | USER |
| 5 | `PATCH /tasks/{id}/complete` | USER |
| 6 | `POST /login` (admin) → copy admin token | Public |
| 7 | `PATCH /tasks/{id}/approve` | ADMIN |
| 8 | `GET /audit-logs` | ADMIN |

---

## Task Status Flow

```
PENDING → IN_PROGRESS → COMPLETED → APPROVED
                                  → REJECTED
```

- **USER** can move: `PENDING → IN_PROGRESS → COMPLETED`
- **ADMIN** can move: `COMPLETED → APPROVED` or `COMPLETED → REJECTED`
- Invalid transitions return `422 Unprocessable Entity`

---

## RBAC Summary

| Endpoint | USER | ADMIN |
|----------|------|-------|
| Register / Login | ✓ | ✓ |
| Create / view own tasks | ✓ | ✓ |
| View all tasks | ✗ | ✓ |
| Start / Complete task | ✓ | ✓ |
| Approve / Reject task | ✗ | ✓ |
| Manage users | ✗ | ✓ |
| View audit logs | ✗ | ✓ |

---

## Architecture Decisions

**Automated Auditing** — `TaskObserver` automatically fires on every model event (`created`, `updated`, `deleted`). No manual logging needed anywhere in the codebase.

**Workflow Engine** — `WorkflowService` holds a strict transition map. Any invalid state change is rejected with a descriptive error before the database is touched.

**Security** — Role is embedded in the JWT custom claims. `AdminMiddleware` validates role on every protected route. Users cannot access or modify other users' tasks.

**Soft Deletes** — Tasks are never permanently removed. `deleted_at` timestamp is set instead, keeping full history intact.
