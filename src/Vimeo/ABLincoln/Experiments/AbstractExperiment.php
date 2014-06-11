<?php

namespace Vimeo\ABLincoln\Experiments;

use \Vimeo\ABLincoln\Assignment;

/*
 * Abstract base class for experiments
 */
abstract class AbstractExperiment
{
    protected $inputs;
    protected $logger_configured = false;
    protected $in_experiment = true;

    private $name;
    private $salt = null;
    private $exposure_logged = false;
    private $auto_exposure_log = true;
    private $assigned = false;
    private $assignment;

    /**
     * Set up attributes needed for experiment
     *
     * @param array $inputs input data to determine parameter assignments, e.g. userid
     */
    public function __construct($inputs)
    {
        $this->inputs = $inputs;         // input data
        $this->name = get_class($this);  // use class name as default name
        $this->setup();                  // manually set name, salt, etc.

        $this->assignment = $this->getAssignment();
    }

    /*
     * Optionally set experiment attributes before run, e.g. name and salt
     */
    public function setup() {}

    /**
     * Checks if an assignment has been made, assigns one if not
     */
    private function requiresAssignment()
    {
        if (!$this->assigned) {
            $this->assignSetup();
        }
    }

    /**
     * Assignment and setup that happens when we need to log data
     */
    private function assignSetup()
    {
        $this->configureLogger();
        $this->assign($this->assignment, $this->inputs);
        $this->in_experiment = isset($this->assignment['in_experiment']) ? 
                $this->assignment['in_experiment'] : $this->in_experiment;
        $this->logged = $this->previouslyLogged();
    }

    /**
     * Get a new assignment belonging to the current experiment
     *
     * @return Assignment the current experiment's assignment
     */
    private function getAssignment()
    {
        return new Assignment($this->salt);
    }

    /**
     * Add parameters used in experiment to current assignment
     *
     * @param Assignment $params assignment in which to place new parameters
     * @param array $inputs input data to determine parameter assignments
     */
    abstract public function assign($params, $inputs);

    /**
     * Experiment-level salt accessor
     *
     * @return string the experiment-level salt
     */
    public function salt()
    {
        return isset($this->salt) ? $this->salt : $this->name;
    }

    /**
     * Experiment-level salt setter
     *
     * @param string $value value to set the experiment-level salt
     */
    public function setSalt($value)
    {
        $this->salt = $value;
    }

    /**
     * Experiment name accessor
     *
     * @return string the experiment name
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Experiment name setter
     *
     * @param string $value value to set the experiment name
     */
    public function setName($value)
    {
        $this->name = preg_replace('/\s+/', '-', $value);
    }

    /**
     * Get an array representation of the experiment data
     *
     * @param array $extras extra data to include in array
     * @return array experiment data
     */
    protected function asBlob($extras = array())
    {
        $ret = array(
            'name' => $this->name,
            'time' => time(),
            'salt' => $this->salt,
            'inputs' => $this->inputs
        );
        foreach ($extras as $key => $val) {
            $ret[$key] = $val;
        }
        return $ret;
    }

    /**
     * See whether the experiment has already been exposure logged
     *
     * @return boolean true if exposure logged, false otherwise
     */
    public function exposureLogged()
    {
        return $this->exposure_logged;
    }

    /**
     * Set whether the experiment has been exposure logged
     *
     * @param boolean $value true if exposure logged, false otherwise
     */
    public function setExposureLogged($value)
    {
        $this->exposure_logged = value;
    }

    /**
     * Disables / enables auto exposure logging (enabled by default)
     *
     * @param boolean $value true to enable, false to disable
     */
    public function setAutoExposureLogging($value)
    {
        $this->auto_exposure_log = $value;
    }

    /**
     * Get all experiment parameters - triggers exposure log. In general, this
     * should only be used by custom loggers
     *
     * @return array experiment parameters
     */
    public function getParams()
    {
        $this->requiresAssignment();
        $this->requiresExposureLogging();
        return $this->assignment->asArray();
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
        $this->requiresAssignment();
        $this->requiresExposureLogging();
        return isset($this->assignment[$name]) ? $this->assignment[$name]
                                               : $default;
    }

    /**
     * JSON representation of exposure log data - triggers exposure log
     *
     * @return string JSON representation of exposure log data
     */
    public function __toString()
    {
        $this->requiresAssignment();
        $this->requiresExposureLogging();
        return json_encode($this->asBlob());
    }

    /**
     * Checks if experiment requires exposure logging, and if so exposure logs
     */
    protected function requiresExposureLogging()
    {
        if ($this->auto_exposure_log && $this->in_experiment 
                                     && !$this->exposure_logged) {
            $this->logExposure();
        }
    }

    /**
     * Logs exposure to treatment
     *
     * @param array $extras optional extra data to include in exposure log
     */
    public function logExposure($extras = null)
    {
        $this->logEvent('exposure', $extras);
        $this->exposureLogged = true;
    }

    /**
     * Log an arbitrary event
     *
     * @param string $eventType name of event to kig]
     * @param array $extras optional extra data to include in log
     */
    public function logEvent($eventType, $extras = null)
    {
        if (isset($extras)) {
            $extraPayload = array('event' => $eventType, 'extra_data' => $extras);
        }
        else {
            $extraPayload = array('event' => $eventType);
        }
        $this->log($this->asBlob($extraPayload));
    }

    /**
     * Set up files, database connections, sockets, etc for logging
     */
    abstract protected function configureLogger();

    /**
     * Log experiment data
     *
     * @param array $data data to log
     */
    abstract protected function log($data);

    /**
     * Check if the input has already been logged
     *
     * @return boolean true if previously logged, false otherwise
     */
    abstract protected function previouslyLogged();
}