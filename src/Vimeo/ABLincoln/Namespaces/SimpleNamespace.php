<?php

namespace Vimeo\ABLincoln\Namespaces;

use \Psr\Log\LoggerInterface;

/**
 * Simple namespace base class that handles user assignment when dealing with
 * multiple concurrent experiments. Exposure logs according to PSR-3 logging
 * specifications. User experiments extending this class should pass in their
 * own compatible logger instance.
 */
abstract class SimpleNamespace
{
    use TraitNamespace;

    /**
     * Set up attributes needed for the namespace
     *
     * @param array $inputs data to determine parameter assignments, e.g. userid
     * @param LoggerInterface $logger optional PSR-3 logging instance to use
     */
    public function __construct($inputs, LoggerInterface $logger = null)
    {
        $this->initialize($inputs, $logger);
    }
}