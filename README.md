Echo Broadcasting Events Driver for Laravel
===============================================

Installation
--------------

Using Composer:

```sh
composer require pedrogaldino/laravel-echo-driver
```

In your config/app.php file add the following provider to your service providers array:

```php
'providers' => [
    ...
    PedroGaldino\EchoBroadcastDriver\BroadcastServiceProvider::class,
    ...
]
```

In your config/broadcasting.php file set the default driver to 'echo' and add the connection configuration like so:

```php
'default' => 'echo',

'connections' => [
    ...
    'echo' => [
            'driver' => 'echo',
            'connection' => env('ECHO_HOST','localhost')
        ]
    ...
]
```