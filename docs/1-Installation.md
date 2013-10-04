DEVR - Installation
========================================================================================================================

1. Composer
------------------------------------------------------------------------------------------------------------------------
The dependencies for the script will have to be installed first. For this, Composer is used so first make sure you have
it installed or downloaded the .phar to devr's root directory.

    ```
    ~$ cd /path/to/devr
    ~$ composer.phar install
    ```


2. Command-line execution
------------------------------------------------------------------------------------------------------------------------
Currently there is a php callable script that you can run by navigating to devr's root directory and typing

    ```
    ~$ cd /path/to/devr
    ~$ php app/devr
    ```

    *The plan is to make an actual *NIX installable so that it can be called from anywhere (comparable to Composer)*


3. Configuration
------------------------------------------------------------------------------------------------------------------------
Configuration for all commands are done through a local sqlite database, which will be created for you automatically.
You can make changes to the configuration by using the appropriate ``config`` command, e.g. ``config:set mykey myvalue``

