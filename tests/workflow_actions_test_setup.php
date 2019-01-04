<?php

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/generator/lib.php');
require_once(__DIR__ . '/../lib.php');

use tool_lifecycle\manager\workflow_manager;
use tool_lifecycle\entity\workflow;

/**
* Tests the different state changes of the workflow sortindex for up and down action.
*
* @package    tool_lifecycle
* @category   test
* @group      tool_lifecycle
* @copyright  2017 Tobias Reischmann WWU
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/


class workflow_actions_test_setup extends \advanced_testcase {
    protected $workflow1;
    protected $workflow2;
    protected $workflow3;

    public function setUp() {
        $this->resetAfterTest(true);
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_lifecycle');

        // Remove preset workflows.
        $workflows = workflow_manager::get_active_automatic_workflows();
        foreach ($workflows as $workflow) {
            workflow_manager::remove($workflow->id, true); // remove() hasn't removed unremovable workflows (like presets) anymore…
        }

        $this->workflow1 = $generator->create_workflow();
        $this->workflow2 = $generator->create_workflow();
        $this->workflow3 = $generator->create_workflow();

        $this->assertFalse($this->workflow1->active);
        $this->assertFalse($this->workflow2->active);
        $this->assertFalse($this->workflow3->active);
        $this->assertNull($this->workflow1->sortindex);
        $this->assertNull($this->workflow2->sortindex);
        $this->assertNull($this->workflow3->sortindex);
    }
}
