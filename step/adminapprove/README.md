# Adminapprove (moodle-lifecyclestep_adminapprove)

This is a step for [Life Cycle](https://github.com/learnweb/moodle-tool_lifecycle), in which admins can manually decide for every course, whether the course should proceed or roll back.

This is meant as a safeguard, so that admins can check whether their workflows are working correctly.

![Adminapprove table](https://raw.githubusercontent.com/justusdieckmann/images/master/lifecyclestep_adminapprove.png);

## Behaviour
Each cron-job, all courses that are processed in a adminapprove step get added to the corresponding step table and the admin is notified.

If the admin marks courses to be proceeded / rolled back, they are removed from the step table and they are proceeded / rolled back during the next cron job.