---
title: AJAX Requests
description: Learn how to handle AJAX requests in UserFrosting using JSON responses and RESTful endpoints.
---

AJAX (Asynchronous JavaScript and XML) requests allow you to send and receive data from the server without refreshing the page. UserFrosting makes it easy to handle AJAX requests using standard HTTP methods and JSON responses.

## Making AJAX Requests

There are many ways to make AJAX requests from the frontend, including:

- Modern **Fetch API** (built into browsers)
- **Axios** (popular promise-based HTTP client)
- jQuery's `$.ajax()` (if you're using jQuery)
- Any other HTTP client library

UserFrosting's frontend uses **Axios** for AJAX requests.

## Handling AJAX Requests in Controllers

Controllers handling AJAX requests should return JSON responses. 

### Example: A Simple AJAX Endpoint

```php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;

class UserController
{
    public function getUser(Request $request, Response $response, array $args): Response
    {
        $userId = $args['id'];
        
        // Fetch user from database
        $user = User::find($userId);
        
        // Throw exception for not found - will be converted to JSON error automatically
        if (!$user) {
            throw new NotFoundException('User not found');
        }
        
        // Prepare response data
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];
        
        // Encode and write to response body
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $response->getBody()->write($json);
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

### Example: Frontend Example with Fetch API

```javascript
// GET request
fetch('/api/users/123')
    .then(response => response.json())
    .then(data => {
        console.log('User:', data);
    })
    .catch(error => {
        console.error('Error:', error);
    });
```

## Best Practices

1. **Always validate input** - Use [request validation](/routes-and-controllers/client-input/validation) for all incoming data
2. **Include CSRF tokens** - Protect POST/PUT/DELETE requests with [CSRF guards](/routes-and-controllers/client-input/csrf-guard)
3. **Return consistent JSON** - Use a standard structure for success and error responses
4. **Use proper HTTP status codes** - 200 for success, 404 for not found, 422 for validation errors, etc.
5. **Use exceptions for errors** - Throw appropriate exceptions (e.g., `NotFoundException`, `ValidationException`) which will be automatically converted to JSON error responses with proper status codes
6. **Handle errors gracefully** - Always provide meaningful error messages in exception descriptions
