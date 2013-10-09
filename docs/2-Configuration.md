DEVR - Configuration
========================================================================================================================

There are special commands available for manipulating the DEVR configuration while it's installed on your system. These
commands are listed below.

The commands generally can be executed as follows: ``devr command_namespace:command``

To get help on a certain command, simply execute: ``devr command_namespace:command --help``


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
