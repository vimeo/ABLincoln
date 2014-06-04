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

    public function __construct($inputs)
    {
        $this->inputs = $inputs;
        $this->name = get_class();

        $this->setup();
    }

    abstract public function setup();

    public function salt()
    {
        return isset($this->salt) ? $this->salt : $this->name;
    }

    public function setSalt($value)
    {
        $this->salt = $value;
    }

    public function name()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = preg_replace('/\s+/', '-', $value);
    }

    private function asBlob($extras = array())
    {
        $ret = array(
            'name' => $this->name,
            'time' => 0,
            'salt' => $this->salt,
            'inputs' => $this->inputs
        );
        foreach ($extras as $key => $val) {
            $ret[$key] = $val;
        }
        return $ret;
    }
}

//$exp = new Experiment(array("id" => 4453));