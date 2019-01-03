<?php

namespace HnhDigital\LaravelConsoleSelfUpdate;

/*
 * This file is part of Laravel Console Self Update package.
 *
 * (c) H&H Digital
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use League\Flysystem\Filesystem;

/**
 * This is a Laravel Console Self Update trait.
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */
trait SelfUpdateTrait
{
    /**
     * @var string
     */
    private $current_version;

    /**
     * @var string
     */
    private $current_release;

    /**
     * @var string
     */
    private $current_tag;

    /**
     * @var string
     */
    private $latest_tag;

    /**
     * @var string
     */
    private $latest_tag_path = 'latest';

    /**
     * @var string
     */
    private $versions_path = 'versions';

    /**
     * @var bool|string
     */
    private $versions_tag_key = false;

    /**
     * Use public url to self-update.
     *
     * @var bool
     */
    private $use_flysystem = false;

    /**
     * Filesystem adapter.
     *
     * @var Filesystem
     */
    private $flysystem;

    /**
     * @var string
     */
    private $url = '';

    /**
     * Hash source.
     *
     * @var string
     */
    private $hash_source = 0;

    /**
     * Hash file path.
     *
     * @var string
     */
    private $hash_path = 'sha256';

    /**
     * Hash algo to use.
     *
     * @var string
     */
    private $hash_algo = 'sha256';

    /**
     * @var array
     */
    private $version_data;

    /**
     * Set URL.
     *
     * @param string $url
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->use_flysystem = false;
    }

    /**
     * Get URL.
     *
     * @param string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set the flysystem.
     *
     * @param Filesystem $adapter
     *
     * @return void
     */
    protected function setFlysystem(Filesystem $flysystem)
    {
        $this->flysystem = $flysystem;
        $this->use_flysystem = true;
    }

    /**
     * Get flysystem.
     *
     * @param Filesystem
     */
    protected function getFlysystem()
    {
        return $this->flysystem;
    }

    /**
     * Set the current version.
     *
     * @param string $current_tag
     *
     * @return void
     */
    public function setCurrentVersion($current_version)
    {
        $this->current_version = $current_version;
    }

    /**
     * Get the current release.
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return $this->current_version;
    }

    /**
     * Set the current release.
     *
     * @param string $current_tag
     *
     * @return void
     */
    public function setCurrentRelease($current_release)
    {
        $this->current_release = $current_release;
    }

    /**
     * Get the current release.
     *
     * @return string
     */
    public function getCurrentRelease()
    {
        return $this->current_release;
    }

    /**
     * Get the current tag.
     *
     * @return string
     */
    public function getCurrentTag()
    {
        return $this->current_tag;
    }

    /**
     * Set the current tag.
     *
     * @param string $current_tag
     *
     * @return void
     */
    public function setCurrentTag($current_tag)
    {
        $this->current_tag = $current_tag;
    }

    /**
     * Get the current tag.
     *
     * @return string
     */
    public function getLatestTag()
    {
        return $this->latest_tag;
    }

    /**
     * Set the latest tag.
     *
     * @param string $latest_tag
     *
     * @return void
     */
    public function setLatestTag($latest_tag)
    {
        $this->latest_tag = $latest_tag;
    }

    /**
     * Get the path to the file referencing the latest tag.
     *
     * @return string
     */
    public function getLatestTagPath()
    {
        return $this->latest_tag_path;
    }

    /**
     * Set the path to the file referencing the latest tag.
     *
     * @return string
     */
    public function setLatestTagPath($path)
    {
        $this->latest_tag_path = $path;
    }

    /**
     * Get the path to the version file.
     *
     * @return string
     */
    public function getVersionsPath()
    {
        return $this->versions_path;
    }

    /**
     * Set the path to the version file.
     *
     * @return string
     */
    public function setVersionsPath($versions_path)
    {
        $this->versions_path = $versions_path;
    }

    /**
     * Get temp path.
     *
     * @param string $path
     * @param string $tag
     *
     * @return string
     */
    public function getTempPath($path, $tag)
    {
        return sprintf('/tmp/%s.%s', basename($path), $tag);
    }

    /**
     * Get binary path.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getCurrentBinaryFilePath()
    {
        return realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
    }

    /**
     * Get backup path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getBackupPath($path)
    {
        return $path.'.'.$this->getCurrentTag();
    }

    /**
     * Overridden default. Get the key for the versions download path.
     *
     * @return bool|string
     */
    public function setVersionsTagKey($versions_tag_key)
    {
        $this->versions_tag_key = $versions_tag_key;
    }

    /**
     * Overridden default. Get the key for the versions download path.
     *
     * @return bool|string
     */
    public function getVersionsTagKey()
    {
        return $this->versions_tag_key;
    }

