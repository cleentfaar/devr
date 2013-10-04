DEVR - Installation
========================================================================================================================

1. Composer
------------------------------------------------------------------------------------------------------------------------
The dependencies for the script will have to be installed first.

2. Command-line execution
------------------------------------------------------------------------------------------------------------------------
To be able to access DEVR from anywhere in your filesystem, you must add it to your so-called PATH environment variable.
Follow the instructions below for your own operating system:

###Linux users###

You can add the DEVR directory to your path with the following command, replacing the example path given

``~$ export PATH=$PATH:/path/to/devr``

A better way would be to edit your .bash_profile file to include the above command. That way, it would be done
automatically every time you log in. Most modern Linux distributions encourage a practice in which each user has a
specific directory for the programs he/she personally uses. This directory is called 'bin' and is a subdirectory of
your home directory. If you do not already have one, create it with the following command (while in your home dir):

``~$ mkdir bin``

Now download the DEVR package and place the extracted directory 'devr' into your new bin directory.
The dependencies for DEVR will have to be installed before running it. For this, Composer is used, so first make sure
you have it installed globally or download it from http://getcomposer.org/composer.phar and place it in the directory
used below:

```
~$ cd /path/to/my/bin/devgen
~$ composer install
```

Now you are all set, you just have to type:

``~$ devr``

to access all the commands at your fingertips.


###Windows users###

Before anything, keep in mind that you will have to run your CMD with Administrator rights (right-click, Run as Administrator)

To be able to access DEVR from anywhere in your filestructure, download the package, add it to a directory of your choice,
and then add that directory's path to your 'path' environment variable. If you are unfamiliar witch changing this variable,
see [this tutorial](http://www.computerhope.com/issues/ch000549.htm#0).

After changing the variable, don't forget to close and re-open your CMD if it was still open before, since the variable
will not be applied until your CMD has restarted.

So, start up your CMD again, and you should be able to run the following command from anywhere in your filesystem:

``c:\Users\Cas>devgen``


3. Configuration
------------------------------------------------------------------------------------------------------------------------
Configuration for all commands are done through a local sqlite database, which will be created for you automatically.
You can make changes to the configuration by using the appropriate ``config`` command, e.g. ``config:set mykey myvalue``

