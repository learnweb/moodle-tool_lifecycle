# Course Life Cycle (moodle-tool_lifecycle)

[![codecov](https://codecov.io/gh/learnweb/moodle-tool_lifecycle/branch/master/graph/badge.svg)](https://codecov.io/gh/learnweb/moodle-tool_lifecycle)

You find the documentation for this Moodle community plugin in the wiki section of this github repository!

Course Life Cycle is a Moodle plugin that enables the automatic processing and removal of courses during and after their active period. It offers a wide range of selection criteria and processing steps for this purpose.
   
To be adaptable to the needs of different institutions the plugin provides two subplugin types:

**Trigger**: These subplugins control the conditions a course must meet so that a specific process is started.

**Step**: These subplugins represent atomic, reusable tasks that should be executed for a specific course.

## Subplugins
Requirements that are specific to your institution can be added through additional subplugins.
A list of all subplugins and more information can be found in the [Wiki](https://github.com/learnweb/moodle-tool_lifecycle/wiki) ([subpluginslist](https://github.com/learnweb/moodle-tool_lifecycle/wiki/List-of-Installed-Subplugins)).
It provides instructions for administrators as well as for developers to implement their own requirements into subplugins.

Installation
============
This is an admin plugin and should go into ``admin/tool/lifecycle``.

Obtain this plugin from https://moodle.org/plugins/view/tool_lifecycle.

Moodle version
==============
The plugin is continuously tested with all moodle versions, which are security supported by the moodle headquarter.
