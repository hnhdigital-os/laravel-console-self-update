```
              .__   _____                          .___       __          
  ______ ____ |  |_/ ____\         __ ________   __| _/____ _/  |_  ____  
 /  ___// __ \|  |\   __\  ______ |  |  \____ \ / __ |\__  \\   __\/ __ \ 
 \___ \\  ___/|  |_|  |   /_____/ |  |  /  |_> > /_/ | / __ \|  | \  ___/ 
/____  >\___  >____/__|           |____/|   __/\____ |(____  /__|  \___  >
     \/     \/                          |__|        \/     \/          \/ 
```

Provides a trait to provide self-updating for Laravel Zero console applications.

[![Latest Stable Version](https://img.shields.io/github/release/hnhdigital-os/laravel-console-self-update.svg)](https://travis-ci.org/hnhdigital-os/laravel-console-self-update) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT) [![Donate to this project using Patreon](https://img.shields.io/badge/patreon-donate-yellow.svg)](https://patreon.com/RoccoHoward)

[![Build Status](https://travis-ci.com/hnhdigital-os/laravel-console-self-update.svg?branch=master)](https://travis-ci.com/hnhdigital-os/laravel-console-self-update) [![StyleCI](https://styleci.io/repos/163498842/shield?branch=master)](https://styleci.io/repos/163498842) [![Test Coverage](https://codeclimate.com/github/hnhdigital-os/laravel-console-self-update/badges/coverage.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-console-self-update/coverage) [![Issue Count](https://codeclimate.com/github/hnhdigital-os/laravel-console-self-update/badges/issue_count.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-console-self-update) [![Code Climate](https://codeclimate.com/github/hnhdigital-os/laravel-console-self-update/badges/gpa.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-console-self-update)


This package has been developed by H&H|Digital, an Australian botique developer. Visit us at hnh.digital.

## Requirements

* PHP 7.1.3
* Laravel Zero 5.7

## Installation

`composer require hnhdigital-os/laravel-console-self-update`

## Implementation

This package is implemented through a trait and an interface (for the constants).

The basic implementation requires setting a base URL `setUrl` or providing a flysystem adapter `setFlysystem` before calling the `runSelfUpdate` method.

Binary versioning is implemented using BRANCH-TAG (eg stable-1.00) but will fallback to TAG (1.0.0) for the more common Laravel Zero version approach.

```php
<?php

namespace App\Commands;

use HnhDigital\LaravelConsoleSelfUpdate\SelfUpdateInterface;
use HnhDigital\LaravelConsoleSelfUpdate\SelfUpdateTrait;
use LaravelZero\Framework\Commands\Command;

class SelfUpdateCommand extends Command implements SelfUpdateInterface
{
    use SelfUpdateTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'self-update
                            {--tag=0 : Set a specific tag to install}
                            {--check-version : Return the version of this current binary}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Self-update this binary';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setUrl('https://example.com');
        $this->runSelfUpdate();
    }
}
```

### Latest version

The script checks `/latest` for the latest tag (eg it would contain 1.0.1).

### Download path to binary

The download path for a specific binary version is sourced from a JSON encoded values sourced from `/versions` (default). You can override that by using `setVersionsPath`.

```json
{
    "1.0.0": "download/1.0.0/example-file",
    "1.0.1": "download/1.0.1/example-file"
}
```

By default, the versions file is tag/path. If the versions file contains more than the path, the default path source can be overridden to a specific key using `setVersionsTagKey`.

For example:

```json
{
    "1.0.0": {"path": "download/1.0.0/example-file"},
    "1.0.1": {"path": "download/1.0.1/example-file"}
}
```

```php
...
    public function handle()
    {
        ...
        $this->setVersionsTagKey('path');
        ...
    }
...
```

### Hash comparing

The downloaded file is hash checked (SHA256). This hash string by default is found in the same path as the download file path (download/1.0.1/sha256).

You can change the default source by overriding the `setHashSource` method and returning a different constant.

If there is a top level json encoded file storing the hashes, set the source to `CHECKSUM_TOP_LEVEL`. Specify the file path by using `setHashPath`.

NOTE: This array is keyed to the download path discovered through the versions file. It must match to be able to retrieve the hash.

```php
...
    public function handle()
    {
        ...
        $this->setHashSource(self::CHECKSUM_TOP_LEVEL);
        $this->setHashPath('checksums');
        ...
    }
...
```

```json
{
    "download/1.0.0/example-file": "...",
    "download/1.0.1/example-file": "..."
}
```

If the hash is included in the versions file, set the source to `CHECKSUM_VERSIONS`. Specify the array key using `setHashPath`.

```php
...
    public function handle()
    {
        ...
        $this->setHashSource(self::CHECKSUM_VERSIONS);
        $this->setHashPath('sha256');
        ...
    }
...
```

```json
{
    "1.0.0": {"path": "download/1.0.0/example-file", "sha256": "..."},
    "1.0.1": {"path": "download/1.0.1/example-file", "sha256": "..."}
}
```

## Build scripts

Looking for a build script to help create all the necessary files?

* [fs-watcher](https://github.com/hnhdigital-os/fs-watcher/blob/master/build.sh)
* [mysql-helper](https://github.com/hnhdigital-os/mysql-helper/blob/master/build.sh)

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-console-self-update/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/RoccoHoward)
* [All Contributors](https://github.com/hnhdigital-os/laravel-console-self-update/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-console-self-update/blob/master/LICENSE.md) for more information.
