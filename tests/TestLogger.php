<?php

use \Psr\Log\AbstractLogger;

class TestLogger extends AbstractLogger
{
    public $log = [];

    public function log($level, $message, array $context = array())
    {
        $this->log[] = $message;
    }
}
