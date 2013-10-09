DEVR - Commands reference
========================================================================================================================

Currently there are only a few commands available and they are all at their very first iteration. Be warned to use
these with care on your filesystem, or use the --dry-run option where available to prevent any file changes whatsoever

The commands generally can be executed as follows: ``devr command:here``

To get help on a certain command, simply execute: ``devr command:here --help``

Currently, the following commands are available to you:


### install:composer ###

Installs composer in the working directory, e.g. for easy use in a project.
NOTE: This is not meant to be used to install DEVR itself, you will still need to do that manually (at least once)!
```
Usage:
 install:composer [-a|--auto-install] [-f|--force]

Options:
 --auto-install (-a)   Use this to let composer automatically install any dependencies found in composer.json after the installation succeeded
 --force (-f)          Use this to overwrite any available composer.phar with the latest version downloaded from http://getcomposer.org/composer.phar
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.
```


### create:project ###

Creates a new project for a client
```
Usage:
 create:project [-d|--dry-run]

Options:
 --dry-run (-d)        Use this to only show what would happen, without writing to any files
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.
 ```

There are also a number of commands related to configuring DEVR itself, for this, see [the Configuration doc](2-Configuration.md)