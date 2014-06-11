<?php

namespace Vimeo\ABLincoln\Namespaces;

use \Vimeo\ABLincoln\Assignment;
use \Vimeo\ABLincoln\Experiments\DefaultExperiment;
use \Vimeo\ABLincoln\Operators\Random\Sample;

class SimpleNamespace extends AbstractNamespace
{
    protected $name;
    protected $inputs;
    protected $primary_unit;
    protected $num_segments;
    
    private $experiment;
    private $default_experiment;
    private $default_experiment_class;
    private $in_experiment;
    private $current_experiments;

    private $available_segments;
    private $segment_allocations;

    public function __construct($inputs)
    {
        $this->inputs = $inputs;         // input data
        $this->name = get_class($this);  // use class name as default name
        $this->num_segments = null;      // num_segments set in setup()
        $this->in_experiment = false;    // not in experiment until unit assigned

        // array mapping segments to experiment names
        $this->segment_allocations = array();  // map segmnents to experiment names

        // array mapping experiment names to experiment objects
        $this->current_experiments = array();

        $this->experiment = null;          // memoized experiment object
        $this->default_experiment = null;  // memoized default experiment object
        $this->default_experiment_class = 'DefaultExperiment';

        // setup name, primary key, number of segments, etc
        $this->setup();
        $this->available_segments = range(0, $this->num_segments - 1);
        
        $this->setupExperiments();  // load namespace with experiments
    }

    abstract protected function setup();

    abstract protected function setupExperiments();

    public function primaryUnit()
    {
        return $this->primary_unit;
    }

    public function setPrimaryUnit($value)
    {
        $unit = isArray($value) ? $value : array($value);
    }

    public function addExperiment($name, $exp_class, $num_segments)
    {
        $num_available = count($this->available_segments);
        if ($num_available < $num_segments) {
            return;  // more segments requested than available, exit
        }
        if (array_key_exists($name, $this->current_experiments)) {
            return;  // experiment name collision, exit
        }

        // randomly select the given numer of segments from all available options
        $a = new Assignment($this->name);
        $a['sampled_segments'] = new Sample(array(
            'choices' => $this->available_segments,
            'draws' => $num_segments,
            'unit' => $name
        ));

        // assign each segment to the experiment name
        foreach ($a['sampled_segments'] as $key => $segment) {
            $this->segment_allocations[$segment] = $name;
            unset($this->available_segments[$segment]);
        }

        // associate the experiment name with a class to instantiate
        $this->current_experiments[$name] = $exp_class;
    }

    public function removeExperiment($name)
    {
        if (!array_key_exists($name, $this->current_experiments)) {
            return;  // given experiment not currently running
        }

        // make segments available for allocation again, remove experiment name
        foreach ($this->segment_allocations as $segment => $exp_name) {
            if (!strcmp($exp_name, $name)) {
                unset($this->segment_allocations[$segment]);
                $this->available_segments[$segment] = $segment;
            }
        }
        unset($this->current_experiments[$name]);
    }

    private function getSegment()
    {
        $a = new Assignment($this->name);
        $a['segment'] = new RandomInteger(array(
            'min' => 0,
            'max' => $this->num_segments - 1,
            'unit' => $this->inputs[$this->primary_unit]
        ));
        return $a['segment'];
    }

    protected function requiresExperiment()
    {
        if (!isset($this->experiment)) {
            $this->assignExperiment();
        }
    }

    protected function requiresDefaultExperiment()
    {
        if (!isset($this->default_experiment)) {
            $this->assignDefaultExperiment();
        }
    }

    private function assignExperiment()
    {
        $segment = $this->getSegment();

        // is the unit allocated to an experiment?
        if (array_key_exists($segment, $this->segment_allocations)) {
            $exp_name = $this->segment_allocations[$segment];
            $experiment = new $this->current_experiments[$exp_name]($this->inputs);
            $experiment.setName("{$this->name}-{$exp_name}");
            $experiment.setSalt("{$this->name}.{$exp_name}");
            $this->experiment = $experiment;
            $this->in_experiment = $experiment->inExperiment();
        }
        else {
            $this->assignDefaultExperiment();
            $this->in_experiment = false;
        }
    }

    private function assignDefaultExperiment()
    {
        $this->default_experiment = $this->default_experiment_class($this->inputs);
    }

    public function inExperiment()
    {
        $this->requiresExperiment();
        return $this->in_experiment;
    }

    public function get($name, $default = null)
    {
        $this->requiresExperiment();
        if (!isset($this->experiment)) {
            return $this->defaultGet($name, $default);
        }
        return $this->experiment->get($name, $this->defaultGet($name, $default));
    }

    private function defaultGet($name, $default = null)
    {
        $this->requiresDefaultExperiment();
        return $this->default_experiment->get($name, $default);
    }

    public function setAutoExposureLogging($value)
    {
        $this->requiresExperiment();
        if (isset($this->experiment)) {
            $this->experiment->setAutoExposureLogging($value);
        }
    }

    public function logExposure($extras = null)
    {
        $this->requiresExperiment();
        if (isset($this->experiment)) {
            $this->experiment->logExposure($extras);
        }
    }

    public function logEvent($event_type, $extras = null)
    {
        $this->requiresExperiment();
        if (isset($this->experiment)) {
            $this->experiment->logEvent($event_type, $extras);
        }
    }
}