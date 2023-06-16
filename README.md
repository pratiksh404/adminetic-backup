# Backup Panel Module for Adminetic Admin Panel

![Adminetic Backup Panel Module](https://github.com/pratiksh404/adminetic-backup/blob/main/screenshots/banner.jpg)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/adminetic/backup.svg?style=flat-square)](https://packagist.org/packages/adminetic/backup)

[![Stars](https://img.shields.io/github/stars/pratiksh404/adminetic-backup)](https://github.com/pratiksh404/adminetic-backup/stargazers) [![Downloads](https://img.shields.io/packagist/dt/adminetic/backup.svg?style=flat-square)](https://packagist.org/packages/adminetic/backup) [![StyleCI](https://github.styleci.io/repos/654030356/shield?branch=main)](https://github.styleci.io/repos/654030356?branch=main) [![License](https://img.shields.io/github/license/pratiksh404/adminetic-backup)](//packagist.org/packages/adminetic/backup)

Backup Panel module for Adminetic Admin Panel

For detailed documentaion visit [Adminetic Backup Panel Module Documentation](https://app.gitbook.com/@pratikdai404/s/adminetic/addons/backup)

#### Contains : -

- Backup Panel Panel

## Installation

##### Composer Install:

You can install the package via composer:

```bash
composer require adminetic/backup
```

##### Config Publish:

Publish spatie backup config

```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

##### Dropbox Configuration:

Create a dropbox account and extract `DROPBOX_APP_KEY`,`DROPBOX_APP_SECRET` and `DROPBOX_ACCESS_TOKEN` to `.env`

```bash
DROPBOX_APP_KEY=
DROPBOX_APP_SECRET=
DROPBOX_ACCESS_TOKEN=
```

##### Register Dropbox As Disk:

In `config/filesystems.php` add following code to `disks` array

```bash
   'dropbox' => [
            'driver' => 'dropbox',
            'key' => env('DROPBOX_APP_KEY'),
            'secret' => env('DROPBOX_APP_SECRET'),
            'authorization_token' => env('DROPBOX_ACCESS_TOKEN'),
        ],
```

##### Add dropbox disk to `config/backup.php`:

```html
  'destination' => [

            /*
             * The filename prefix used for the backup zip file.
             */
            'filename_prefix' => '',

            /*
             * The disk names on which the backups will be stored.
             */
            'disks' => [
                'local', 'dropbox'
            ],
        ],
```
```html
  'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['local', 'dropbox'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ]
```


### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email pratikdai404@gmail.com instead of using the issue tracker.

## Credits

- [Pratik Shrestha](https://github.com/adminetic)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Screenshots

![Backup Panel](https://github.com/pratiksh404/adminetic-backup/blob/main/screenshots/panel.jpg)

