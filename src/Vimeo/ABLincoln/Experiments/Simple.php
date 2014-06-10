<?php

namespace Vimeo\ABLincoln\Experiments;

use \Psr\Log\LoggerInterface;

/**
 * Simple experiment base class which exposure logs according to PSR logging
 * specifications. User experiments extending this class should pass in their
 * own compatible logger instance.
 */
class Simple extends AbstractExperiment
{
    protected $logger;
    const LOG_FORMAT = '%s with event type: %s';å

    /**
     * Construct a new Simple experiment, passing in hashing inputs and logger
     *
     * @param array $inputs array of inputs to use for experiment hashing
     * @param LoggerInterface $logger optional PSR logging instance to use
     */
    public function __construct($inputs, LoggerInterface $logger = null)
    {
        parent::__construct($inputs);
        $this->logger = $logger;
    }

    /**
     * Set a new PSR logging instance to use for output
     *
     * @param LoggerInterface $logger PSR-compliant logger to use for output
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * All logger configuring will be done outside of this class
     */
    protected function configureLogger() {}

    /**
     * Use the logging instance to log experiment name, event type, and data
     *
     * @param array $data exposure log data to record
     */
    protected function log($data)
    {
        if (isset($logger)) {
            $this->logger->info(sprintf(self::LOG_FORMAT, $this->name,
                                        $data['event']), $data);
        }
    }

    /**
     * Assume data has never been logged before for a Simple experiment
     */
    protected function previouslyLogged()
    {
        return false;
    }
}