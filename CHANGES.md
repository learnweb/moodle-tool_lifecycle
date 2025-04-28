CHANGELOG
=========

4.5.0 (2025-04-28)
------------------
* [FEATURE] Workflowoverview: Added possibility to select single courses for process
* [FEATURE] Add customfielddelay trigger
* [FEATURE] At least one trigger and one step necessary for activating a workflow
* [FEATURE] After workflow creation you have to add course selection trigger at first
* [FEATURE] Exclude processed courses from trigger counting
* [FEATURE] Prepare for not included trigger customfieldsemester
* [FEATURE] Workflowoverview: separate course selection and course selection run triggers
* [FEATURE] Improve performance of processor
* [FEATURE] Improve performance of get_count_of_courses_to_trigger_for_workflow
* [FEATURE] Shorter tab texts and introduce tab titles
* [FEATURE] Replace triggers sitecourse and delayedcourses by workflow instance options
* [FEATURE] Add triggers byrole and semindependent and step makeinvisible
* [FEATURE] The step adminapprove is now a fixed part of the lifecycle plugin
* [FEATURE] Workflowoverview: search field for course lists only in case of paging
* [FEATURE] Workflowoverview: Display number and list of courses which are part of a process already
* [FEATURE] Delayed courses page: confirmation needed when deleting all delays
* [FEATURE] Workflowoverview: display delayed courses; courses lists also for triggers; search function in course lists
* [FEATURE] Workflowoverview draft workflow: Introduce "Activate"-button
* [FEATURE] Administration subpages organized by tabs (#237)
* [FEATURE] Email: Clear separation in courses-list (#231)
* [FEATURE] Fix error when otherindex does not exist (#192)
* [FEATURE] Use field courseid for logging events (#203)
* [FEATURE] Show category of specified level in interaction tables (#216)
* [FEATURE] Email: add ##shortcourses## placeholder (#215)
* [FEATURE] Store log data of sending emails in database (#218)
* [FEATURE] In case the action column is empty display a string (#217)
* [FEATURE] Add checkbox "only once a day" to admin setting for trigger specificdate (#221)
* [FEATURE] Allow steps to run when course no longer exists (#222) (#223)
* [FEATURE] Add validation for step form (#226)
* [FIXED] Add character length input field validation for step and trigger names (#232)
* [FIXED] Capability "viewhiddencourses" is not applied to Lifecycle's "Manage courses" (#234)
* [FIXED] Backups not working if backup_auto_destination for automated backups is set (#233)
* Moodle 4.5 compatible version
