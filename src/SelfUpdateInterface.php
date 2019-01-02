<?php

namespace HnhDigital\LaravelConsoleSelfUpdate;

interface SelfUpdateInterface
{
    /**
     * Checksum checking disabled.
     *
     * @var int
     */
    const CHECKSUM_DISABLED = 0;

    /**
     * Checksum checking against top level json encoded file.
     *
     * eg /checksums
     *
     * @var int
     */
    const CHECKSUM_TOP_LEVEL = 1;

    /**
     * Checksum checking against file at the binary download path.
     *
     * eg download/1.0.0/sha256
     *
     * @var int
     */
    const CHECKSUM_BINARY_LEVEL = 2;

    /**
     * Checksum sourced from the versions file.
     *
     * @var int
     */
    const CHECKSUM_VERSIONS = 3;
}
