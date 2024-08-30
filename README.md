# Course Life Cycle (moodle-tool_lifecycle)

[![Build Status](https://travis-ci.org/learnweb/moodle-tool_lifecycle.svg?branch=master)](https://travis-ci.org/learnweb/moodle-tool_lifecycle)
[![codecov](https://codecov.io/gh/learnweb/moodle-tool_lifecycle/branch/master/graph/badge.svg)](https://codecov.io/gh/learnweb/moodle-tool_lifecycle)

[Presentation Video Moodle Moot 2022](https://www.youtube.com/watch?v=7IduhrBMve4) | 
[Slides](https://moodle.academy/pluginfile.php/42164/mod_data/content/2470/04-9-Finally_%20Deleting%20Courses%20Automatically.pdf)

This plugin provides a modular framework, similar to a workflow engine, which allows the to execute recurring tasks within moodle associated with courses. 
Possible use cases are (not limited to):
   - Deleting courses at end of life (including asking teachers for permission).
   - Doing a rollover at the end of a semester.
   - Automatically setting an end date for courses.
   
To be adaptable to the needs of different institutions the plugin provides two subplugin types:

**Trigger**: These subplugins control the conditions a course must meet so that a specific process is started.

**Step**: These subplugins represent atomic, reusable tasks that should be executed for a specific course.

## Subplugins
Requirements that are specific to your institution can be added through additional subplugins.
A list of all subplugins and more information can be found in the [Wiki](https://github.com/learnweb/moodle-tool_lifecycle/wiki/List-of-Additional-Subplugins) ([subpluginslist](https://github.com/learnweb/moodle-tool_lifecycle/wiki/List-of-Additional-Subplugins)).
It provides instructions for administrators as well as for developers to implement their own requirements into subplugins.

Installation
============
This is an admin plugin and should go into ``admin/tool/lifecycle``.
Obtain this plugin from https://moodle.org/plugins/view/tool_lifecycle.

Moodle version
==============
The plugin is continously tested with all moodle versions, which are security supported by the moodle headquarter.
Therefore, Travis uses the most current release to build a test instance and run the behat and unit tests on them.
In addition to all stable branches the version is tested against the master branch to support early adopters.

Changelog
=========
The changes for every release are listed here: https://github.com/learnweb/moodle-tool_lifecycle/wiki/Change-log.
