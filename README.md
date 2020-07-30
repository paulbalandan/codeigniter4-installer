# Liaison Installer for CodeIgniter4

![QA Build](https://github.com/paulbalandan/codeigniter4-installer/workflows/QA%20Build/badge.svg?branch=develop)
![QA Control](https://github.com/paulbalandan/codeigniter4-installer/workflows/QA%20Control/badge.svg?branch=develop)
[![Latest Stable Version](https://poser.pugx.org/paulbalandan/codeigniter4-installer/v)](https://packagist.org/packages/paulbalandan/codeigniter4-installer)
[![Latest Unstable Version](https://poser.pugx.org/paulbalandan/codeigniter4-installer/v/unstable)](https://packagist.org/packages/paulbalandan/codeigniter4-installer)
[![GitHub license](https://img.shields.io/github/license/paulbalandan/codeigniter4-installer)](LICENSE)
[![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg)](https://github.com/paulbalandan/codeigniter4-installer/pulls)

## Installation

Using Composer, globally install this Installer using the following command:
```bash
    composer global require paulbalandan/codeigniter4-installer
```

## Usage

Now that you have globally installed the Installer, you can just use `codeigniter4 new [name]` in your terminal, where `name` is the name of the directory, to call the scaffolding.

`name` here is optional. If you did not provide one, this will default to your current working directory.
If `name` is provided, it will be resolved relative to the current working directory.

Run `codeigniter4 new --help` for the full options.

## Options

- `--dev` - Installs the latest CI4 developer version instead of the latest stable release
- `--with-git` - Initializes an empty Git repository in the directory
- `--with-gitflow` - Uses GitFlow to initialize the Git repository. This has `--with-git` option implicitly included.
- `-f|--force` - Force install on existing directory.

**Note:** These options are not enabled by default. You should provide your set of options.
