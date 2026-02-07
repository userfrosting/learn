---
title: Authentication & Password Security
description: Proper authentication and password handling are fundamental to application security. Learn how UserFrosting implements secure authentication and password storage.
---

## Password Storage

Never, ever store passwords in plain text. If your database is compromised, plain text passwords allow attackers to access user accounts not only on your site, but potentially on other sites where users have reused passwords.

## How UserFrosting Stores Passwords and Secures Accounts

UserFrosting uses **bcrypt** hashing to securely store passwords. Bcrypt is specifically designed for password hashing and includes:

- **Salting**: Each password gets a unique salt
- **Cost factor**: Computational difficulty that can be increased over time
- **Slow hashing**: Deliberately slow to make brute-force attacks impractical

### Minimum Password Complexity

UserFrosting's default password rules require:
- Minimum length (typically 8-12 characters)
- Mix of character types (uppercase, lowercase, numbers, symbols)

These can be customized through validation rules and configuration.

### Session Hijacking Prevention

UserFrosting implements several protections against session hijacking:

**1. Regenerate Session ID on Login**

This prevents session fixation attacks where an attacker tricks a user into using a known session ID.

**2. HTTP-Only Cookies**

Session cookies are marked `HttpOnly`, preventing JavaScript from accessing them. This mitigates XSS-based session theft.

**3. Secure Flag for HTTPS**

When using HTTPS, session cookies are marked `Secure`, ensuring they are only sent over encrypted connections.

**4. SameSite Cookie Attribute**

Helps prevent CSRF attacks by restricting cross-site cookie sending.

### Session Timeout

Implement appropriate session timeouts to limit the window for session hijacking:

```php
// UserFrosting's config
'session' => [
    'timeout' => 3600,  // 1 hour in seconds
    'remember_me_timeout' => 604800,  // 1 week for "remember me"
],
```

### Multi-Factor Authentication (MFA)

While UserFrosting does not include built-in multi-factor authentication (MFA) by default, it is highly recommended for enhancing security. 

Consider implementing multi-factor authentication for sensitive applications. UserFrosting can be extended with MFA using packages like:

- TOTP (Time-based One-Time Password) - Google Authenticator, Authy
- SMS-based codes
- Email-based codes
- Hardware tokens (U2F, WebAuthn)

> [!NOTE]
> Future versions of UserFrosting may include built-in MFA support. Check the documentation for updates.

### Throttling

Protect against brute-force attacks by implementing account lockout. After a certain number of failed login attempts, UserFrosting temporarily locks the login form for that user or IP address.

### Password Reset Security

Password reset functionality is a common attack vector. UserFrosting implements secure password resets using time-limited, single-use tokens and email verification.
