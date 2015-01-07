<?php

namespace Vimeo\ABLincoln\Experiments;

/*
 * Simple experiment base class which exposure logs to a file.
 */
abstract class SimpleExperiment extends AbstractExperiment
{
    use Logging\FileLoggerTrait;
}
