SynologyServiceProvider
====================

This library provides the users authentication through the [Synology][1]'s security System.

Installation
------------

Use [Composer][2] to install the jmelosegui/synology-security-provider library by adding it to your `composer.json`.

```json
{
    "require": {
        "silex/silex": "~1.0",
        "symfony/form": "~2.3",
        "symfony/security": "~2.3",
        "jmelosegui/synology-security-provider": "~0.1"
    }
}
```

Usage
-----

First, register the service provider passing the synology server name or ip address and port:

```php
$app->register(new SynologyServiceProvider(), array(
    'synologyServerName' => 'localhost',
    'synologyServerPort' => '5000',
));
```
Since the code will run inside the NAS you can use localhost as the `serverName`.
If you need to debug on your dev pc change localhost to the Synology server Ip address.

Next, register the `synology` security provider in your firewall.

```php
// Provides URL generation
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Provides CSRF token generation
$app->register(new Silex\Provider\FormServiceProvider());

// Provides session storage
$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new SecurityServiceProvider($app), array(
    'security.firewalls' => array(
        'login' => array(
            'pattern' => '^/login$',
        ),
        'synology' => array(
            'pattern' => '^.*$',
            'synology' => true,
            'form' => array('login_path' => '/login', 'check_path' => '/dologin', 'use_referer' => true),
            'logout' => array('logout_path' => '/logout'),
        )
    )
));
```

License
-------

Released under the MIT license. See the [LICENSE][3] file for details.

[1]: http://www.synology.com/
[2]: http://getcomposer.org
[3]: https://github.com/jmelosegui/synology-security-provider/blob/master/LICENSE
