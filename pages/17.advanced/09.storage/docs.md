---
title: File Storage
taxonomy:
    category: docs
---

The filesystem service provides access to locally and remotely stored files using the [Flysystem](https://github.com/thephpleague/flysystem) PHP package by Frank de Jonge. Based on [Laravel Flysystem integration](https://laravel.com/docs/8.x/filesystem), UserFrosting integration provides simple to use drivers for working with local filesystems, Amazon S3, and Rackspace Cloud Storage. Even better, it's amazingly simple to switch between these storage options as the API remains the same for each system. You can even [add your own adapter] in your sprinkle if you need access to the many adapter supported by Flysystem.

## Disks Configuration

UserFrosting uses **disk** to define storage location. A disk can represent a location where files can be found and saved. Each disk uses a particular storage driver that contains the necessary code to access your file using a standardized API. UserFrosting provides built-in configuration for three disks: `local`, `public` and `s3`.

### Provided disks
#### The _local_ disk
The **local** disk stores files in `app/storage`. This is also the **default disk**. Those files are not publicly available which means you can't access any files located in the local disk by typing the correct URL in your browser. It's perfect for storing any kind of files you want to control. You can still access them from your controllers classes and methods and return them manually if you require so.

#### The _public_ disk
The **public** disk, on the other hand, store files directly in your project public folder, under `public/files/`. This means any files saved in this disk will be **publicly available**. It's the perfect disk for user generated assets (think images), as they will be directly handled by the web server. For example, if you store a file named `cats.jpg` in the public disk, you'll be able to access this image by typing `https://localhost/files/cats.jpg` in your browser.

[notice=note]The package required to use S3 disk is not included with a default install. You must include `league/flysystem-aws-s3-v3` inside a custom Sprinkles `composer.json`.[/notice]

#### The _S3_ disk
The **S3** disk provides an example configuration to access an [Amazon S3](https://aws.amazon.com/s3/) bucket from your app. Because of the sensitive information, we recommend storing your S3 credential in the `app/.env` file. This will avoid committing your private keys to your git repo, for example.

Simply add the necessary entries to your `app/.env` file if they don't already exist :

```bash
AWS_ACCESS_KEY_ID=""
AWS_SECRET_ACCESS_KEY=""
AWS_DEFAULT_REGION=""
AWS_BUCKET=""
AWS_URL=""
```

See [Amazon S3 Support Page](https://aws.amazon.com/en/blogs/security/wheres-my-secret-access-key/) if you need help finding your access keys. The region code can be found [here](http://docs.aws.amazon.com/general/latest/gr/rande.html).

[notice=note]The package required to use rackspace disk is not included with a default install. You must include `league/flysystem-rackspace` inside a custom Sprinkles `composer.json`.[/notice]

#### The _rackspace_ disk
The **rackspace** disk provides an example configuration to access [rackspace](https://www.rackspace.com) storage solution. Because of the sensitive information, we recommend storing your rackspace credential in the `app/.env` file.

```bash
RACKSPACE_USERNAME=""
RACKSPACE_KEY=""
RACKSPACE_CONTAINER=""
RACKSPACE_ENDPOINT=""
RACKSPACE_REGION=""
RACKSPACE_URL_TYPE=""
```

### Adding your own disk

Of course, you may configure as many disks as you like, and may even have multiple disks that use the same driver.

[notice=warning]Since every sprinkles can access the filesystem, we recommend you create a sprinkle specific disk if you want to avoid another sprinkle from accidentally overwriting a file managed by your sprinkle.[/notice]

To define a new disk, simply add the necessary configuration to your sprinkle configuration file :

```php
'filesystems' => [
    'disks' => [
        'mySite' => [
            'driver' => 'local',
            'root' => \UserFrosting\STORAGE_DIR . "/_mySite"
        ]
    ]
],
```

The `mySite` disk will point to the `app/storage/_mySite` directory.

Note that you can also overwrite a default disk configuration values in your sprinkle the same way you do with other configuration values.

[notice=tip]To change the default disk used by UserFrosting, you can also overwrite the `filesystems.default` configuration.[/notice]

The following drivers have built-in support in UserFrosting :
 - local
 - s3
 - rackspace
 - ftp
 - sftp

## Using the filesystem service

### Obtaining Disk Instances

The filesystem service may be used to interact with any of your configured disks. For example, you may use the put method on the facade to store an avatar on the default disk. If you call methods on the Storage facade without first calling the disk method, the method call will automatically be passed to the default disk:

```php
$this->ci->filesystem->put('avatars/1', $fileContents);
```

If your applications interact with multiple disks, you may use the disk method on the Storage facade to work with files on a particular disk:

```php
$this->ci->filesystem->disk('s3')->put('avatars/1', $fileContents);
```


The Storage facade may also be used to interact with any of your configured disks the same way the filesystem service is :

```php
use UserFrosting\Sprinkle\Core\Facades\Storage;

Storage::put('avatars/1', $fileContents);
```

You may also use the disk method on the Storage facade to work with files on a particular disk:

```php
Storage::disk('s3')->put('avatars/1', $fileContents);
```

### Retrieving Files

The get method may be used to retrieve the contents of a file. The raw string contents of the file will be returned by the method. Remember, all file paths should be specified relative to the "root" location configured for the disk:

```php
$contents = $this->ci->filesystem->get('file.jpg');
```

The exists method may be used to determine if a file exists on the disk:

```php
$exists = $this->ci->filesystem->disk('s3')->exists('file.jpg');
```

### Storing Files

The `put` method may be used to store raw file contents on a disk. You may also pass a PHP `resource` to the `put` method, which will use Flysystem's underlying stream support. Using streams is greatly recommended when dealing with large files:

```php
$this->ci->filesystem->put('file.jpg', $contents);

$this->ci->filesystem->put('file.jpg', $resource);
```

### Deleting Files

The delete method accepts a single filename or an array of files to remove from the disk:

```php
$this->ci->filesystem->delete('file.jpg');

$this->ci->filesystem->delete(['file1.jpg', 'file2.jpg']);
```

### Going Further

Since UserFrosting relies on Laravel implementation, see [Laravel Documentation](https://laravel.com/docs/8.x/filesystem) for more info on how to use the **filesystem** service.

## Custom driver setup

UserFrosting's Flysystem integration provides drivers for several "drivers" out of the box; however, Flysystem is not limited to these and has adapters for many other storage systems. You can create a custom driver if you want to use one of these additional adapters in your UserFrosting application.

In order to set up the custom filesystem you will need a Flysystem adapter. Let's add a community maintained Google Drive adapter to your sprinkle `composer.json` :

```json
"nao-pon/flysystem-google-drive": "~1.1"
```

Next, you should [extend the `filesystem` service](/services/extending-services#extending-existing-services) in your sprinkle. There you can use the filesystem service `extend` method to define the custom driver:

```php
    $container->extend('filesystem', function ($filesystem, $c) {
        $filesystem->extend('gdrive', function ($config, $diskConfig) {

            $client = new \Google_Client();
            $client->setClientId($diskConfig['clientID']);
            $client->setClientSecret($diskConfig['clientSecret']);
            $client->refreshToken($diskConfig['refreshToken']);

            $driveRoot = $diskConfig['rootPath'] ?: '';

            $service = new \Google_Service_Drive($client);
            $adapter = new \Hypweb\Flysystem\GoogleDrive\GoogleDriveAdapter($service, $driveRoot);

            return new \League\Flysystem\Filesystem($adapter);
        });

        return $filesystem;
    });
```

The first argument of the `extend` method is the name of the driver and the second is a Closure that receives the config service (`$config`) and the disk configuration array (`$diskConfig`). The resolver Closure must return an instance of `League\Flysystem\Filesystem`.

Last thing to do is to create a disk using the `gdrive` driver in your sprinkle configuration file :

```php
    'google' => [
        'driver' => 'gdrive', // For help finding the client ID : https://developers.google.com/drive/api/v3/enable-sdk
        'clientID' => getenv('GOOGLE_CLIENT_ID') ?: '', // [app client id].apps.googleusercontent.com
        'clientSecret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'refreshToken' => getenv('GOOGLE_REFRESH_TOKEN') ?: '',
        'rootPath' => getenv('GOOGLE_ROOT_PATH') ?: ''
    ]

```

[notice=tip]As with the S3 and rackspace Drivers, it's recommended to store your tokens and keys in the `app/.env` file.[/notice]
