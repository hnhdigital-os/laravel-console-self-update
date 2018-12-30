<?php

namespace HnhDigital\LaravelConsoleSelfUpdate;

/**
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
    protected $version;

    /**
     * @var string
     */
    protected $release;

    /**
     * @var string
     */
    protected $tag;

    /**
     * @var string
     */
    protected $latest_tag;

    /**
     * Use public url to self-update.
     *
     * @var bool
     */
    private $flysystem = false;

    /**
     * @var string
     */
    private $url = '';

    /**
     * Execute the command.
     *
     * @return void
     */
    public function runSelfUpdate()
    {
        $this->parseVersion();

        if (!empty($this->option('check-version'))) {
            $this->line($this->release.'-'.$this->tag);
            return;
        }

        $this->line('');

        // Check for latest version.
        if (!$this->checkVersion()) {
            $this->info('You are running the latest version!');
            $this->line('');

            return;
        }

        return $this->processUpdate();
    }

    /**
     * Parse the version.
     *
     * @return void
     */
    protected function parseVersion()
    {
        $this->version = config('app.version');

        list($release, $tag) = explode('-', $this->version, 2);

        $this->release = $release;
        $this->tag = $this->latest_tag = $tag;
    }

    /**
     * Check if there is a new version available.
     *
     * @return bool
     */
    protected function checkVersion()
    {
        $this->line('Release: <info>'.$this->release.'</info>');
        $this->line('Version: <info>'.$this->tag.'</info>');

        if (!$this->flysystem_adapter) {
            $this->line('Source: <info>'.$this->url.'</info>');
            $this->line('');
        }

        // Tag to install has been specified.
        if (!empty($this->option('tag'))) {
            $this->latest_tag = $this->release === 'stable' ? $this->option('tag') : '';

            return true;
        }

        $this->latest_tag = $this->getLatestTag();

        $this->line('Latest: <info>'.$this->latest_tag.'</info>');
        $this->line('');

        return $this->tag !== $this->latest_tag;
    }

    /**
     * Get the latest tag.
     *
     * @return string
     */
    protected function getLatestTag()
    {
        $tag = $this->readFile('latest');

        if ($this->tag === false) {
            return false;
        }

        return trim($tag);
    }

    /**
     * Set URL.
     * 
     * @param void
     */
    protected function setUrl($url)
    {
        $this->url = $url;
        $this->flysystem_adapter = false;
    }

    /**
     * Set the flysystem.
     *
     * @param Filesystem $adapter [description]
     */
    protected function setFlysystem(Filesystem $flysystem)
    {
        $this->flysystem = $flysystem;
        $this->flysystem_adapter = true;
    }

    /**
     * Process update.
     *
     * @return void
     */
    private function processUpdate()
    {
        $current_binary_path = $this->getBinaryPath();
        $temp_binary_path = $this->getTempPath($current_binary_path);

        // Get the download path for the updated binary.
        if (($download_path = $this->getDownloadPath($this->latest_tag)) === false) {
            $this->error('Could not get path to download.');

            return 1;
        }

        // Save the updated binary to temp disk.
        file_put_contents($temp_binary_path, $this->downloadUpdatedBinary($download_path));

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

        if ($this->release !== 'RELEASE') {
            // Replace with the new binary.
            rename($temp_binary_path, $current_binary_path);
        }

        // Verbose.
        $this->line(sprintf(
            'You are now running the latest version: <info>%s-%s</info>',
            $this->release,
            $this->latest_tag
        ));
    }

    /**
     * Get temp path.
     *
     * @return string
     */
    protected function getTempPath($path)
    {
        return sprintf('/tmp/%s.%s', basename($path), $this->latest_tag);
    }

    /**
     * Get binary path.
     *
     * @return string
     */
    private function getBinaryPath()
    {
        return realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
    }

    /**
     * Backup binary.
     *
     * @return void
     */
    private function backupCurrentBinary($path)
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

        if ($this->release === 'RELEASE') {
            return;
        }

        // Move current binary to backup path.
        rename($path, $this->getBackupPath($path));
    }

    /**
     * Get backup path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getBackupPath($path)
    {
        return $path.'.'.$this->tag;
    }

    /**
     * Get the download path for the given tag.
     *
     * @return string
     */
    protected function getDownloadPath($tag)
    {
        $versions = $this->readVersions();

        if (!isset($versions[$tag])) {
            return false;
        }

        return ltrim(array_get($versions[$tag], 'path', false), '/');
    }

    /**
     * Read the versions file.
     *
     * @return string
     */
    protected function readVersions()
    {
        $versions = json_decode($this->readFile('versions'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Unable to decode the versions file');

            exit(1);
        }

        return $versions;
    }

    /**
     * Download updated binary.
     *
     * @return void
     */
    private function downloadUpdatedBinary($path)
    {
        $file_contents = $this->readFile($path);

        if (mb_strlen($file_contents) === 0) {
            $this->error('Downloaded file is empty.');

            exit(1);
        }

        return $file_contents;
    }

    /**
     * Validate binary.
     *
     * @return bool
     */
    private function validateBinary($path)
    {
        $version = exec(sprintf('%s self-update --check-version', $path));

        if (empty($version)) {
            return false;
        }

        list($release, $tag) = explode('-', $version, 2);

        // Binary version should match what we are expecting to download.
        return $tag === $this->latest_tag;
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
            if ($this->flysystem_adapter) {
                return $this->flysystem->read($path);
            }

            $url = sprintf('%s/%s?%s', $this->url, $path, time());

            return file_get_contents($url);
        } catch (\Exception $exception) {
            $this->error('Could not read '.$path);

            exit(1);
        }
    }
}
