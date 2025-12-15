---
title: Factories
metadata:
    obsolete: true
---

> [!NOTE]
> This page is a stub. To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

Model factories can be used to insert test data into the database. See [Laravel Documentation](https://laravel.com/docs/10.x/eloquent-factories#introduction) for more information. The only difference si the class your factory need to extend:

1. Create your factory, which must extend `UserFrosting\Sprinkle\Core\Database\Factories\Factory`;
2. You model need to use the `Illuminate\Database\Eloquent\Factories\HasFactory` trait;
3. Add `newFactory` method in your model, returning your factory:
   ```php 
    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
    ```
4. Use the factory : `$user = User::factory()->create();`