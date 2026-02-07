---
title: SQL Injection Prevention
description: SQL injection is one of the most dangerous web vulnerabilities. Learn how UserFrosting's use of Eloquent ORM and prepared statements protects your application.
---

## What is SQL Injection?

SQL injection is a code injection technique where attackers insert malicious SQL code into queries, potentially allowing them to:

- Read sensitive data from the database
- Modify or delete database records
- Execute administrative operations
- In some cases, execute commands on the operating system

### Example of a Vulnerable Query

Consider this dangerous PHP code:

```php
// NEVER DO THIS!
$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $db->query($sql);
```

An attacker could submit:
- Username: `admin' --`
- Password: (anything)

The resulting query becomes:
```sql
SELECT * FROM users WHERE username = 'admin' -- AND password = ''
```

The `--` comments out the rest of the query, allowing the attacker to log in as admin without knowing the password!

## How UserFrosting Protects Against SQL Injection

UserFrosting uses **Laravel's Eloquent ORM** and the **query builder**, which automatically use **prepared statements** with parameter binding. This separates SQL code from data, making injection attacks impossible.

### Safe Queries with Eloquent

When using Eloquent models, queries are automatically protected:

```php
use UserFrosting\Sprinkle\Account\Database\Models\User;

// Safe - uses parameter binding internally
$user = User::where('username', $username)
    ->where('email', $email)
    ->first();

// Also safe - even with multiple conditions
$users = User::where('last_login', '>', $date)
    ->where('group_id', $groupId)
    ->get();
```

### Safe Queries with the Query Builder

The query builder also uses prepared statements:

```php
use Illuminate\Database\Connection;

class MyService
{
    public function __construct(
        protected Connection $db
    ) {}
    
    public function getActiveUsers(): Collection
    {
        // Safe - parameters are bound
        return $this->db->table('users')
            ->where('status', 'active')
            ->where('age', '>', 18)
            ->get();
    }
    
    public function getAdmins(): Collection
    {
        // Safe - using whereIn
        return $this->db->table('users')
            ->whereIn('role_id', [1, 2, 3])
            ->get();
    }
}
```

### Parameterized Raw Queries

If you need to use raw SQL, always use parameter binding:

```php
// Safe - using parameter binding with positional parameters
$users = $this->db->select(
    'SELECT * FROM users WHERE email = ? AND status = ?', 
    [$email, 'active']
);

// Also safe - using named parameters
$users = $this->db->select(
    'SELECT * FROM users WHERE email = :email AND status = :status', 
    ['email' => $email, 'status' => 'active']
);
```

## Dangerous Patterns to Avoid

### Never Concatenate User Input into Queries

```php
// EXTREMELY DANGEROUS!
$sql = "SELECT * FROM users WHERE username = '" . $username . "'";
$this->db->select($sql);

// Also dangerous with query builder!
$this->db->table('users')
    ->whereRaw("username = '" . $username . "'")  // DON'T DO THIS
    ->get();
```

### Be Careful with WhereRaw and Raw Expressions

While `whereRaw()` can be necessary for complex queries, never include unescaped user input:

```php
// Dangerous!
$this->db->table('users')
    ->whereRaw("YEAR(created_at) = " . $_GET['year'])
    ->get();

// Safe - use parameter binding
$this->db->table('users')
    ->whereRaw("YEAR(created_at) = ?", [$year])
    ->get();
```

### Dynamic Column Names

Be extremely careful when user input determines which column to query:

```php
// Dangerous if $column comes from user input!
$results = User::where($column, $value)->get();

// Safe - whitelist allowed columns
$allowedColumns = ['username', 'email', 'created_at'];
if (in_array($column, $allowedColumns)) {
    $results = User::where($column, $value)->get();
} else {
    throw new \InvalidArgumentException('Invalid column name');
}
```

### ORDER BY and Dynamic Sorting

User-controlled sorting can be exploited:

```php
// Dangerous!
$orderBy = $_GET['sort'];  // Could be: "username; DELETE FROM users--"
User::orderBy($orderBy)->get();

// Safe - whitelist columns and directions
$allowedColumns = ['username', 'email', 'created_at'];
$allowedDirections = ['asc', 'desc'];

$column = in_array($_GET['sort'], $allowedColumns) ? $_GET['sort'] : 'username';
$direction = in_array($_GET['dir'], $allowedDirections) ? $_GET['dir'] : 'asc';

User::orderBy($column, $direction)->get();
```

## Advanced Protection Techniques

### Input Validation with Fortress

While Eloquent protects against SQL injection, you should still validate input. UserFrosting includes **Fortress**, a validation and sanitization library that helps ensure data conforms to expected types and formats:

```php
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;

// Define validation rules
$schema = new RequestSchema('schema://requests/user.yaml');

// Validate request data
$validator = new JqueryValidationAdapter($schema, $this->translator);
$validator->validate($data);

// At this point, data is validated and safe to use
$user = User::find($data['user_id']);
```

Fortress schemas can define:
- Data types (integer, string, email, etc.)
- Length constraints
- Regular expression patterns
- Custom validation rules

This provides defense-in-depth: even if an attacker somehow bypassed client-side validation, Fortress catches invalid input on the server before it reaches your database queries.

For simple type validation:

```php
// Validate that user ID is actually a number
if (!is_numeric($userId)) {
    throw new \InvalidArgumentException('User ID must be numeric');
}

$user = User::find($userId);
```

### Principle of Least Privilege

Configure your database user with minimal permissions:

```sql
-- Database user should only have necessary permissions
GRANT SELECT, INSERT, UPDATE ON myapp.* TO 'myapp_user'@'localhost';

-- Avoid giving DELETE or DROP permissions unless necessary
```

### Use Views for Complex Queries

For complex reporting queries, consider using database views:

```sql
CREATE VIEW active_users AS
SELECT id, username, email, created_at
FROM users
WHERE status = 'active' AND deleted_at IS NULL;
```

Then query the view safely:
```php
$users = $this->db->table('active_users')->get();
```

## What About Table/Column Names?

Eloquent and the query builder handle table and column names correctly, but if you need to work with dynamic table names:

```php
// Whitelist table names
$allowedTables = ['users', 'roles', 'permissions'];

if (!in_array($tableName, $allowedTables)) {
    throw new \InvalidArgumentException('Invalid table name');
}

$this->db->table($tableName)->get();
```

## Testing for SQL Injection

You can test your application for SQL injection vulnerabilities by trying these payloads in your inputs:

```
' OR '1'='1
' OR '1'='1' --
' OR '1'='1' /*
admin' --
') OR ('1'='1
```

If your application uses Eloquent/query builder properly, these should be treated as literal strings and won't execute as SQL.
