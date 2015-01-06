<?php

namespace Vimeo\ABLincoln\Experiments\Logging;

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

trait FileLoggerTrait
{
    use PSRLoggerTrait;

    protected $file_path = null;

    /**
     * Setup Monolog logger to write to file. _configureLogger() only gets
     * called once in AbstractExperiment upon making an experiment assignment
     * so the logger will only be initialized a single time when needed.
     */
    protected function _configureLogger()
    {
        if (is_null($this->file_path)) {
            $this->file_path = $this->name . '.log';
        }

        $logger = new Logger($this->name);
        $logger->pushHandler(new StreamHandler($this->file_path, $this->log_level));
        $this->setLogger($logger);
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
        $this->file_path = $path;
    }
}
