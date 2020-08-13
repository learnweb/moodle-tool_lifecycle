# Course Life Cycle (moodle-tool_lifecycle)

[![Build Status](https://travis-ci.org/learnweb/moodle-tool_lifecycle.svg?branch=master)](https://travis-ci.org/learnweb/moodle-tool_lifecycle)
[![codecov](https://codecov.io/gh/learnweb/moodle-tool_lifecycle/branch/master/graph/badge.svg)](https://codecov.io/gh/learnweb/moodle-tool_lifecycle)

This plugin provides a modular framework, similar to a workflow engine, which allows the to execute recurring tasks within moodle associated with courses. 
Possible use cases are (not limited to):
   - Deleting courses at end of life (including asking teachers for permission).
   - Doing a rollover at the end of a semester.
   - Automatically setting an end date for courses.
   
To be adaptable to the needs of different institutions the plugin provides two subplugin types:

**Trigger**: These subplugins control the conditions a course have to meet so that a specific process is started for it.

**Step**: These subplugins represent atomic, reusable tasks that should be executed for a specific course.

Requirements that are specific to your institution can be added through additional subplugins.
For more information please have a look at the [wiki](https://github.com/learnweb/moodle-tool_lifecycle/wiki).
It provides instructions for administrators as well as for developers to implement own requirements into subplugins.

Installation
============
This is an admin plugin and should go into ``admin/tool/lifecycle``.
Obtain this plugin from https://moodle.org/plugins/view/tool_lifecycle.

Moodle version
==============
The plugin is continously tested with all moodle versions, which are security supported by the moodle headquarter.
Therefore, Travis uses the most current release to build a test instance and run the behat and unit tests on them.
In addition to all stable branches the version is also tested against the master branch to support early adopters.

Changelog
=========
The changes for every release are listed here: https://github.com/learnweb/moodle-tool_lifecycle/wiki/Change-log.
