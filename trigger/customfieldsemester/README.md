moodle-lifecycletrigger_customfieldsemester
===========================================

[![Moodle Plugin CI](https://github.com/moodle-an-hochschulen/moodle-lifecycletrigger_customfieldsemester/actions/workflows/moodle-plugin-ci.yml/badge.svg?branch=main)](https://github.com/moodle-an-hochschulen/moodle-lifecycletrigger_customfieldsemester/actions?query=workflow%3A%22Moodle+Plugin+CI%22+branch%3Amain)

Moodle trigger subplugin for the Moodle admin tool "Course Life Cycle" which triggers based on values of a custom course field of type "semester".


Requirements
------------

This plugin requires Moodle 5.0+

Additionally, this plugin requires two other third party plugins:

1. The Moodle plugin tool_lifecycle which is published on https://github.com/learnweb/moodle-tool_lifecycle / https://moodle.org/plugins/tool_lifecycle.
2. The Moodle plugin customfield_semester which is published on https://github.com/learnweb/moodle-customfield_semester / https://moodle.org/plugins/customfield_semester.


Motivation for this plugin
--------------------------

Higher education institutions are offering their lectures in lecture terms and most often these lecture terms are semesters. After a semester has ended, a new one is started and most often a particular lecture is offered again, but it is run in a new Moodle course.

The third party plugin customfield_semester is a great tool to manage the semester which a particular Moodle course belongs to.
And the third party plugin tool_lifecycle is a great tool to get rid of outdated Moodle courses.

This plugin connects both worlds as it allows the Moodle admin to get rid of Moodle courses after a defined amount of semesters has passed.


Installation
------------

Install the plugin like any other plugin to folder
/admin/tool/lifecycle/trigger/customfieldsemester

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it does not do anything to Moodle yet.

To configure the trigger and its behaviour, please visit:
Site administration -> Plugins -> Admin tools -> Life Cycle -> Workflow settings and add a new workflow there.

Within this workflow, please add a new trigger instance of type "Customfield semester trigger".

Within the trigger configuration, you have two specific settings to define:

### 1. Custom field

With this setting, you define the (pre-existing) custom field which holds the term of a course. The value of this field will be the basis of this trigger.

### 2. Trigger x months after term start

With this setting, you define the delay until a process is started.

The trigger will take the term of a course, get the start month of the term, add the configured amount of months as delay and check if this delay period has already passed. If yes, the trigger will be invoked.

Courses which have the 'term-independent' value in the custom course field will never be triggered.


Capabilities
------------

This plugin does not add any additional capabilities.


Scheduled Tasks
---------------

This plugin does not add any additional scheduled tasks.


Theme support
-------------

This plugin acts behind the scenes, therefore it should work with all Moodle themes.
This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
http://moodle.org/plugins/view/lifecycletrigger_customfieldsemester

The latest development version can be found on Github:
https://github.com/moodle-an-hochschulen/moodle-lifecycletrigger_customfieldsemester


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/moodle-an-hochschulen/moodle-lifecycletrigger_customfieldsemester/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Due to limited resources, the functionality of this plugin is primarily implemented for our own local needs and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/moodle-an-hochschulen/moodle-lifecycletrigger_customfieldsemester/issues

Please create pull requests on Github:
https://github.com/moodle-an-hochschulen/moodle-lifecycletrigger_customfieldsemester/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

Due to limited resources, this plugin is only maintained for the most recent major release of Moodle as well as the most recent LTS release of Moodle. Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.

Apart from these maintained releases, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that this plugin still works with a new major release - please let us know on Github.

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.

As the plugin creator, we manage the translation into german for our own local needs on AMOS. Please contribute your translation into all other languages in AMOS where they will be reviewed by the official language pack maintainers for Moodle.


Right-to-left support
---------------------

This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send us a pull request on Github with modifications.


Maintainers
-----------

The plugin is maintained by\
Moodle an Hochschulen e.V.


Copyright
---------

The copyright of this plugin is held by\
Moodle an Hochschulen e.V.

Individual copyrights of individual developers are tracked in PHPDoc comments and Git commits.


Initial copyright
-----------------

This plugin was initially built by\
Alexander Bias

on behalf of\
Hochschule Hannover
Servicezentrum Lehre E-Learning (elc)

It was contributed to the Moodle an Hochschulen e.V. plugin catalogue in 2022.
