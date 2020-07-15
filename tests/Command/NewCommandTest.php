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

use Liaison\Installers\CodeIgniter4\Command\NewCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
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
        $this->name      = 'output/app';
        $this->directory = __DIR__ . '/../../output/app';

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
        $app = new Application('Liaison Installer for CodeIgniter4');
        $app->add(new NewCommand());

        $tester = new CommandTester($app->find('new'));

        $exitCode = $tester->execute(['name' => $this->name, '-v' => null]);

        $this->assertEquals(0, $exitCode);
        $this->assertDirectoryExists($this->directory . '/vendor');
        $this->assertFileExists($this->directory . '/.env');
    }

    public function testInstallerCanScaffoldADeveloperCodeigniterApp()
    {
        $app = new Application('Liaison Installer for CodeIgniter4');
        $app->add(new NewCommand());

        $tester = new CommandTester($app->find('new'));

        $exitCode = $tester->execute(['name' => $this->name, '--dev' => null, '-v' => null]);

        $this->assertEquals(0, $exitCode);
        $this->assertDirectoryExists($this->directory . '/vendor');
        $this->assertDirectoryNotExists($this->directory . '/admin');
        $this->assertFileNotExists($this->directory . '/CONTRIBUTING.md');
    }

    public function testInstallerCanInitializeGit()
    {
        $app = new Application('Liaison Installer for CodeIgniter4');
        $app->add(new NewCommand());

        $tester = new CommandTester($app->find('new'));

        $exitCode = $tester->execute(['name' => $this->name, '--with-git' => null, '-v' => null]);

        $this->assertEquals(0, $exitCode);
        $this->assertDirectoryExists($this->directory . '/.git');
    }
}