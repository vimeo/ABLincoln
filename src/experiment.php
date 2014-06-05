<?php
/*
 * Abstract base class for experiments
 */
abstract class Experiment
{
    protected $inputs;
    protected $logger_configured = False;
    protected $in_experiment = True;

    private $name;
    private $salt = NULL;
    private $exposure_logged = False;
    private $auto_exposure_log = True;
    private $assigned = False;
    private $assignment;
    private $checksum;

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
        $this->checksum = $this->checksum();
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
        if (!self.assigned) {
            self.assignSetup();
        }
    }

    /**
     * Assignment and setup that happens when we need to log data
     */
    private function assignSetup()
    {

    }

    /**
     * Get the assignment belonging to the current experiment
     *
     * @return Assignment the current experiment's asssignment
     */
    public function getAssignment()
    {

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
    private function asBlob($extras = array())
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

    public function checksum()
    {

    }

    /**
     * See whether the experiment has already been exposure logged
     *
     * @return boolean True if exposure logged, False otherwise
     */
    public exposureLogged()
    {
        return $this->exposure_logged;
    }

    /**
     * Set whether the experiment has been exposure logged
     *
     * @param boolean $value True if exposure logged, False otherwise
     */
    public setExposureLogged($value)
    {
        $this->exposure_logged = value;
    }

    /**
     * Disables / enables auto exposure logging (enabled by default)
     *
     * @param boolean $value True to enable, False to disable
     */
    public setAutoExposureLogging($value)
    {
        $this->auto_exposure_log = $value;
    }

    /**
     * Get all experiment parameters - triggers exposure log
     *
     * @return array experiment parameters
     */
    public function getParams()
    {

    }

    /**
     * Get the value of a given experiment parameter - triggers exposure log
     *
     * @param string $name parameter to get the value of
     * @param string $default optional value to return if parameter undefined
     * @return the value of the given parameter
     */
    public function get($name, $default = NULL)
    {

    }

    /**
     * JSON representation of exposure log data - triggers exposure log
     *
     * @return string JSON representation of exposure log data
     */
    public function __toString()
    {
        return json_encode($this->asBlob());
    }

    /**
     * Checks if experiment requires exposure logging, and if so exposure logs
     */
    public function requiresExposureLogging()
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
    public function logExposure($extras = NULL)
    {
        $this->exposureLogged = True;
        $this->logEvent('exposure', $extras);
    }

    /**
     * Log an arbitrary event
     *
     * @param string $eventType name of event to kig]
     * @param array $extras optional extra data to include in log
     */
    public function logEvent($eventType, $extras = NULL)
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
    abstract public function configureLogger();

    /**
     * Log experiment data
     *
     * @param array $data data to log
     */
    abstract public function log($data);

    /**
     * Check if the input has already been logged
     *
     * @return boolean True if previously logged, False otherwise
     */
    abstract public function previouslyLogged();
}