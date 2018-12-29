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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @return void
     */
    public function runSelfUpdate(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        if (!$this->checkVersion()) {
            $this->line('You are already up-to-date: <info>'.$this->release.'-'.$this->version.'</info>');

            return;
        }
    }

    /**
     * Check if there is a new version available.
     *
     * @return bool
     */
    private function checkVersion()
    {
        $this->version = config('app.version');
        $this->release = config('app.release');

        return false;
    }
}
