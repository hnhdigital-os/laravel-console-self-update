<?php

namespace HnhDigital\LaravelConsoleSelfUpdate\Tests;

use HnhDigital\LaravelConsoleSelfUpdate\SelfUpdateInterface;
use HnhDigital\LaravelConsoleSelfUpdate\SelfUpdateTrait;

class MockCommand implements SelfUpdateInterface
{
    use SelfUpdateTrait;

    public function error($message)
    {
        throw new \Exception($message);
    }

    /**
     * Get unique URL.
     *
     * @param string $path
     *
     * @return string
     */
    public function getUniqueUrl($path)
    {
        return $path;
    }

    public function setVersionData($data)
    {
        $this->version_data = $data;
    }
}
