DEVGEN - Installation
========================================================================================================================

1. Composer
------------------------------------------------------------------------------------------------------------------------
The dependencies for the script will have to be installed first. For this, Composer is used so first make sure you have
it installed or downloaded the .phar to devgen's root directory.

    ```
    ~$ cd /path/to/devgen
    ~$ composer.phar install
    ```


2. Command-line execution
------------------------------------------------------------------------------------------------------------------------
Currently there is a php callable script that you can run by navigating to devgen's root directory and typing

    ```
    ~$ cd /path/to/devgen
    ~$ php app/devgen
    ```

    *The plan is to make an actual *NIX installable so that it can be called from anywhere (comparable to Composer)*


3. Configuration
------------------------------------------------------------------------------------------------------------------------
Configuration for all commands are set in app/config/config.yml, you will need to create this file if it does not exist yet
Information on what values can be written over can be found in the devgen.yml file in the same directory.
Note however, that you are advised not to change the devgen.yml-file itself, since that can interfere with the application's
logic rather quickly.

Stick to the config.yml and you'll be fine. Soon I will adjust the configuration process some more to simplify these instructions.
In the meantime, I have created some helper commands to quickly change the configuration (see ``config:set`` etc.)

