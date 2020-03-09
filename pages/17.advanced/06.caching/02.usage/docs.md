---
title: The Cache Service
description:
    metadata: UserFrosting provides a convenient global caching service for your application, as well as user-specific caches for user data.
taxonomy:
    category: docs
---

## The Cache service

The UserFrosting cache service instantiates the [Laravel Cache](https://laravel.com/docs/5.8/cache) component for global caching. The [Laravel documentation](https://laravel.com/docs/5.8/cache#cache-usage) provides excellent examples on how to use the caching API. The only difference is that UserFrosting provides the required setup for you. Plus, instead of using a `Cache` facade, you simply use the **cache service** from the [The DI Container](/services/the-di-container):

```php
$value = $this->ci->cache->get('users', function () {
    return Users::get();
});
```

Every method documented for the Laravel Cache is accessible inside the cache service: `get`, `has`, `increment`, `decrement`, `remember`, `pull`, `put`, `forever`, `rememberForever`, `tags`, etc.

## User cache

While the cache service provides caching for the entire website, that is when the data is shared across all users, UserFrosting also provides a handy way to cache data specifically to each user. Simply use the `getCache()` method of the user object to get a cache instance tied to that user :

```php
$userCache = User::find(1)->getCache();
$userCache->forever('key', 'Foo');
$userCache->get('key'); // Return 'Foo'

$userCache = User::find(2)->getCache();
$userCache->forever('key', 'Bar');
$userCache->get('key'); // Return 'Bar'
```

## Clearing the cache

While the `forget` and `flush` methods will work to flush the entire application cache, you can also use the `clear-cache` command from the [Bakery CLI](/cli/commands#clearcache):

```bash
$ php bakery clear-cache
```

## Cache drivers

UserFrosting provide access and configuration out of the box for 3 cache drivers: `File`, `Memcached` and `Redis`.

The driver used by UserFrosting can be defined in the configuration files under the `cache.driver` key. To change drivers, simply overwrite this key in your sprinkle for one of the drivers below.

>>>> In a production environment, Memcached or Redis should be used for better performance.

### File

The file driver is the one enabled by default. Cached data is stored in text files located in `app/cache/`. While slower and less efficient than memory based drivers, the file driver is ideal for a development environment.

>>>>> The default Laravel File Driver doesn't support `tags`. UserFrosing uses a custom version of this driver that enabled tagging. Still, Memecached and Redis are more optimized for this and should be used in a production environment for better performance.

### Memcached

In contrast with the file driver, the **Memcached** driver stores the cached data in memory, allowing for better performance. Using the Memcached driver requires the [Memcached PECL package](https://pecl.php.net/package/memcached) to be installed. Configuration for this driver can be found in the configuration files, under the `cache.memcached` key:

```php
'cache' => [
    'memcached' => [
        'host' => '127.0.0.1',
        'port' => 11211,
        'weight' => 100
    ]
]
```

>>>> **Memcached** shoudn't be confused with **memcache**. Those are two differents clients !

### Redis

Similar to Memcached, **Redis** uses in-memory data structure to store the cache data. This, of course, requires you to have [Redis](https://redis.io) already installed on the server. Configuration for this driver can be found in the configuration files, under the `cache.redis` key:

```php
'cache' => [
    "redis" => [
        'host' => '127.0.0.1',
        'password' => null,
        'port' => 6379,
        'database' => 0
    ]
]
```

>>>>>> When using Redis with multiple applications on the same server, you can use the `database` option to assign one of 16 default Redis databases (identified by a number form 0 to 15) to your UserFrosting instance and avoid sharing the same database with multiple apps.

## Prefix configuration

When using multiple instances of UserFrosting with the **Memcached** or **Redis** driver on the same server, or any other app using Redis/Memcached, you should edit the `config.prefix` configuration value so each installation uses a unique prefix. Otherwise, both installations of UserFrosting might end up sharing the same cached data. Note that this does not affect the **file** driver.

>>>>  When using the **Redis** or **Memcached** drivers, flushing the cache does not respect the cache prefix and will remove all entries from the cache. That means all data stored in the Redis/Memcached database will be deleted, whether or not it belong to your application. Consider this carefully when clearing a cache which is shared by other applications. When using the Redis driver, [different database can be used for each app](https://stackoverflow.com/a/38272337/445757) to avoid this.
