<?php

/**
 * This file is part of Liaison Installer for CodeIgniter4.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Installers\CodeIgniter4\Tests\Command;

use Liaison\Installers\CodeIgniter4\Application;
use Liaison\Installers\CodeIgniter4\Command\NewCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * NewCommandTest
 *
 * @author John Paul E. Balandan <paulbalandan@gmail.com>
 */
class NewCommandTest extends TestCase
{
    /** @var Filesystem */
    protected $fs;

    /** @var string */
    protected $name;

    /** @var string */
    protected $directory = '';

    protected function setUp(): void
    {
        $this->fs        = new Filesystem();
        $this->name      = 'output/my-app';
        $this->directory = realpath(__DIR__ . '/../..') . '/output/my-app';

        if ($this->fs->exists($this->directory)) {
            $this->fs->chmod($this->directory, 0777, 0000, true);
            $this->fs->remove($this->directory);
        }
    }

    protected function tearDown(): void
    {
        if ($this->fs->exists($this->directory)) {
            $this->fs->chmod($this->directory, 0777, 0000, true);
            $this->fs->remove($this->directory);
        }
    }

    public function testInstallerCanScaffoldANewCodeigniterApp()
    {
        $app = new Application();
        $app->add(new NewCommand());

        $tester = new CommandTester($app->find('new'));

        $exitCode = $tester->execute(['name' => $this->name, '--verbose' => null]);

        $this->assertEquals(0, $exitCode);
        $this->assertDirectoryExists($this->directory . '/vendor');
        $this->assertFileExists($this->directory . '/.env');
    }

    public function testInstallerCanScaffoldWithConfig()
    {
        if ('\\' !== DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('Currently cannot make this test pass on Linux.');
        }

        $app = new Application();
        $app->add(new NewCommand());

        $tester = new CommandTester($app->find('new'));

        $exitCode = $tester->execute([
            'name'      => $this->name,
            '--verbose' => null,
            '--dev'     => null,
            '--config'  => 'config.php',
        ]);

        $config = require __DIR__ . '/../../output/config.php';
        $json   = json_decode(file_get_contents($this->directory . '/composer.json'), true);

        $this->assertEquals(0, $exitCode);
        $this->assertDirectoryExists($this->directory . '/vendor');
        $this->assertFileExists($this->directory . '/.env');
        $this->assertEquals($config['userName'], $json['authors'][0]['name']);
        $this->assertEquals($config['userEmail'], $json['authors'][0]['email']);
        $this->assertEquals($config['license'], $json['license']);
    }
}
