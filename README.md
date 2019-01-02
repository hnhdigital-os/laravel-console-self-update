```
              .__   _____                          .___       __          
  ______ ____ |  |_/ ____\         __ ________   __| _/____ _/  |_  ____  
 /  ___// __ \|  |\   __\  ______ |  |  \____ \ / __ |\__  \\   __\/ __ \ 
 \___ \\  ___/|  |_|  |   /_____/ |  |  /  |_> > /_/ | / __ \|  | \  ___/ 
/____  >\___  >____/__|           |____/|   __/\____ |(____  /__|  \___  >
     \/     \/                          |__|        \/     \/          \/ 
```

Provides a trait to provide self-updating for Laravel Zero console applications.

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at hnh.digital.

## Requirements

* PHP 7.1.3
* Laravel Zero 5.7

## Installation

`composer require hnhdigital-os/laravel-console-self-update`

## Implementation

This package is implemented through a trait.

Basic setup requires setting a base URL (setUrl) or providing a flysystem adapter (setFlysystem) before calling the `runSelfUpdate` method.

Binary version is implemented as BRANCH-TAG (eg stable-1.00) but will fallback to TAG (1.0.0).

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

The download path for a specific binary version is sourced from a JSON encoded values sourced from `/versions`.

```json
{
    "1.0.0": "download/1.0.0/example-file",
    "1.0.1": "download/1.0.1/example-file"
}
```

By default, the versions file is tag/path. If the versions file contains more than the path, the default path source can be overridden to a specific key using `getVersionsTagKey`.

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

The downloaded file is hash checked (SHA256). This hash string buy default is found in the same path as the download file path (download/1.0.1/sha256).

You can change the default source by overriding the `compareHash` method and returning a different constant.

Top level json encoded file: (CHECKSUM_TOP_LEVEL);

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

Hash included in the versions file (CHECKSUM_VERSIONS);

```php
...
    public function handle()
    {
        ...
        $this->setHashSource(self::CHECKSUM_VERSIONS);
        $this->setHashPath('checksums');
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

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-console-self-update/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/RoccoHoward)
* [All Contributors](https://github.com/hnhdigital-os/laravel-console-self-update/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-console-self-update/blob/master/LICENSE.md) for more information.
