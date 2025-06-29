# Security Policy

## 🔐 1. Authentication
- All users must register and log in to access protected features.
- Passwords are securely hashed using `password_hash()` in PHP.
- Session-based authentication is used to persist user login.

## 🛡️ 2. Role-Based Access Control (RBAC)
- Three roles are defined: `admin`, `editor`, `viewer`.
- `admin`: Full access (create, edit, delete, manage users)
- `editor`: Create and edit posts, no delete
- `viewer`: Read-only access to posts

## 🧹 3. Input Validation
- **Client-side validation** using HTML5 and JavaScript
- **Server-side validation** using PHP:
  - Email format validation
  - Password length checks
  - Required fields enforcement
  - Prepared statements used for all DB queries to prevent SQL injection

## 🧱 4. Output Escaping
- All dynamic outputs are escaped using `htmlspecialchars()` to prevent XSS.

## 🔒 5. Session Security
- Sessions start with `session_start()` and are protected by role-check logic.
- Sensitive pages are accessible only if `$_SESSION['user_id']` is set.

## 📣 6. Reporting Security Issues
If you discover a security vulnerability, please report it by contacting the developer via [email@example.com].

We take security seriously and will respond promptly.
