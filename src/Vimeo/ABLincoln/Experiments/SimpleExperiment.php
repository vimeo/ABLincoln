<?php

namespace Vimeo\ABLincoln\Experiments;

use \Psr\Log\LoggerInterface;

/**
 * Simple experiment class that exposure logs according to PSR-3 logging
 * specifications. User experiments extending this class should pass in their
 * own compatible logger instance.
 */
abstract class SimpleExperiment
{
    use TraitExperiment;

    /**
     * Construct a new Simple experiment, passing in hashing inputs and logger
     *
     * @param array $inputs array of inputs to use for experiment hashing
     * @param LoggerInterface $logger optional PSR-3 logging instance to use
     */
    public function __construct($inputs, LoggerInterface $logger = null)
    {
        $this->initialize($inputs, $logger);
    }
}