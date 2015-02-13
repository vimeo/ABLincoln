<?php

namespace Vimeo\ABLincoln\Experiments\Logging;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
use \Monolog\Formatter\LineFormatter;

/**
 * Give experiments the ability to log to files. Configures a Monolog PSR-3
 * file logger and uses it in conjunction with the methods defined in
 * PSRLoggerTrait.
 */
trait FileLoggerTrait
{
    use PSRLoggerTrait;

    // We only want to set up the logger for each experiment once, the first
    // time it's instantiated. We do this by maintaining these class variables.
    protected static $loggers = [];
    protected static $file_paths = [];

    /**
     * Set up Monolog logger to write to file. _configureLogger() only gets
     * called once in AbstractExperiment upon making an experiment assignment
     * so the logger will only be initialized a single time when needed.
     */
    protected function _configureLogger()
    {
        // use previously instantiated logger if already set up
        if (array_key_exists($this->name, self::$loggers)) {
            $this->setLogger(self::$loggers[$this->name]);
            return;
        }

        // if file path not set for experiment default to 'experiment_name.log'
        if (!array_key_exists($this->name, self::$file_paths)) {
            self::$file_paths[$this->name] = $this->name . '.log';
        }

        // create new logger with channel=experiment_name and given level/path
        $logger = new Logger($this->name);
        $handler = new StreamHandler(self::$file_paths[$this->name], $this->log_level);

        // format to ignore empty context + extra arrays
        $handler->setFormatter(new LineFormatter(null, null, false, true));
        $logger->pushHandler($handler);

        $this->setLogger($logger);
        self::$loggers[$this->name] = $logger;
    }

    /**
     * Set the file path to log to - if not given, file name defaults to
     * 'experiment_name.log'. This function should be called in the experiment
     * setup() method so that the file path is set before the logger gets
     * instantiated.
     *
     * @param string $path file path to log to
     */
    public function setLogFile($path)
    {
        self::$file_paths[$this->name] = $path;
    }
}
