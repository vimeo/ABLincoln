<?php

namespace Vimeo\ABLincoln\Namespaces;

abstract class AbstractNamespace
{
    /**
     * Set up attributes needed for the namespace
     *
     * @param array $inputs data to determine parameter assignments, e.g. userid
     */
    abstract public function __contruct($inputs);

    /**
     * In-experiment accessor
     *
     * @return boolean true if primary unit mapped to an experiment, false otherwise
     */
    abstract public function inExperiment();

    /**
     * Map a new experiment to a given number of segments in the namespace
     *
     * @param string $name name to give the new experiment
     * @param string $exp_class string version of experiment class to instantiate
     * @param int $num_segments number of segments to allocate to experiment
     */
    abstract public function addExperiment($name, $exp_class, $num_segments);

    /**
     * Remove a given experiment from the namespace and free its associated segments
     *
     * @param string $name previously defined name of experiment to remove
     */
    abstract public function removeExperiment($name);

    /**
     * Get the value of a given experiment parameter - triggers exposure log
     *
     * @param string $name parameter to get the value of
     * @param string $default optional value to return if parameter undefined
     * @return the value of the given parameter
     */
    abstract public function get($name, $default);

    /**
     * Disables / enables auto exposure logging (enabled by default)
     *
     * @param boolean $value true to enable, false to disable
     */
    abstract public function setAutoExposureLogging($value);

    /**
     * Logs exposure to treatment
     *
     * @param array $extras optional extra data to include in exposure log
     */
    abstract public function logExposure($extras = null);

    /**
     * Log an arbitrary event
     *
     * @param string $eventType name of event to kig]
     * @param array $extras optional extra data to include in log
     */
    abstract public function logEvent($eventType, $extras = null);
}