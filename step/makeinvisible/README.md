# Course Life Cycle (moodle-lifecyclestep_makeinvisible)

This is a step for [Life Cycle](https://github.com/learnweb/moodle-tool_lifecycle), that hides courses.

## Behaviour

For each course, moodle saves the visibility for course (```visibleold```) and category seperately.
The final course visibility (```visible```) will be true, if both ```visibleold``` is true and the category is visible. 
This is done, so that if you hide a course, then hide the category and then show the category again, the course remains hidden.

You can choose to display a course despite it's category being hidden, however, this is disregarded in case of a rollback.

When this step is executed, the original visibility (```visibleold```) of the course is saved.
Then the course will be set to hidden.

In case of a rollback, if the course is still hidden, the courses visibility (```visibleold```) will be restored to it's saved state
and the final visibility (```visible```) will be recalculated.

If a course is visible, this step is executed, you unhide the course, hide it again and then a rollback is performed, 
the visibility of the course will be restored to being shown. 