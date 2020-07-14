<?php

/**
 * This file is part of Liaison Installer for CodeIgniter4.
 *
 * (c) John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liaison\Installers\CodeIgniter4\Command;

use GuzzleHttp\Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * NewCommand
 *
 * Scaffolds an entire CodeIgniter4 application.
 *
 * @author John Paul E. Balandan, CPA <paulbalandan@gmail.com>
 */
class NewCommand extends Command
{
    /**
     * Git config variables
     *
     * @var array
     */
    protected $gitConfig = [];

    /**
     * InputInterface object
     *
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * OutputInterface object
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('new')
            ->setDescription('Create a new CodeIgniter4 application.')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of the local directory where the application will be made.')
            ->addOption('dev', 'd', InputOption::VALUE_NONE, 'Installs the latest CI4 developer version')
            ->addOption('with-git', null, InputOption::VALUE_NONE, 'Initializes an empty Git repository in the directory.')
            ->addOption('with-gitflow', null, InputOption::VALUE_NONE, 'Uses GitFlow to initialize the Git repository. This has "--with-git" option implicitly included.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force install on existing directory.')
        ;
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws RuntimeException
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!\extension_loaded('zip')) {
            throw new RuntimeException('Liaison Installer for CodeIgniter4 needs the ZIP extension installed.');
        }

        // save these for use by other methods later
        $this->input  = $input;
        $this->output = $output;

        $name      = $this->input->getArgument('name');
        $directory = ($name && '.' !== $name) ? getcwd() . DIRECTORY_SEPARATOR . $name : getcwd();

        if (!$this->input->getOption('force') && $this->verifyApplicationDirectory($directory)) {
            throw new RuntimeException('Application already exists.');
        }

        $this->output->writeln('<info>Creating your own CodeIgniter4 application...</info>');
        $zipFile = $this->getFilename();

        return $this
            ->download($zipFile, $this->getVersion())
            ->extract($zipFile, $directory)
            ->prepareWritableDirectory($directory)
            ->cleanUp($zipFile)
            ->prepareComposerJson($directory)
            ->initializeGit($directory)
            ->initializeGitFlow($directory)
            ->installApplication($directory)
        ;
    }

    /**
     * Verify that the application directory does not exist.
     *
     * @param string $directory
     *
     * @return bool
     */
    protected function verifyApplicationDirectory(string $directory)
    {
        return (is_dir($directory) || is_file($directory)) && $directory !== getcwd();
    }

