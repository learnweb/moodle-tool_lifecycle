CHANGELOG
=========


4.5.5 (2025-07-24)
------------------
* [FEATURE] Display trigger countings partial as tooltip; show courses already part of the workflow process or the process errors
* [FEATURE] workflowoverview: show also 0 courses in exclude trigger. Make instancenames in supplugin form of active workflows static
* [FIXED] proceed, rollback event: take course context when context is missing
* [FIXED] Fix trigger customfielddelay's missing field error message
* [FEATURE] Delete all delays: show amount of delays that would be deleted next to button
* [FEATURE] workflowoverview: exclude trigger: show excluded 0 as well
* [FIXED] prozessor.php: restore version 4.5 of function process_courses
* [FIXED] prozessor.php: restore version 4.5 of function call_trigger
* [FEATURE] workflowoverview: place new link to run lifecycle task in timetrigger row
* [FIXED] Fix step email context course id
* [FIXED] call_trigger: mtrace only when called by cron
* [FIXED] Fix behat test interaction.feature
* [FIXED] Step libs' function process_course error: make sure course is of type stdClass
* [FIXED] Fix unit test process_error_test
* [FEATURE] Add additional jobs to run in ci-file
* [FIXED] Fixed error occurring when renaming field manual to manually during the upgrade

4.5.4 (2025-06-23)
------------------
* [FEATURE] No php notice if string plugindescription is missing
* [FIXED] Fix adminapprove sql error single approve
* [FIXED] catch/prevent missing workflow error
* [FIXED] Fix uploadworkflow wrong redirect
* [FEATURE] Workflowoverview: show also delayed trigger courses but only when still in delay
* [FEATURE] Shift showdetails icon to block trigger
* [FEATURE] activestep.php: make approval tab an active link
* [FEATURE] Exclude trigger customfieldsemester from git
* [FIXED] mtrace processes without workflow and delete processes of removed courses
* [FEATURE] Remove customfield_semester dependency
* [FIXED] Improve display of next run time
* [FEATURE] Trigger byrole: introduce invert function
* [FEATURE] Prevent deleting course 1
* [FEATURE] Add step movecategory
* [FEATURE] Add trigger lastaccess
* [FIXED] Fix lastaccess error when no courses found
* [FEATURE] Add filter form to procerror page
* [FEATURE] Categories trigger: instance settings should not be editable
* [FEATURE] Shift subplugins list to own page
* [FIXED] DB field 'manual' now reserved word in mysql version 8.4, change to 'manually'

4.5.0 (2025-05-04)
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
