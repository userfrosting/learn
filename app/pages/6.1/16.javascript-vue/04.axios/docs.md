---
title: Axios HTTP Client
description: Learn what Axios is and how to use it for HTTP requests in UserFrosting
---

Modern web applications need to pass data from the backend (PHP) to the frontend (JavaScript/Vue). This is typically done through HTTP requests to API endpoints. To manage this communication, UserFrosting uses Axios, a powerful and easy-to-use HTTP client library. With Axios, you can send requests to your backend APIs, handle responses, and manage errors in a clean and efficient way.

## What Is Axios?

[Axios](https://axios-http.com) is a Promise-based HTTP client for JavaScript. Compared to the native `fetch` API, Axios gives you helpful defaults and features out of the box:

- automatic JSON transformation
- request and response interceptors
- timeout support
- easy query params handling
- consistent error objects

If you already use `fetch`, Axios will feel familiar, but often requires less boilerplate for real-world apps.

## Import Axios

Axios is already installed in UserFrosting, so you can import it directly in your TypeScript file:

```typescript
import axios from 'axios'
```

## Your First Request

Example GET request, using TypeScript generics to type the response:

```typescript
import axios from 'axios'

interface User {
  id: number
  user_name: string
  first_name: string
  last_name: string
}

async function loadUsers(): Promise<User[]> {
  const response = await axios.get<User[]>(`/api/users`)
  return response.data
}
```

In the example above, Axios will automatically parse the JSON response and return it as a JavaScript object. The `response.data` property contains the actual response body, while `response.status` and `response.headers` provide additional metadata about the response:

```typescript
const response = await axios.get('/api/users')

console.log(response.data)    // Response body
console.log(response.status)  // HTTP status code (200, 404, ...)
console.log(response.headers) // Response headers
```

## Common Request Types

### GET with query parameters

```typescript
const response = await axios.get('/api/users', {
  params: {
    page: 1,
    per_page: 20,
    sort: 'last_name'
  }
})
```

Axios will build the URL for you, for example:

`/api/users?page=1&per_page=20&sort=last_name`

### POST JSON data

```typescript
const newUser = {
  user_name: 'jdoe',
  first_name: 'Jane',
  last_name: 'Doe'
}

const response = await axios.post('/api/users', newUser)
```

### PUT and DELETE

```typescript
await axios.put('/api/users/42', {
  first_name: 'Janet'
})

await axios.delete('/api/users/42')
```

## Axios and CSRF

When making state-changing requests (POST, PUT, DELETE), UserFrosting will automatically include the CSRF token to the request headers using Axios interceptors. This ensures that your requests are protected against cross-site request forgery attacks without needing extra configuration. Just use Axios as normal, and the CSRF token will be handled for you.

## Handling Errors Correctly

Axios rejects the Promise for non-2xx responses and network failures. To handle errors properly, you can use a then/catch/finally block or async/await with try/catch:

```typescript
import axios from 'axios'

return axios
    .get('/api/route')
    .then((response) => {
        // Handle successful response
    })
    .catch((err) => {
        // Handle error response
    })
    .finally(() => {
        // Handle cleanup (both success and error)
    })
```

The error (e.i. `err`) object contains useful information about what went wrong: 
- `err.response`: The server's response (if any)
- `err.request`: The request that was made (if no response received)
- `err.message`: A message describing the error