    /**
     * Gets the temporary filename for the zip file.
     *
     * @return string
     */
    protected function getFilename(): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'codeigniter4_' . md5(time() . uniqid()) . '.zip';
    }

    /**
     * Gets the CI4 version to download.
     *
     * @return string
     */
    protected function getVersion(): string
    {
        if ($this->input->getOption('dev')) {
            return 'develop';
        }

        return 'framework';
    }

    /**
     * Gets the zipball URL for the latest release of framework.
     *
     * @return string
     */
    protected function getFrameworkURL(): string
    {
        $response = (new Client())->get('https://api.github.com/repos/codeigniter4/appstarter/releases/latest');

        $json = json_decode($response->getBody(), true);
        return $json['zipball_url'];
    }

    /**
     * Downloads the zip file.
     *
     * @param string $zipFile
     * @param string $version
     *
     * @return $this
     */
    protected function download(string $zipFile, string $version = 'framework')
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln('<info>Downloading the zip file...</info>');
        }

        switch ($version) {
            case 'develop':
                $zip = 'https://github.com/codeigniter4/CodeIgniter4/zipball/develop';
                break;
            case 'framework':
            default:
                $zip = $this->getFrameworkURL();
                break;
        }

        $response = (new Client())->get($zip);

        file_put_contents($zipFile, $response->getBody());
        return $this;
    }

    /**
     * Extracts the zip file into the directory
     *
     * @param string $zipFile
     * @param string $directory
     *
     * @return $this
     */
    protected function extract(string $zipFile, string $directory)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln('<info>Extracting zip file...</info>');
        }

        $archive  = new ZipArchive();
        $response = $archive->open($zipFile, ZipArchive::CHECKCONS);

        if (!$response) {
            throw new RuntimeException('The zip file errored during download.');
        }

        // the zip has a parent folder so we need to know its name
        // to properly extract the subdirectories
        $parentFolder = $archive->getNameIndex(0);

        $archive->extractTo($directory);
        $archive->close();

        $fs = new Filesystem();
        $fs->mirror($directory . DIRECTORY_SEPARATOR . $parentFolder, $directory);
        $fs->remove($directory . DIRECTORY_SEPARATOR . $parentFolder);

        if ($this->input->getOption('dev')) {
            $dirs = [
                $directory . DIRECTORY_SEPARATOR . '.github',
                $directory . DIRECTORY_SEPARATOR . 'system',
                $directory . DIRECTORY_SEPARATOR . 'tests',
                $directory . DIRECTORY_SEPARATOR . 'user_guide_src',
            ];

            $fs->remove($dirs);
        }

        return $this;
    }

    /**
     * Prepares writability of the 'writable' directory.
     *
     * @param string $directory
     *
     * @return $this
     */
    protected function prepareWritableDirectory(string $directory)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln('<info>Preparing permissions on "writable" directory...</info>');
        }

        try {
            $fs = new Filesystem();
            $fs->chmod($directory . DIRECTORY_SEPARATOR . 'writable', 0755, 0000, true);
        } catch (IOExceptionInterface $e) {
            $this->output->writeln('<error>' . $e->getMessage() . '</error>');
            $this->output->writeln('<comment>You should verify that the "writable" directory is really writable.</comment>');
        }

        return $this;
    }

    /**
     * Clean up the zip file
     *
     * @param string $zipFile
     *
     * @return $this
     */
    protected function cleanUp(string $zipFile)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln('<info>Cleaning up the zip file...</info>');
        }

        if (!@chmod($zipFile, 0777) || !@unlink($zipFile)) {
            $this->output->writeln('Cannot clean up the zip file. Please delete it yourself.');
        }

        return $this;
    }

    /**
     * Finds the composer executable.
     *
     * @return string
     */
    protected function findComposerPhar(): string
    {
        $composerPhar = getcwd() . '/composer.phar';
        $phpBinary    = (new PhpExecutableFinder())->find();

        if (file_exists($composerPhar)) {
            return escapeshellarg($phpBinary) . ' ' . escapeshellarg($composerPhar);
        }

        return 'composer';
    }

    /**
     * Prepares the composer.json with additional details.
     *
     * @param string $directory
     *
     * @return $this
     */
    protected function prepareComposerJson(string $directory)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln('<info>Preparing composer.json...</info>');
        }

        $composerPath = $directory . '/composer.json';
        $templateJson = json_decode(file_get_contents(__DIR__ . '/../../bin/template.json'), true);

        if (file_exists($composerPath)) {
            @unlink($composerPath);
        }

        $git = $this->getGitConfig();

        // create package name
        $name = basename(realpath('.'));
        $name = preg_replace('{(?:([a-z])([A-Z])|([A-Z])([A-Z][a-z]))}', '\\1\\3-\\2\\4', $name);
        $name = mb_strtolower($name);

        if (isset($git['github.user'])) {
            $name = $git['github.user'] . '/' . $name;
        } elseif (get_current_user()) {
            $name = get_current_user() . '/' . $name;
        } else {
            $name .= '/' . $name;
        }

        $name = mb_strtolower($name);

        // get author details
        $author = [
            'name'  => '',
            'email' => '',
        ];

        if (isset($git['user.name'])) {
            $author['name'] = $git['user.name'];
        }

        if (isset($git['user.email'])) {
            $author['email'] = $git['user.email'];
        }

        $templateJson['name']      = $name;
        $templateJson['authors'][] = $author;

        file_put_contents($composerPath, json_encode(
            $this->appendComposerJson($templateJson),
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        ) . "\n");

        return $this;
    }

    /**
     * Appends additional details to composer.json
     *
     * @param array $composerJson
     * @param array $templateJson
     *
     * @return array
     */
    protected function appendComposerJson(array $composerJson, ?array $templateJson = null)
    {
        $templateJson  = $templateJson ?? $composerJson;

        $framework = ($this->input->getOption('dev'))
            ? ['codeigniter4/CodeIgniter4' => 'dev-develop']
            : ['codeigniter4/framework' => '^4'];

        // minimum stability
        $this->input->getOption('dev')
            ? $composerJson['minimum-stability'] = 'dev'
            : $composerJson['minimum-stability'] = 'stable';

        // repositories
        if ($this->input->getOption('dev')) {
            if (!isset($composerJson['repositories'])) {
                $composerJson['repositories'] = [];
            }

            $composerJson['repositories'][] = [
                'type' => 'vcs',
                'url'  => 'https://github.com/codeigniter4/codeigniter4',
            ];
        }

        // require
        $composerJson['require'] = isset($composerJson['require'])
            ? array_unique(array_merge($composerJson['require'], $templateJson['require'], $framework))
            : array_merge($templateJson['require'], $framework);

        // require-dev
        $composerJson['require-dev'] = isset($composerJson['require-dev'])
            ? array_unique(array_merge($composerJson['require-dev'], $templateJson['require-dev']))
            : $templateJson['require-dev'];

        $composerJson['autoload-dev']['psr-4']      = $templateJson['autoload-dev']['psr-4'];
        $composerJson['scripts']['post-update-cmd'] = $templateJson['scripts']['post-update-cmd'];

        return $composerJson;
    }

    /**
     * Gets the location of the git binary.
     *
     * @throws RuntimeException
     * @return string
     */
    protected function getGitBinary(): string
    {
        $gitBin = (new ExecutableFinder())->find('git');

        if (null === $gitBin) {
            throw new RuntimeException('Git is not installed in your machine.');
        }

        return $gitBin;
    }

    /**
     * Gets the git config variables.
     *
     * @throws RuntimeException
     * @return array
     */
    protected function getGitConfig(): array
    {
        if ($this->gitConfig) {
            return $this->gitConfig;
        }

        $gitBin = escapeshellarg($this->getGitBinary());
        $cmd    = Process::fromShellCommandline($gitBin . ' config -l');
        $cmd->run();

        if ($cmd->isSuccessful()) {
            $this->gitConfig = [];

            preg_match_all('{^([^=]+)=(.*)$}m', $cmd->getOutput(), $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $this->gitConfig[$match[1]] = $match[2];
            }

            return $this->gitConfig;
        }

        return $this->gitConfig = [];
    }

    /**
     * Initializes an empty Git repository.
     *
     * @param string $directory
     *
     * @return $this
     */
    protected function initializeGit(string $directory)
    {
        if ($this->input->getOption('with-git')) {
            if ($this->output->isVerbose()) {
                $this->output->writeln("<info>Initializing empty Git repository in {$directory}...</info>");
            }

            $gitBin = escapeshellarg($this->getGitBinary());
            $cmd    = Process::fromShellCommandline($gitBin . ' init', $directory);
            $cmd->run();

            if ($cmd->isSuccessful()) {
                $this->output->writeln("<comment>Empty Git repository initialized at {$directory}</comment>");
            } else {
                $this->output->writeln('<error>Git initialization failed. Please run "git init" by yourself.</error>');
            }

            return $this;
        }

        return $this;
    }

    /**
     * Initializes the repository using Git Flow.
     *
     * @param string $directory
     *
     * @return $this
     */
    protected function initializeGitFlow(string $directory)
    {
        if ($this->input->getOption('with-gitflow')) {
            if ($this->output->isVerbose()) {
                $this->output->writeln('<info>Initializing Git Flow...</info>');
            }

            if (!(new ExecutableFinder())->find('git-flow')) {
                $this->output->writeln('<error>Git Flow is not installed in your machine.</error>');
                return $this;
            }

            $gitBin   = escapeshellarg($this->getGitBinary());
            $commands = [
                $gitBin . ' init',
                $gitBin . ' flow init -d -f --local -t v',
            ];

            $cmd    = Process::fromShellCommandline(implode(' && ', $commands), $directory);
            $cmd->setTimeout(3600)->run();

            if ($cmd->isSuccessful()) {
                $this->output->writeln("<comment>Git Flow initialized at {$directory}</comment>");
            } else {
                $this->output->writeln('<error>Git Flow initialization failed. Please run "git flow init" by yourself.</error>');
            }

            return $this;
        }

        return $this;
    }

    /**
     * The main installation logic.
     *
     * @param string $directory
     *
     * @return int
     */
    protected function installApplication(string $directory)
    {
        $composer = $this->findComposerPhar();
        $commands = [
            $composer . ' install --ansi',
        ];

        if ($this->input->getOption('no-ansi')) {
            $commands = array_map(function ($command) {
                return str_replace('--ansi', '--no-ansi', $command);
            }, $commands);
        }

        if ($this->input->getOption('quiet')) {
            $commands = array_map(function ($command) {
                return $command . ' --quiet';
            }, $commands);
        }

        $cmd = Process::fromShellCommandline(implode(' && ', $commands), $directory);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $cmd->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('<comment>Warning: ' . $e->getMessage() . '</comment>');
            }
        }

        $output = $this->output;

        $cmd
            ->setTimeout(3600)
            ->run(function ($type, $line) use ($output) {
                $output->write($line);
            })
        ;

        if ($cmd->isSuccessful()) {
            $this->output->writeln('<comment>Application ready! Start building your craft now!</comment>');
            return 0;
        }

        $this->output->writeln('<error>Application scaffolding failed.</error>');
        return 1;
    }
}
