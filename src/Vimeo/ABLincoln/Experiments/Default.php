<?php

namespace Vimeo\ABLincoln\Experiments;

/**
 * Dummy experiment which has no logging. Default experiments used by 
 * namespaces should inherit from this class
 */
class Default extends AbstractExperiment
{
    /**
     * We don't need a logger when there's no experiment
     */
    protected function configureLogger() {}

    /**
     * Don't log anything when there's no experiment
     *
     * @param array $data the data which we will not be logging
     */
    public function log($data) {}

    /**
     * Assume all data passed in has already been logged
     *
     * @return boolean true always since we're assuming data's been logged
     */
    public function previouslyLogged()
    {
        return true;
    }

    /**
     * More complex default experiments can override this method
     *
     * @param Assignment $params assignment in which to place new parameters
     * @param array $inputs input data to determine parameter assignments
     */
    public function assign($params, $inputs)
    {
        for ($this->getDefaultParams() as $key => $val) {
            $params[$key] = $val;
        }
    }

    /**
     * Default experiments that are just key-value stores should override 
     * this method
     *
     * @return array array of default parameters
     */
    public function getDefaultParams()
    {
        return array();
    }
}