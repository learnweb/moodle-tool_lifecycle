Upgrading this plugin
=====================

This is an internal documentation for plugin developers with some notes what has to be considered when updating this plugin to a new Moodle major version.

General
-------

* Generally, this is a quite simple plugin with just one purpose.
* It does not rely on any fluctuating library functions and should remain quite stable between Moodle major versions.
* Thus, the upgrading effort is low.


Upstream changes
----------------

* This plugin is built on top of tool_lifecycle and customfield_semester which do have their own roadmap and release process. 
* If you update this plugin, you should check if these two plugins had any major changes which might have an effect on this plugin.


Automated tests
---------------

* The plugin has a good coverage with PHPUnit tests which test all of the plugin's user stories.


Manual tests
------------

* There aren't any manual tests needed to upgrade this plugin.


Visual checks
-------------

* There aren't any additional visual checks in the Moodle GUI needed to upgrade this plugin.
