<?php

namespace Vimeo\ABLincoln\Experiments;

use \Psr\Log\LoggerInterface;

class Simple extends AbstractExperiment
{
    protected $logger;
    const LOG_FORMAT = '%s with event type: %s';å

    public function __construct($inputs, LoggerInterface $logger = null)
    {
        parent::__construct($inputs);
        $this->logger = $logger;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function configureLogger() {}

    public function log($data)
    {
        if (isset($logger)) {
            $this->logger->info(sprintf(self::LOG_FORMAT, $this->name,
                                        $data['event']), $data);
        }
    }

    public function previouslyLogged()
    {
        return false;
    }
}