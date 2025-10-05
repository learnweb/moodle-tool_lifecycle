# Pushbackuptask (moodle-lifecyclestep_pushbackuptask)

This is a step for [Life Cycle](https://github.com/learnweb/moodle-tool_lifecycle) which does basically the same as the createbackup step but asynchronously by utilizing an adhoc task. The step just pushes one adhoc task for each triggered course. The adhoc task does on execution the same as the createbackup step. That allows the backup processes to run in parallel when cron configured accordingly (see https://docs.moodle.org/401/en/Cron#Scaling_up_cron_with_multiple_processes)

Creation of backups during execution of the adhoc task can be disable by setting `$CFG->custom_no_tool_lifecycle_adhoc_backups = true;`  in `config.php`. Useful for staging environments.
