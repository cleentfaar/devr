DEVR - Configuration
========================================================================================================================

Currently there are only a few commands available and they are all at their very first iteration. Be warned to use
these with care on your filesystem, or use the --dry-run option where available to prevent any file changes whatsoever

The commands generally can be executed as follows: ``php app/devr command:here``

To get help on a certain command, simply execute: ``php app/devr command:here --help``

Currently, the following commands are available to you:


### config:list ###

Lists all key/value pairs as defined in the configuration
```
Usage:
 config:list

Options:
  --help (-h)           Display this help message.
  --quiet (-q)          Do not output any message.
  --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
  --version (-V)        Display this application version.
  --ansi                Force ANSI output.
  --no-ansi             Disable ANSI output.
  --no-interaction (-n) Do not ask any interactive question.
```


### config:get ###

Returns the value for a given key in the configuration
```
Usage:
 config:get key

Arguments:
 key                   The name of the key to get the value for

Options:
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.
```


### config:set ###

Changes the value for the given key
```
Usage:
 config:set [-f|--force] key value

Arguments:
 key                   The name of the key to get the value for
 value                 The new value for this key

Options:
 --force (-f)          Use this option if you would like to create the key if it does not exist yet
 --help (-h)           Display this help message.
 --quiet (-q)          Do not output any message.
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version.
 --ansi                Force ANSI output.
 --no-ansi             Disable ANSI output.
 --no-interaction (-n) Do not ask any interactive question.
```
