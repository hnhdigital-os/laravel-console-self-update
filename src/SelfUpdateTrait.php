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
    protected $release;

    /**
     * @var string
     */
    protected $version;

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
            $this->line('You are already up-to-date: <info>'.$this->release.'-'.$this->tag.'</info>');
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
        list($release, $version) = explode('-', config('app.version'), 2);

        $this->release = $release;
        $this->tag = $this->latest_tag = $version;
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

        $this->latest_tag = $this->readFile('latest');

        if ($this->latest_tag === false) {
            return false;
        }

        return $this->tag !== $this->latest_tag;
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
        $binary_path = $this->getBinaryPath();

        $this->backupCurrentBinary($binary_path);

        $versions = $this->readVersions();
        $download_path = ltrim(array_get($versions, $this->latest_tag.'.path'), '/');

        $temp_binary_path = $this->getTempPath($binary_path);

        file_put_contents($temp_binary_path, $this->downloadUpdatedBinary($download_path));

        // Match mod.
        chmod($temp_binary_path, fileperms($binary_path));

        if (!$this->validateBinary($temp_binary_path)) {
            $this->error('Could not validate update.');
            unlink($temp_binary_path);

            exit(1);
        }

        // Remove existing binary.
        unlink($binary_path);

        // Replace with the new binary.
        rename($temp_binary_path, $binary_path);

        $this->line('You are now running the latest version: <info>'.$this->release.'-'.$this->latest_tag.'</info>');
    }

    /**
     * Get temp path.
     *
     * @return string
     */
    protected function getTempPath($path)
    {
        return sprintf('/tmp/%s.%s', basename($path), $this->tag);
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
        if (!is_writable($path)) {
            $this->error('Can not self-update - not writable.');

            exit(1);
        }

        if (!is_writable(dirname($path))) {
            throw new \Exception('');
            $this->error('Can not write to parent path to backup.');

            exit(1);
        }

        // Copy file.
        copy($path, $this->getBackupPath($path));

        // Match mod.
        chmod($this->getBackupPath($path), fileperms($path));
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

        // Binary version should match what we're expecting to download.
        return $version == $this->release.'-'.$this->latest_tag;
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
            } else {
                return file_get_contents($this->url.'/'.$path);
            }
        } catch (\Exception $exception) {
            $this->error('Could not read '.$path);

            exit(1);
        }
    }
}
