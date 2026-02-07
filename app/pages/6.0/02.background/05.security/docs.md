---
title: Security
description: It is essential to understand some basic security concepts before diving into web development. With an understanding of how the most common vulnerabilities work and some diligence in configuring your system, UserFrosting sets you up with an application that is robust against most common attack vectors.
---

It is essential to understand some basic security concepts before diving into web development.

If you aren't familiar with [OWASP](https://owasp.org/), they are considered the authoritative source on web application security. Most of what we discuss in this section is covered in OWASP's [Top 10](https://owasp.org/www-project-top-ten/) list; nonetheless we paraphrase it here and discuss the strategies and features that UserFrosting offers to mitigate many of these vulnerabilities.

This section covers the most critical security vulnerabilities you need to understand:

- [Server Misconfiguration](/background/security/server-misconfiguration): Learn why sensitive error messages should never appear in production and how to properly handle logging.
- [CSRF Protection](/background/security/csrf-protection) : Understand Cross-Site Request Forgery attacks and how UserFrosting's built-in CSRF tokens protect your forms and AJAX requests.
- [XSS Prevention](/background/security/xss-prevention) : Learn how Cross-Site Scripting works and how Twig's automatic output escaping protects your application from malicious script injection.
- [SQL Injection Prevention](/background/security/sql-injection) : Discover why SQL injection is so dangerous and how Eloquent ORM and prepared statements keep your database secure.
- [Authentication & Password Security](/background/security/authentication-and-passwords) : Master the essentials of secure password storage, session management, password resets, and multi-factor authentication.

## Security is a Mindset

Security isn't just about implementing specific features—it's about developing the right mindset throughout the development process:

**Principle of Least Privilege:** Grant only the minimum permissions necessary. Database users, file permissions, API keys—everything should have just enough access to do its job and no more.

**Defense in Depth:** Never rely on a single security measure. Layer multiple protections so that if one fails, others are still in place.

**Fail Securely:** When something goes wrong, make sure your application fails in a secure way. Don't display stack traces in production, don't fall back to permissive access, and don't leak sensitive information in error messages.

**Input Validation:** Never trust user input. Validate on the server side (client-side validation is only for user experience). Whitelist what's allowed rather than blacklisting what's not.

**Keep Dependencies Updated:** Regularly update UserFrosting and all third-party packages. Security vulnerabilities are discovered constantly, and updates often include critical security patches.

> [!IMPORTANT]
> Security is not a one-time task—it's an ongoing process. Stay informed about new vulnerabilities, follow security best practices, and regularly audit your application.

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [Laravel Security Best Practices](https://laravel.com/docs/security) (many concepts apply to UserFrosting)
- [CWE Top 25 Most Dangerous Software Weaknesses](https://cwe.mitre.org/top25/)

> [!NOTE]
> Remember: UserFrosting provides many security features out of the box, but it's ultimately your responsibility to use them correctly and follow security best practices in your custom code.
