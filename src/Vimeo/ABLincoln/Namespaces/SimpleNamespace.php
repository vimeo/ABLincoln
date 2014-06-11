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
    private $current_experiments;

    private $available_segments;
    private $segment_allocations;

    public function __construct($inputs)
    {
        $this->inputs = $inputs;         // input data
        $this->name = get_class($this);  // use class name as default name
        $this->num_segments = null;      // num_segments set in setup()

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

    public function addExperiment($name, $exp_object, $num_segments)
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
            'draws' => $num_segments
        ));

        // assign each segment to the experiment name
        foreach ($a['sampled_segments'] as $key => $segment) {
            $this->segment_allocations[$segment] = $name;
            unset($this->available_segments[$segment]);
        }
    }
}