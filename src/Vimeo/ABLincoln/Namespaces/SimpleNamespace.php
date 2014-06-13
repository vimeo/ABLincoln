<?php

namespace Vimeo\ABLincoln\Namespaces;

use \Vimeo\ABLincoln\Assignment;
use \Vimeo\ABLincoln\Operators\Random as Random;

abstract class SimpleNamespace extends AbstractNamespace
{
    protected $_name;
    protected $_inputs;
    protected $_primary_unit;
    protected $_num_segments;
    
    private $_experiment;
    private $_default_experiment;
    private $_default_experiment_class;
    private $_in_experiment;
    private $_current_experiments;

    private $_available_segments;
    private $_segment_allocations;

    /**
     * Set up attributes needed for the namespace
     *
     * @param array $inputs data to determine parameter assignments, e.g. userid
     */
    public function __construct($inputs)
    {
        $this->_inputs = $inputs;         // input data
        $this->_name = get_class($this);  // use class name as default name
        $this->_num_segments = null;      // num_segments set in setup()
        $this->_in_experiment = false;    // not in experiment until unit assigned

        // array mapping segments to experiment names
        $this->_segment_allocations = array();

        // array mapping experiment names to experiment objects
        $this->_current_experiments = array();

        $this->_experiment = null;          // memoized experiment object
        $this->_default_experiment = null;  // memoized default experiment object
        $this->_default_experiment_class = 'Vimeo\ABLincoln\Experiments\DefaultExperiment';

        // setup name, primary key, number of segments, etc
        $this->_setup();
        $this->_available_segments = range(0, $this->_num_segments - 1);
        
        $this->_setupExperiments();  // load namespace with experiments
    }

    /**
     * Set namespace attributes for run. Developers extending this class should
     * set the following variables:
     *     this->name = 'sample namespace';
     *     this->primary_unit = 'userid';
     *     this->num_segments = 10000;
     */
    abstract protected function _setup();

    /**
     * Setup experiments segments will be assigned to:
     *     $this->addExperiment('first experiment', Exp1, 100);
     */
    abstract protected function _setupExperiments();

    /**
     * Get the primary unit that will be mapped to segments
     *
     * @return array array containing value(s) used for unit assignment
     */
    public function primaryUnit()
    {
        return $this->_primary_unit;
    }

    /**
     * Set the primary unit that will be used to map to segments
     *
     * @param mixed $value value or array used for unit assignment to segments
     */
    public function setPrimaryUnit($value)
    {
        $unit = is_array($value) ? $value : array($value);
    }

    /**
     * In-experiment accessor
     *
     * @return boolean true if primary unit mapped to an experiment, false otherwise
     */
    public function inExperiment()
    {
        $this->_requiresExperiment();
        return $this->_in_experiment;
    }

    /**
     * Map a new experiment to a given number of segments in the namespace
     *
     * @param string $name name to give the new experiment
     * @param string $exp_class string version of experiment class to instantiate
     * @param int $num_segments number of segments to allocate to experiment
     */
    public function addExperiment($name, $exp_class, $num_segments)
    {
        $num_available = count($this->_available_segments);
        if ($num_available < $num_segments) {
            return;  // more segments requested than available, exit
        }
        if (array_key_exists($name, $this->_current_experiments)) {
            return;  // experiment name collision, exit
        }

        // randomly select the given numer of segments from all available options
        $assignment = new Assignment($this->_name);
        $assignment['sampled_segments'] = new Random\Sample(
            array('choices' => $this->_available_segments, 'draws' => $num_segments),
            array('unit' => $name)
        );

        // assign each segment to the experiment name
        foreach ($assignment['sampled_segments'] as $key => $segment) {
            $this->_segment_allocations[$segment] = $name;
            unset($this->_available_segments[$segment]);
        }

        // associate the experiment name with a class to instantiate
        $this->_current_experiments[$name] = $exp_class;
    }

