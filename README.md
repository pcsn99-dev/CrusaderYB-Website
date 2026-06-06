# Admin Authentication System

For the revamped CYB Admin Portal, we implemented a complete admin authentication and authorization foundation using Laravel Breeze as the base authentication system. Breeze provided the default login, logout, password reset, profile update, and password update features, which were then customized to support the needs of the admin-side system.

## Main Additions

### 1. Laravel Breeze Authentication

Laravel Breeze was installed and used as the base authentication system. This provided the standard login flow, session handling, password reset logic, and profile/password update pages.

The public registration feature is not intended to be used for the admin portal. Admin accounts are instead managed through the Admin User Management module.

---

### 2. Existing Database Compatibility

The project uses the previous CYB website database. Because the old database already contains several tables and records, the authentication setup was adjusted to avoid recreating or dropping existing tables.

The previous database SQL file is required when setting up the project because the project does not currently contain migrations for the full old CYB schema. Some tables, such as `users`, `roles`, `permissions`, and `role_has_permissions`, already existed from the previous system and were reused instead of recreated from scratch.

---

### 3. User Type Classification

A `type` column was added to the `users` table to separate admin accounts from student accounts.

Current types:

```txt
admin
student
```

A seeder was created to classify existing users. Old admin accounts were marked as `admin`, while regular users were marked as `student`.

This allows the Admin Portal to block non-admin users from logging in.

---

### 4. Admin-Only Login Restriction

The Breeze login logic was updated so only users with:

```txt
type = admin
active = 1
```

can log in to the Admin Portal.

An additional middleware was also added to protect admin routes in case a user’s account status changes while already logged in.

This prevents student accounts and inactive admin accounts from accessing the admin side.

---

### 5. Roles and Permissions

The existing roles and permissions tables were reused and aligned with the new system.

Existing tables used:

```txt
roles
permissions
role_has_permissions
```

Additional fields were added where needed, such as:

```txt
slug
description
is_protected
```

A seeder was created for the initial admin permissions and the protected Super Admin role.

Current seeded permissions include:

```txt
view-admin-dashboard
manage-roles
manage-admin-users
view-writeups
proofread-writeups
approve-writeups
return-writeups
```

The Super Admin role is protected and automatically receives all permissions.

---

### 6. Role Management CRUD

A Role Management module was added.

Features:

```txt
Create roles
Edit roles
View roles
Delete roles if unused
Assign permissions to roles using checkboxes
Prevent deletion of protected roles
Prevent deletion of roles currently assigned to users
```

Permissions are seeded by the developer and assigned through the role form. Permission creation is not exposed through the UI to avoid creating permissions that do not match actual route middleware.

---

### 7. Admin User Management CRUD

An Admin User Management module was added.

Features:

```txt
Create admin users
Edit admin users
Assign roles
Activate/deactivate accounts
View account details
Delete admin users with restrictions
Resend temporary password
```

When creating an admin user, the Super Admin only inputs:

```txt
name
email
username
role
active status
```

The system generates the password automatically and sends it to the staff member by email.

---

### 8. Temporary Password System

Admin accounts created through the system receive a generated temporary password through email.

Added user fields:

```txt
must_change_password
temporary_password_expires_at
last_login_at
```

When a temporary password is issued:

```txt
must_change_password = 1
temporary_password_expires_at = now + 3 days
```

The staff member must change their password after logging in.

---

### 9. Forced Password Change

A forced password change middleware and page were added.

Flow:

```txt
User logs in with temporary password
System detects must_change_password = 1
User is redirected to the force password change page
User enters temporary password and new password
System updates password
must_change_password becomes 0
temporary_password_expires_at becomes null
```

Users cannot access the dashboard, roles, admin users, or other protected admin pages until they change their password.

---

### 10. Temporary Password Expiration

Temporary passwords expire after the configured expiration date.

If the temporary password is expired:

```txt
User can still reach the force password change page
Password change form is disabled
User is told to ask a Super Admin for a new temporary password
```

A Super Admin can resend a new temporary password from the admin user details page. If the user already changed their password, the system warns that resending a temporary password will reset the user’s current password and force another password change.

---

### 11. Permission Middleware

A custom permission middleware was added.

Example usage:

```php
->middleware('permission:manage-roles')
```

This allows routes to be protected based on permissions stored in the database.

Examples:

```txt
manage-roles protects Role Management
manage-admin-users protects Admin User Management
view-admin-dashboard protects the dashboard
```

The route file also includes a permission reference comment so future developers can easily see the available permission slugs.

---

### 12. Navbar Permission Visibility

The Breeze navigation layout was updated to show or hide links based on the logged-in user’s permissions.

Example:

```txt
Users with manage-roles can see Roles
Users with manage-admin-users can see Admin Users
Users without those permissions do not see those links
```

This only affects visibility. Route middleware still handles the actual security.

---

### 13. Google Login

Google login was added using Laravel Socialite.

Google login rules:

```txt
The Google email must already exist in the users table
The account must be type = admin
The account must be active
No new account is auto-created from Google login
```

This keeps account creation controlled by the Super Admin.

Google login also bypasses the local temporary password requirement because the user is authenticating through Google instead of using the temporary local password.

When Google login succeeds:

```txt
google_id is saved
avatar is saved
last_login_at is updated
must_change_password is set to 0
temporary_password_expires_at is cleared
```

---

### 14. Windows CA Certificate Requirement

During Google login testing on Windows, the system encountered this error:

```txt
cURL error 60: SSL certificate OpenSSL verify result: unable to get local issuer certificate
```

This happened because PHP/cURL on Windows could not verify Google’s HTTPS certificate.

The fix was to download `cacert.pem` and configure it in `php.ini`:

```ini
curl.cainfo = "C:\path\to\cacert.pem"
openssl.cafile = "C:\path\to\cacert.pem"
```

This gives PHP a trusted certificate authority list so it can securely connect to Google’s OAuth servers.

This may be required on Windows development machines when using Socialite, Google OAuth, or other HTTPS API requests.

---

### 15. Debugging Note

During Google login debugging, this temporary code was used to reveal the actual Socialite error:

```php
} catch (Throwable $exception) {
    dd($exception->getMessage());
}
```

This should only be used during development. After fixing the issue, it should be changed back to a normal error response:

```php
} catch (Throwable $exception) {
    return redirect()
        ->route('login')
        ->withErrors([
            'email' => 'Google login failed. Please try again.',
        ]);
}
```

---

## Final Result

The admin authentication system now supports:

```txt
Email/password login
Google login
Admin-only access
Active/inactive account control
Role-based permissions
Permission-protected routes
Permission-based navbar visibility
Role CRUD
Admin User CRUD
Temporary password email flow
Forced password change
Temporary password expiration
Super Admin protected role
```
