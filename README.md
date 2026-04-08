

# Task Workflow Management System API

This is a robust Backend API built with **Laravel 11** designed to handle complex task lifecycles. It features JWT authentication, Role-Based Access Control (RBAC), and automated audit tracking to ensure enterprise-grade security and accountability.

---

## 🚀 Core Features

- **JWT Authentication:** Secure stateless login and registration.
- **RBAC:** Differentiated access for `ADMIN` and `USER` roles.
- **Task Workflow:** Managed states: `PENDING` → `IN_PROGRESS` → `COMPLETED` → `APPROVED`/`REJECTED`.
- **Audit Tracking:** Automatic logging of `user_id` (Creator) and `updated_by` (Last Modifier/Admin).
- **Soft Deletes:** Prevents permanent data loss using Laravel's SoftDeletes trait.
- **Pagination & Filtering:** Scalable data retrieval for task lists.

---

## 🛠️ Installation & Setup Guide

Follow these steps to get the project running locally:

 1. Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL

 2. Clone the Repository
```bash
git clone [https://github.com/your-username/your-repo-name.git](https://github.com/your-username/your-repo-name.git)
cd your-repo-name
```
3. Install Dependencies
 ```bash
composer install
```
4. Environment Configuration
 Create a copy of the environment file and update your database credentials:
   ```bash
   cp .env.example .env
   ```
Open .env and set DB_DATABASE, DB_USERNAME, and DB_PASSWORD. 
5. Generate Application Keys
```bash
php artisan key:generate
php artisan jwt:secret
```
6. Run Migrations

```bash
php artisan migrate
```
7. Start the Application
 ```bash
php artisan serve
```
 📂 API Documentation & Testing

### How to use the Postman Collection:
1.  **Locate the file:** Find `Workflow.postman_collection.json` in the root folder of this repository.
2.  **Download:** Click on the file name, then click the **"Download raw file"** button (top right of the code box).
3.  **Import to Postman:** - Open your Postman Desktop app.
    - Click the **Import** button (top left).
    - Drag and drop the downloaded JSON file.
4.  **Setup Environment:** Ensure you update the `BASE_URL` in Postman to `http://127.0.0.1:8000/api`.
   
Workflow Demonstration
1. Register/Login: Obtain a JWT Bearer Token.

2. Create Task: The system automatically captures the `user_id`.

3. Complete Task: User updates status to `COMPLETED`.

4. Approve Task: Admin updates status to `APPROVED`. The system automatically captures the Admin's ID in `updated_by`.
 
🛡️ Architecture Decisions  

* Automated Auditing: Instead of manual inputs, the system extracts the authenticated user ID from the JWT payload to populate `user_id`  and `updated_by`.

* State Integrity: Admin approval is restricted exclusively to tasks in the `COMPLETED` state.

* Data Safety: Soft Deletes ensure that deleted tasks remain in the database with a `deleted_at` timestamp for recovery or history.
