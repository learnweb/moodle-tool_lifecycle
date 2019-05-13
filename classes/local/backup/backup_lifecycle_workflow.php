<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_lifecycle\local\backup;

use tool_lifecycle\entity\step_subplugin;
use tool_lifecycle\entity\trigger_subplugin;
use tool_lifecycle\entity\workflow;
use tool_lifecycle\manager\workflow_manager;
use tool_lifecycle\manager\step_manager;
use tool_lifecycle\manager\trigger_manager;
use tool_lifecycle\manager\settings_manager;

defined('MOODLE_INTERNAL') || die();

class backup_lifecycle_workflow {

    /** @var $workflow workflow */
    private $workflow;
    /** @var $steps step_subplugin[] */
    private $steps;
    /** @var $trigger trigger_subplugin[] */
    private $trigger;
    /** @var $writer XMLWriter */
    private $writer;
    /** @var $tempfilename string */
    private $tempfilename;

    public function __construct($workflowid) {
        $this->workflow = workflow_manager::get_workflow($workflowid);
        $this->steps = step_manager::get_step_instances($workflowid);
        $this->trigger = trigger_manager::get_triggers_for_workflow($workflowid);
    }

    /**
     * Executes the backup process. It write the workflow with all steps and triggers to a xml file.
     * Afterwards send_temp_file should be called, which sends the file to the client.
     * @throws \moodle_exception
     */
    public function execute() {
        global $CFG;
        make_temp_directory('lifecycle');
        $this->tempfilename = $CFG->tempdir .'/lifecycle/'. md5(sesskey().microtime());
        if (!$handle = fopen($this->tempfilename, 'w+b')) {
            print_error('cannotcreatetempdir');
        }
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->startDocument();
        $this->writer->startElement("workflow");
        $this->write_workflow();
        $this->write_steps();
        $this->write_triggers();
        $this->writer->endElement();
        $this->writer->endDocument();
        fwrite($handle,  $this->writer->flush());
        fclose($handle);
    }

    /**
     * This sends the created tempfile to the client.
     */
    public function send_temp_file() {
        if (!$this->tempfilename) {
            throw new \moodle_exception('There is no tempfile yet. Call execute first!');
        }
        header('Content-type: text/xml');
        @header("Content-type: text/xml; charset=UTF-8");
        send_temp_file($this->tempfilename, 'myfile.xml', false);
        die();
    }


    /**
     * Write the workflow with all its attributes to the xmlwriter.
     */
    private function write_workflow() {
        foreach (get_object_vars($this->workflow) as $prop => $value) {
            $this->writer->writeAttribute($prop, $value);
        }
    }

    /**
     * Write all trigger of the workflow with all their attributes to the xmlwriter.
     */
    private function write_triggers() {
        foreach ($this->trigger as $trigger) {
            $this->writer->startElement("trigger");
            foreach (get_object_vars($trigger) as $prop => $value) {
                $this->writer->writeAttribute($prop, $value);
            }
            $settings = settings_manager::get_settings($trigger->id, SETTINGS_TYPE_TRIGGER);
            foreach ($settings as $name => $value) {
                $this->writer->startElement("setting");
                $this->writer->writeAttribute('name', $name);
                $this->writer->writeAttribute('value', $value);
                $this->writer->endElement();
            }
            $this->writer->endElement();
        }
    }


    /**
     * Write all steps of the workflow with all their attributes to the xmlwriter.
     */
    private function write_steps() {
        foreach ($this->steps as $step) {
            $this->writer->startElement("step");
            foreach (get_object_vars($step) as $prop => $value) {
                $this->writer->writeAttribute($prop, $value);
            }
            $settings = settings_manager::get_settings($step->id, SETTINGS_TYPE_STEP);
            foreach ($settings as $name => $value) {
                $this->writer->startElement("setting");
                $this->writer->writeAttribute('name', $name);
                $this->writer->writeAttribute('value', $value);
                $this->writer->endElement();
            }
            $this->writer->endElement();
        }
    }

    public function get_temp_filename() {
        return $this->tempfilename;
    }
}