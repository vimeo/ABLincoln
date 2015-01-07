<?php

namespace Vimeo\ABLincoln\Experiments;

use \Vimeo\ABLincoln\Assignment;

/*
 * Abstract base class used in all experiments.
 */
abstract class AbstractExperiment
{
    protected $name;
    protected $salt = null;

    protected $inputs;
    protected $logger_configured = false;
    protected $in_experiment = true;
    protected $assignment;

    private $logged;
    private $exposure_logged = false;
    private $auto_exposure_log = true;
    private $assigned = false;

    /**
     * Set up attributes needed for experiment
     *
     * @param mixed $inputs input value or array to determine parameter assignments, e.g. userid
     */
    public function __construct($inputs)
    {
        $this->inputs = $inputs;         // input data
        $this->name = get_class($this);  // use class name as default name
        $this->setup();                  // manually set name, salt, etc.
        $this->salt = $this->salt();     // salt defaults to experiment name

        $this->assignment = $this->_getAssignment();
    }

    /*
     * Optionally set experiment attributes before run, e.g. name and salt
     */
    public function setup() {}

    /**
     * Checks if an assignment has been made, assigns one if not
     */
    private function _requiresAssignment()
    {
        if (!$this->assigned) {
            $this->_assignSetup();
        }
    }

    /**
     * Assignment and setup that happens when we need to log data
     */
    private function _assignSetup()
    {
        $this->_configureLogger();
        $this->assign($this->assignment, $this->inputs);
        $this->in_experiment = array_key_exists('in_experiment', $this->assignment->asArray()) ? $this->assignment['in_experiment'] : $this->in_experiment;
        $this->logged = $this->_previouslyLogged();
        $this->assigned = true;
    }

    /**
     * Get a new assignment belonging to the current experiment
     *
     * @return Assignment the current experiment's assignment
     */
    private function _getAssignment()
    {
        return new Assignment($this->salt());
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
        return is_null($this->salt) ? $this->name : $this->salt;
    }

    /**
     * Experiment-level salt setter
     *
     * @param string $value value to set the experiment-level salt
     */
    public function setSalt($value)
    {
        $this->salt = $value;
        $this->assignment = $this->_getAssignment();
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
        $this->assignment = $this->_getAssignment();
    }

    /**
     * In-experiment accessor
     *
     * @return boolean true if currently in experiment, false otherwise
     */
    public function inExperiment()
    {
        return $this->in_experiment;
    }

    /**
     * In-experiment setter
     *
     * @param boolean $value true if currently in experiment, false otherwise
     */
    public function setInExperiment($value)
    {
        $this->in_experiment = $value;
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
     * Get an array representation of the experiment data
     *
     * @param array $extras extra data to include in array
     * @return array experiment data
     */
    protected function _asBlob($extras = [])
    {
        $this->_requiresAssignment();
        $ret = [
            'name' => $this->name,
            'time' => time(),
            'salt' => $this->salt(),
            'inputs' => $this->inputs,
            'params' => $this->assignment->asArray()
        ];
        return array_merge($ret, $extras);
    }

    /**
     * Get all experiment parameters - triggers exposure log. In general, this
     * should only be used by custom loggers
     *
     * @return array experiment parameters
     */
    public function getParams()
    {
        $this->_requiresAssignment();
        $this->_requiresExposureLogging();
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
        $this->_requiresAssignment();
        $this->_requiresExposureLogging();
        return array_key_exists($name, $this->assignment->asArray()) ? $this->assignment->$name : $default;
    }

    /**
     * JSON representation of exposure log data - triggers exposure log
     *
     * @return string JSON representation of exposure log data
     */
    public function __toString()
    {
        $this->_requiresAssignment();
        $this->_requiresExposureLogging();
        return json_encode($this->_asBlob());
    }

    /**
     * Checks if experiment requires exposure logging, and if so exposure logs
     */
    protected function _requiresExposureLogging()
    {
        if ($this->auto_exposure_log && $this->in_experiment && !$this->exposure_logged) {
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
        $this->exposure_logged = true;
    }

    /**
     * Log an arbitrary event
     *
     * @param string $eventType name of event to log
     * @param array $extras optional extra data to include in log
     */
    public function logEvent($eventType, $extras = null)
    {
        if (!is_null($extras)) {
            $extraPayload = ['event' => $eventType, 'extra_data' => $extras];
        }
        else {
            $extraPayload = ['event' => $eventType];
        }
        $this->_log($this->_asBlob($extraPayload));
    }

    /**
     * Set up files, database connections, sockets, etc for logging
     */
    abstract protected function _configureLogger();

    /**
     * Log experiment data
     *
     * @param array $data data to log
     */
    abstract protected function _log($data);

    /**
     * Check if the input has already been logged. Gets called once in the
     * constructor
     *
     * @return boolean true if previously logged, false otherwise
     */
    abstract protected function _previouslyLogged();
}
