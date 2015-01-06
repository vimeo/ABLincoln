<?php

namespace Vimeo\ABLincoln\Experiments\Logging;

use \Psr\Log\LoggerAwareTrait;

/**
 * Give experiments the ability to log via PSR-3 logging specifications. User
 * experiments utilizing this trait should pass in their own compatible logger
 * instance via setLogger().
 */
trait PSRLoggerTrait
{
    use LoggerAwareTrait;

    protected $log_level = 'info';
    protected $ALLOWED_LOG_LEVELS = array('emergency', 'alert', 'critical', 'error',
                                          'warning', 'notice', 'info', 'debug');

    /**
     * Set the level at which to log, which must be one of the constants
     * defined in the PSR\Log\LogLevel class. Should be called in the
     * experiment setup() method so that the level is set before the logger
     * gets instantiated.
     *
     * @param string $level PSR level at which to log
     */
    public function setLogLevel($level)
    {
        if (!in_array($level, $this->ALLOWED_LOG_LEVELS)) {
            throw new \Exception(get_class($this) . ": 'level' must be one of the constants defined in PSR\Log\LogLevel, not $level.");
        }

        $this->log_level = $level;
    }

    /**
     * PSR logger configuration can either be done outside of the experiment
     * class or within this method if overriden. Either way, the initialized
     * logger should be passed to the experiment via the LoggerAwareTrait's
     * setLogger() method.
     */
    protected function _configureLogger() {}

    /**
     * Use the logging instance to log an optional message as well as exposure
     * data. The log level may be set by the included setLogLevel() method.
     *
     * @param array $data exposure log data to record
     */
    protected function _log($data)
    {
        if (isset($this->logger)) {
            $this->logger->log($this->log_level, json_encode($data));
        }
    }

    /**
     * Assume data has never been logged before and this is the first time we
     * are seeing the inputs/outputs given (can be overriden if needed).
     */
    protected function _previouslyLogged()
    {
        return false;
    }
}
