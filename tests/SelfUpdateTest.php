<?php

namespace HnhDigital\LaravelConsoleSelfUpdate\Tests;

use HnhDigital\LaravelConsoleSelfUpdate\SelfUpdateInterface;
use PHPUnit\Framework\TestCase;

class SelfUpdateTest extends TestCase
{
    /**
     * Setup required for tests.
     *
     * @return void
     */
    public function setUp()
    {

    }

    public function testSetUrl()
    {
        $command = new MockCommand();
        $command->setUrl('localhost');
        $this->assertEquals('localhost', $command->getUrl());
    }

    public function testParseVersion()
    {
        $command = new MockCommand();

        $release_tag = $command->parseVersion('1.0.0');

        $this->assertEquals('stable', $release_tag[0]);
        $this->assertEquals('1.0.0', $release_tag[1]);

        $release_tag = $command->parseVersion('stable-1.0.0');
        $this->assertEquals('stable', $release_tag[0]);
        $this->assertEquals('1.0.0', $release_tag[1]);

        $release_tag = $command->parseVersion('dev-1.0.0');
        $this->assertEquals('dev', $release_tag[0]);
        $this->assertEquals('1.0.0', $release_tag[1]);

        $release_tag = $command->parseVersion('dev-1.0.0-beta1');
        $this->assertEquals('dev', $release_tag[0]);
        $this->assertEquals('1.0.0-beta1', $release_tag[1]);
    }

    public function testBackupPath()
    {
        $command = new MockCommand();
        $command->setCurrentTag('1.0.0');
        
        $this->assertEquals('mysql-helper.1.0.0', $command->getBackupPath('mysql-helper'));
    }

    public function testTempPath()
    {
        $command = new MockCommand();
        
        $this->assertEquals('/tmp/mysql-helper.1.0.0', $command->getTempPath('mysql-helper', '1.0.0'));
    }

    public function testLatestTagPath()
    {
        $command = new MockCommand();
        
        $this->assertEquals('latest', $command->getLatestTagPath());

        $command->setLatestTagPath('latest.json');
        $this->assertEquals('latest.json', $command->getLatestTagPath());
    }

    public function testVersionsPath()
    {
        $command = new MockCommand();
        
        $this->assertEquals('versions', $command->getVersionsPath());
        
        $command->setVersionsPath('version.json');
        $this->assertEquals('version.json', $command->getVersionsPath());
    }

    public function testHashFromString()
    {
        $command = new MockCommand();
        $this->assertEquals('9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', $command->getHashFromString('test'));
    }

    public function testCompareHash()
    {
        $command = new MockCommand();
        $command->setLatestTag('1.0.1');

        $tmp_file = tempnam('/tmp', 'mysql-helper-1.0.1');
        file_put_contents($tmp_file, 'testing');
        $hash = hash_file($command->getHashAlgo(), $tmp_file);
        unlink($tmp_file);

        $command->setHashSource(SelfUpdateInterface::CHECKSUM_DISABLED);
        $this->assertTrue($command->compareHash('', 'testing'));

        $checksums = [
            '1.0.0' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
            '1.0.1' => $hash,
        ];

        $tmp_file = tempnam('/tmp', 'mysql-helper-checksums');
        file_put_contents($tmp_file, json_encode($checksums));
        $command->setHashSource(SelfUpdateInterface::CHECKSUM_TOP_LEVEL);
        $command->setHashPath($tmp_file);

        $this->assertTrue($command->compareHash('', 'testing'));

        $command->setVersionData([
            '1.0.0' => ['sha256' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08'],
            '1.0.1' => ['sha256' => $hash],
        ]);
        $command->setHashPath('sha256');
        $command->setHashSource(SelfUpdateInterface::CHECKSUM_VERSIONS);

        $this->assertTrue($command->compareHash('', 'testing'));

        unlink($tmp_file);
    }
}
