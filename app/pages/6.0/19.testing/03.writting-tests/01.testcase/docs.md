---
title: Sprinkle Test Case
metadata:
    obsolete: true
---

To make it easier to run your tests in your Sprinkle environment, that is with every routes, middlewares and other class registered in your Recipe, UserFrosting provides a base TestCase you can use. You simply need to tell the TestCase to use your Recipe. It will create a simple UserFrosting app instance, and cleanly destroy it when the test is done. It also provides some additional helper methods.

> [!NOTE]
> Of course, the use of this test case is purely optional. You can still use the default PHPUnit test case. However, you won't have access to other services, such as the database. However, the tests will be faster if you're using the default PHPUnit test case, so it's a good idea to use it when Mocking services for example.

## Integrating with your Sprinkle

To begin testing your Sprinkle, your test case simply need to extends `UserFrosting\Testing\TestCase`. Then, define your Sprinkle Recipe in the `$mainSprinkle` property. For example:

```php
<?php

namespace App\MySite\Tests;

use App\MySite\MySite;
use UserFrosting\Testing\TestCase;

/**
 * Test case with MySite as main sprinkle
 */
class MyTest extends TestCase
{
    protected string $mainSprinkle = MySite::class;

    // Your test goes here...
}
```

You could also use the code above as a new test case, instead of defining `$mainSprinkle` in every tests. Instead of naming the class `MyTest`, name it `MyTestCase` and make every test class extend `MyTestCase`. 

The biggest advantage is you don't *need* to use your Recipe. Alternatively, you can create a recipe stub. Simply create a *second* recipe in your testing directory. This other recipe can register only the class you want to test.

## Helping methods & properties

When extending `UserFrosting\Testing\TestCase`, you have access to many helper methods and properties. 

### Properties 
| Property              | Description                    |
| --------------------- | ------------------------------ |
| `$this->ci`           | Dependency Injection Container |
| `$this->app`          | The Slim App Instance          |
| `$this->userfrosting` | The UserFrosting App Instance  |
| `$this->mainSprinkle` | The main sprinkle identifier   |

> [!NOTE]
> The default PHPUnit `setUp` method will create the application, while `tearDown` will delete the application. All properties needs to be access after invoking the parent method.

### createRequest

This methods can be used to create a basic `ServerRequestInterface`.

```php
$this->createRequest( 
    string $method, // The HTTP method : GET, POST, PUT, DELETE, etc.
    string|UriInterface $uri, // The URI
    array $serverParams = [] // The POST data
): ServerRequestInterface
```

**Example**
```php
$request = $this->createRequest('GET', '/index');
$request = $this->createRequest('POST', '/create/foo', ['name' => 'bar']);
$request = $this->createRequest('DELETE', '/api/foo/12');
```

### createJsonRequest

Same as `createRequest`, but for JSON request.

```php
createJsonRequest(
    string $method, // The HTTP method : GET, POST, PUT, DELETE, etc.
    string|UriInterface $uri, // The URI
    ?array $data = null // The POST data
): ServerRequestInterface
```

### handleRequest

Pass the request to the Slim App for handling by the routes and controllers.
```php
handleRequest(ServerRequestInterface $request): ResponseInterface
```

**Example**
```php
$response = $this->handleRequest($request);
```

### assertResponse
Verify that the given string is an exact match for the body returned. Use `assertSame` under the hood.

```php
assertResponse(string $expected, ResponseInterface $response)
```

**Example**
```php
$this->assertResponse('fr_FR', $response);

// Same as 

$this->assertSame('fr_FR', (string) $response->getBody());
```

### assertResponseStatus
Verify that the given response has the expected status code.

```php
assertResponseStatus(int $expected, ResponseInterface $response)
```

**Example**
```php
$this->assertResponseStatus(400, $response);

// Same as 

$this->assertSame(400, $response->getStatusCode());
```

### assertJsonResponse
Verify that the given array is an exact match for the JSON returned. Optionally, the key argument can be used to isolate a key from the json array (Support dot notation).

```php
assertJsonResponse(mixed $expected, ResponseInterface $response, ?string $key = null)
```

