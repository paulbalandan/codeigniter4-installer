<?php

/**
 * This file is part of Liaison Installer for CodeIgniter4.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Installers\CodeIgniter4;

use RuntimeException;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The main entry point for the Installer.
 *
 * @author John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 */
class Application extends BaseApplication
{
    public const NAME    = 'Liaison Installer for CodeIgniter4';
    public const VERSION = 'v1.0.0';

    /**
     * Required PHP extensions of
     * Installer and its dependencies
     *
     * @static
     * @var array
     */
    private static $requiredExtensions = [
        'curl',
        'intl',
        'json',
        'mbstring',
        'zip',
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(static::NAME, static::VERSION);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->resolvePlatform();
        parent::doRun($input, $output);
    }

    /**
     * Resolve platform requirements if met.
     *
     * @throws \RuntimeException
     * @return void
     */
    protected function resolvePlatform()
    {
        if (version_compare(PHP_VERSION, '7.2.5', '<')) {
            throw new RuntimeException(static::NAME . ' needs to run on PHP 7.2.5 and higher. Your PHP version is ' . PHP_VERSION . '.');
        }

        $missingExtensions = [];
        foreach (self::$requiredExtensions as $extension) {
            if (!\extension_loaded($extension)) {
                $missingExtensions[] = $extension;
            }
        }

        if ($missingExtensions) {
            throw new RuntimeException(static::NAME . ' needs the following PHP extensions installed: ' . implode(', ', $missingExtensions) . '.');
        }
    }
}
