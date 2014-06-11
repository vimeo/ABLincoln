<?php

namespace Vimeo\ABLincoln\Namespaces;

abstract class AbstractNamespace
{
    public function __contruct() {}

    abstract public function inExperiment();

    abstract public function addExperiment($name, $exp_object, $num_segments);

    abstract public function removeExperiment($name);

    abstract public function setAutoExposureLogging($value);

    abstract public function get($name, $default);

    abstract public function logExposure($extras = null);

    abstract public function logEvent($eventType, $extras = null);
}