**Example**
```php
$this->assertJsonResponse(['foo' => 'bar'], $response);
$this->assertJsonResponse('bar', $response), 'foo';

// Same as 

$this->assertJsonEquals(['foo' => 'bar'], (string) $response->getBody());
$this->assertJsonEquals('bar', (string) $response->getBody(), 'foo');
```

### assertNotJsonResponse
Reverse of `assertJsonResponse`

```php
assertNotJsonResponse(mixed $expected, ResponseInterface $response, ?string $key = null)
```

### assertJsonEquals
Asserts json string is valid json and is equals to an array. Optionally, the key argument can be used to isolate a key from the json array (Support dot notation).

```php
assertJsonEquals(mixed $expected, string|ResponseInterface $json, ?string $key = null)
```

**Example**
```php
$array = ['result' => ['foo' => true, 'bar' => false, 'list' => ['foo', 'bar']]];
$json = '{"result": {"foo":true,"bar":false,"list":["foo","bar"]}}';
$this->assertJsonEquals($array, $json); // true
```

### assertJsonNotEquals
Reverse of `assertJsonEquals`.

```php
assertJsonNotEquals(mixed $expected, string|ResponseInterface $json, ?string $key = null)
```

### assertJsonStructure
Asserts Json string or response equals the expected structure. Optionally, the key argument can be used to isolate a key from the json array (Support dot notation).
```php
assertJsonStructure(array $expected, string|ResponseInterface $json, ?string $key = null)
```

**Example**
```php
$json = '{"result": {"foo":true,"bar":false,"list":["foo","bar"]}}';
$this->assertJsonStructure(['result'], $json);
$this->assertJsonStructure(['foo', 'bar', 'list'], $json, 'result');
```

### assertJsonCount
Asserts the json has the expected count of items at the given key.

```php
assertJsonCount(int $expected, string|ResponseInterface $json, ?string $key = null)
```

**Example**
```php
$json = '{"result": {"foo":true,"bar":false,"list":["foo","bar"]}}';
$this->assertJsonCount(1, $json);
$this->assertJsonCount(3, $json, 'result');
$this->assertJsonCount(2, $json, 'result.list');
```

### assertHtmlTagCount
Asserts the number of time the $tag is found in $html.

```php
assertHtmlTagCount(int $expected, string|ResponseInterface $html, string $tag)
```

**Example**
```php
$html = '<html><div>One</div><div>Two</div><span>Not You</span><div>Three</div></html>';
$this->assertHtmlTagCount(3, $html, 'div');
$this->assertHtmlTagCount(1, $html, 'html');
$this->assertHtmlTagCount(1, $html, 'span');
$this->assertHtmlTagCount(0, $html, 'p');
```

## Testing HTTP routes

To methods above can be used to test routes endpoints. The basic concept is to use `createRequest` or `createJsonRequest` to create the `ServerRequestInterface`. Then pass this request to `handleRequest` which will invoked the correct route and return the response from the controller. Finally use custom assertions to make sure the response contains the right content.

For example : 

### HTML Routes
```php
$request = $this->createRequest('GET', '/index');
$response = $this->handleRequest($request);

$this->assertResponseStatus(200, $response);
$body = (string) $response->getBody();

// ... Assert Body content
```

### Json Routes

**Get:**
```php
$request = $this->createJsonRequest('GET', '/api/foo');
$response = $this->handleRequest($request);

// Assert response status & body
$this->assertResponseStatus(200, $response);
$this->assertJsonCount(1, $response);
$this->assertJsonStructure(['bar'], $response);
$this->assertJsonResponse(['bar' => true], $response);
$this->assertJsonResponse(true, $response, 'bar'); // Equivalent to previous line
$this->assertJsonNotEquals(['bar' => false], $response);
```

**Post:**
```php
$request = $this->createJsonRequest('POST', '/foo/create', ['name' => 'bar']);
$response = $this->handleRequest($request);

$this->assertResponseStatus(200, $response);
$this->assertJsonResponse([], $response);

// ... Assert "foo" was created
```
