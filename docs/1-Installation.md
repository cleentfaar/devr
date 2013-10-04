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
To be able to access DEVR from anywhere in your filesystem, you must add it to your so-called PATH environment variable.
Follow the instructions below for your own operating system:

    #Linux users#
    You can add the DEVR directory to your path with the following command, replacing the example path given

    ``~$ export PATH=$PATH:/path/to/devr``

    A better way would be to edit your .bash_profile file to include the above command. That way, it would be done
    automatically every time you log in. Most modern Linux distributions encourage a practice in which each user has a
    specific directory for the programs he/she personally uses. This directory is called 'bin' and is a subdirectory of
    your home directory. If you do not already have one, create it with the following command (while in your home dir):

    ``~$ mkdir bin``

    Now download the DEVR package and place the extracted directory 'devr' into your new bin directory and you're all set.
    Now you just have to type:

    ``~$ devr``

    to access all the commands at your fingertips.


3. Configuration
------------------------------------------------------------------------------------------------------------------------
Configuration for all commands are done through a local sqlite database, which will be created for you automatically.
You can make changes to the configuration by using the appropriate ``config`` command, e.g. ``config:set mykey myvalue``