    /**
     * Remove a given experiment from the namespace and free its associated segments
     *
     * @param string $name previously defined name of experiment to remove
     */
    public function removeExperiment($name)
    {
        if (!array_key_exists($name, $this->_current_experiments)) {
            return;  // given experiment not currently running
        }

        // make segments available for allocation again, remove experiment name
        foreach ($this->_segment_allocations as $segment => $exp_name) {
            if ($exp_name === $name) {
                unset($this->_segment_allocations[$segment]);
                $this->_available_segments[$segment] = $segment;
            }
        }
        unset($this->_current_experiments[$name]);

        // currently assigned experiment just deleted!
        if ($this->_experiment->name() === $this->_name . '-' . $name) {
            $this->_experiment = null;
            $this->_in_experiment = false;
        }
    }

    /**
     * Use the primary unit value(s) to obtain a segment and associated experiment
     *
     * @return int the segment corresponding to the primary unit value(s)
     */
    private function _getSegment()
    {
        $assignment = new Assignment($this->_name);
        $assignment['segment'] = new Random\RandomInteger(
            array('min' => 0, 'max' => $this->_num_segments - 1),
            array('unit' => $this->_inputs[$this->_primary_unit])
        );
        return $assignment['segment'];
    }

    /**
     * Checks if primary unit segment is assigned to an experiment, 
     * and if not assigns it to one
     */
    protected function _requiresExperiment()
    {
        if (!isset($this->_experiment)) {
            $this->_assignExperiment();
        }
    }

    /**
     * Checks if primary unit segment is assigned to a default experiment,
     * and if not assigns it to one
     */
    protected function _requiresDefaultExperiment()
    {
        if (!isset($this->default_experiment)) {
            $this->_assignDefaultExperiment();
        }
    }

    /**
     * Assigns the primary unit value(s) and associated segment to a new 
     * experiment and updates the experiment name/salt accordingly
     */
    private function _assignExperiment()
    {
        $segment = $this->_getSegment();

        // is the unit allocated to an experiment?
        if (array_key_exists($segment, $this->_segment_allocations)) {
            $exp_name = $this->_segment_allocations[$segment];
            $experiment = new $this->_current_experiments[$exp_name]($this->_inputs);
            $experiment->setName($this->_name . '-' . $exp_name);
            $experiment->setSalt($this->_name . '.' . $exp_name);
            $this->_experiment = $experiment;
            $this->_in_experiment = $experiment->inExperiment();
        }
        else {
            $this->_assignDefaultExperiment();
            $this->_in_experiment = false;
        }
    }

    /**
     * Assigns the primary unit value(s) and associated segment to a new
     * default experiment used if segment not assigned to a real one
     */
    private function _assignDefaultExperiment()
    {
        $this->_default_experiment = new $this->_default_experiment_class($this->_inputs);
    }

    /**
     * Get the value of a given experiment parameter - triggers exposure log
     *
     * @param string $name parameter to get the value of
     * @param string $default optional value to return if parameter undefined
     * @return the value of the given parameter
     */
    public function get($name, $default = null)
    {
        $this->_requiresExperiment();
        if (!isset($this->_experiment)) {
            return $this->_defaultGet($name, $default);
        }
        return $this->_experiment->get($name, $this->_defaultGet($name, $default));
    }

    /**
     * Get the value of a given default experiment parameter. Called on get()
     * if primary unit value(s) not mapped to a real experiment
     *
     * @param string $name parameter to get the value of
     * @param string $default optional value to return if parameter undefined
     * @return the value of the given parameter
     */
    private function _defaultGet($name, $default = null)
    {
        $this->_requiresDefaultExperiment();
        return $this->_default_experiment->get($name, $default);
    }

    /**
     * Disables / enables auto exposure logging (enabled by default)
     *
     * @param boolean $value true to enable, false to disable
     */
    public function setAutoExposureLogging($value)
    {
        $this->_requiresExperiment();
        if (isset($this->_experiment)) {
            $this->_experiment->setAutoExposureLogging($value);
        }
    }

    /**
     * Logs exposure to treatment
     *
     * @param array $extras optional extra data to include in exposure log
     */
    public function logExposure($extras = null)
    {
        $this->_requiresExperiment();
        if (isset($this->_experiment)) {
            $this->_experiment->logExposure($extras);
        }
    }

    /**
     * Log an arbitrary event
     *
     * @param string $eventType name of event to kig]
     * @param array $extras optional extra data to include in log
     */
    public function logEvent($event_type, $extras = null)
    {
        $this->_requiresExperiment();
        if (isset($this->_experiment)) {
            $this->_experiment->logEvent($event_type, $extras);
        }
    }
}