    /**
     * Get the hash source.
     *
     * @return string|bool
     */
    public function getHashSource()
    {
        return $this->hash_source;
    }

    /**
     * Set the hash source.
     *
     * @return string
     */
    public function setHashSource($hash_source)
    {
        $this->hash_source = $hash_source;
    }

    /**
     * Get the path to the hash file.
     *
     * @return string
     */
    public function getHashPath()
    {
        return $this->hash_path;
    }

    /**
     * Get the path to the hash file.
     *
     * @return string
     */
    public function setHashPath($hash_path)
    {
        $this->hash_path = $hash_path;
    }

    /**
     * Check hash of downloaded file.
     *
     * @return string
     */
    public function getHashAlgo()
    {
        return $this->hash_algo;
    }

    /**
     * Check hash of downloaded file.
     *
     * @return string
     */
    public function setHashAlgo($hash_algo)
    {
        $this->hash_algo = $hash_algo;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function runSelfUpdate()
    {
        // Set version, release, and tag.
        $this->setCurrentVersion(config('app.version'));

        list($release, $tag) = $this->parseVersion($this->getCurrentVersion());

        $this->setCurrentRelease($release);
        $this->setCurrentTag($tag);

        if (!empty($this->option('check-version'))) {
            $this->line($release.'-'.$this->getCurrentTag());

            return;
        }

        // Check for latest version.
        if (!$this->checkVersion()) {
            $this->info('You are running the latest version!');

            return;
        }

        return $this->processUpdate();
    }

    /**
     * Parse the version.
     *
     * @param string $version
     *
     * @return void
     */
    public function parseVersion($version)
    {
        $parsed_version = explode('-', $version, 2);

        if (count($parsed_version) == 1) {
            $release = 'stable';
            $tag = $parsed_version[0];
        } elseif (count($parsed_version) == 2) {
            $release = $parsed_version[0];
            $tag = $parsed_version[1];
        }

        return [
            $release,
            $tag,
        ];
    }

    /**
     * Check if there is a new version available.
     *
     * @return bool
     */
    protected function checkVersion()
    {
        if (!$this->use_flysystem) {
            $this->line('Source: <info>'.$this->getUrl().'</info>');
        }

        // Tag to install has been specified.
        if (!empty($this->option('tag'))) {
            $this->setLatestTag($this->getCurrentRelease() === 'stable' ? $this->option('tag') : '');

            return true;
        }

        // Get latest tag.
        if (($latest_tag = $this->readLatestTag()) === false) {
            return false;
        }

        $this->setLatestTag($latest_tag);

        return $this->getCurrentTag() !== $this->getLatestTag();
    }

    /**
     * Read the latest tag.
     *
     * @return string
     */
    public function readLatestTag()
    {
        if (($latest_tag = $this->readFile($this->getLatestTagPath())) === false) {
            return false;
        }

        return trim($latest_tag);
    }

    /**
     * Process update.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function processUpdate()
    {
        $current_binary_path = $this->getCurrentBinaryFilePath();
        $temp_binary_path = $this->getTempPath($current_binary_path, $this->getLatestTag());

        // Get the download path for the updated binary.
        if (($download_path = $this->readDownloadPath($this->getLatestTag())) === false) {
            $this->error('Could not get path to download.');

            return 1;
        }

        $this->line(sprintf('Installing %s update...', $this->getLatestTag()));

        // Save the updated binary to temp disk.
        $file_contents = $this->downloadUpdatedBinary($download_path);

        if (empty($file_contents)) {
            $this->error('Failed to download file.');

            return 1;
        }

        file_put_contents($temp_binary_path, $file_contents);

        // Match the file permissions to current binary.
        chmod($temp_binary_path, fileperms($current_binary_path));

        // Validate the binary.
        // Test that the binary "works" and returns the version we are expecting.
        if (!$this->validateBinary($temp_binary_path)) {
            $this->error('Could not validate updated binary.');
            unlink($temp_binary_path);

            return 1;
        }

        // Backup the current binary.
        if (($error_code = $this->backupCurrentBinary($current_binary_path)) > 0) {
            unlink($temp_binary_path);

            return $error_code;
        }

        // Only apply when compiled.
        if (config('app.production', false)) {
            // Replace with the new binary.
            rename($temp_binary_path, $current_binary_path);
        }

        $this->line(sprintf(
            'You are now running the latest version: <info>%s-%s</info>',
            $this->getCurrentRelease(),
            $this->getLatestTag()
        ));

        // Force exit.
        exit(0);
    }

    /**
     * Backup binary.
     *
     * @param string $path
     *
     * @return void
     */
    public function backupCurrentBinary($path)
    {
        // Current file path is not writable.
        if (!is_writable($path)) {
            $this->error('Can not self-update - not writable.');

            return 1;
        }

        // Current path parent folder is not writable.
        if (!is_writable(dirname($path))) {
            throw new \Exception('');
            $this->error('Can not write to parent path to backup.');

            return 1;
        }

        if (!config('app.production', false)) {
            return;
        }

        // Move current binary to backup path.
        rename($path, $this->getBackupPath($path));
    }

    /**
     * Read the download path for the given tag.
     *
     * @param string $tag
     *
     * @return string
     */
    public function readDownloadPath($tag)
    {
        if (($version_data = $this->readJson($this->getVersionsPath())) === false) {
            return false;
        }

        $this->version_data = $version_data;

        // Tag not found.
        if (!isset($this->version_data[$tag])) {
            return false;
        }

        // Default format is "1.0.0": "download/1.0.0/binary-example"
        if ($this->getVersionsTagKey() === false) {
            // Check before we trim.
            if (!is_string($this->version_data[$tag])) {
                return false;
            }

            return ltrim($this->version_data[$tag], '/');
        }

        if (!isset($this->version_data[$tag])
            || !isset($this->version_data[$tag][$this->getVersionsTagKey()])) {
            return false;
        }

        // Check before we trim.
        if (!is_string($this->version_data[$tag][$this->getVersionsTagKey()])) {
            return false;
        }

        return ltrim($this->version_data[$tag][$this->getVersionsTagKey()], '/');
    }

    /**
     * Download updated binary.
     *
     * @param string $path
     *
     * @return void
     */
    private function downloadUpdatedBinary($path)
    {
        $file_contents = $this->readFile($path);

        if (mb_strlen($file_contents) === 0) {
            $this->error('Downloaded file is empty.');

            return false;
        }

        // Hash check failed.
        if (!$this->compareHash($path, $file_contents)) {
            $this->error('Hash mismatch.');

            return false;
        }

        return $file_contents;
    }

    /**
     * Check hash for path/contents.
     *
     * @param string $path
     * @param string $file_contents
     *
     * @return bool
     */
    public function compareHash($path, $file_contents)
    {
        // Check if hash needs comparing.
        if ($this->getHashSource() === self::CHECKSUM_DISABLED) {
            return true;
        }

        // File contents hash.
        $current_hash = $this->getHashFromString($file_contents);

        // Provided hash.
        $provided_hash = $this->readUpdatedBinaryHash($path);

        // Compare hashes.
        return $current_hash === $provided_hash;
    }

    /**
     * Hash of string.
     *
     * @return string
     */
    public function getHashFromString($file_contents)
    {
        return hash($this->getHashAlgo(), $file_contents);
    }

    /**
     * Get the hash for the updated binary.
     *
     * @param string $path
     *
     * @return string
     */
    private function readUpdatedBinaryHash($path)
    {
        // Top level json encoded file.
        if ($this->getHashSource() === self::CHECKSUM_TOP_LEVEL) {
            if (($checksums = $this->readJson($this->getHashPath())) === false) {
                return false;
            }

            if (!isset($checksums[$path])) {
                return false;
            }

            return $checksums[$path];
        }

        // Hash found in versions file.
        if ($this->getHashSource() === self::CHECKSUM_VERSIONS) {
            if (!isset($this->version_data[$this->getLatestTag()])
                || !isset($this->version_data[$this->getLatestTag()][$this->getHashPath()])) {
                return false;
            }

            return $this->version_data[$this->getLatestTag()][$this->getHashPath()];
        }

        // Single file containing hash in the download path of the binary.
        return $this->readFile(basename($path).'/'.$this->getHashPath());
    }

    /**
     * Validate binary.
     *
     * @param string $path
     *
     * @return bool
     */
    public function validateBinary($path)
    {
        $version = exec(sprintf('%s self-update --check-version', $path));

        if (empty($version)) {
            return false;
        }

        // Parse provided version before comparing.
        list($release, $tag) = $this->parseVersion($version);
        unset($release);

        // Binary tag should match what we are expecting to download.
        return $this->getLatestTag() === $tag;
    }

    /**
     * Download and read a JSON file.
     *
     * @param string $path
     *
     * @return string
     */
    private function readJson($path)
    {
        $result = json_decode($this->readFile($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error(sprintf('Unable to decode %s', $path));

            return false;
        }

        return $result;
    }

    /**
     * Read file from flysystem or url.
     *
     * @param string $path
     *
     * @return string
     */
    private function readFile($path)
    {
        try {
            if ($this->use_flysystem) {
                return $this->getFlysystem()->read($path);
            }

            return file_get_contents($this->getUniqueUrl($path));
        } catch (\Exception $exception) {
            $this->error('Could not read '.$path);

            return false;
        }
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
        return sprintf('%s/%s?%s', $this->getUrl(), $path, time());
    }
}
