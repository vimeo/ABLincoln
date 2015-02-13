# ABLincoln [![Latest Stable Version][version image]][packagist link] [![Build Status][build image]][build link]

[version image]: https://poser.pugx.org/vimeo/ablincoln/v/stable.svg
[packagist link]: https://packagist.org/packages/vimeo/ablincoln
[build image]: https://travis-ci.org/vimeo/ABLincoln.svg?branch=master
[build link]: https://travis-ci.org/vimeo/ABLincoln

ABLincoln is a PHP-based implementation of Facebook's [PlanOut], a framework
for online field experimentation. ABLincoln makes it easy to deploy and
maintain sophisticated randomized experiments and to quickly iterate on these
experiments, while satisfying the constraints of large-scale Internet services
with many users.

[PlanOut]: http://facebook.github.io/planout/

Developers integrate ABLincoln by defining experiments that detail how _inputs_
(e.g. users, cookie IDs) should get mapped onto conditions. To set up an
experiment randomizing both the text and color of a button, you would create a
class like this:

```php
use \Vimeo\ABLincoln\Experiments\SimpleExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;

class MyExperiment extends SimpleExperiment
{
    public function assign($params, $inputs)
    {
        $params->button_color = new Random\UniformChoice(
            ['choices' => ['#ff0000', '#00ff00']],
            $inputs
        );
        $params->button_text = new Random\WeightedChoice(
            [
                'choices' => ['Join now!', 'Sign up.'],
                'weights' => [0.3, 0.7]
            ],
            $inputs
        );
    }
}
```

Then, in the application code, you query the Experiment object to find out what
values the current user should be mapped onto:

```php
$my_exp = new MyExperiment(['userid' => 42]);
$my_exp->get('button_color');
$my_exp->get('button_text');
```

Querying the experiment parameters automatically generates an exposure log that
we can direct to a location of our choice:

```
{"name": "MyExperiment", "time": 1421622363, "salt": "MyExperiment", "inputs": {"userid": 42}, "params": {"button_color": "#ff0000", "button_text": "Join now!"}, "event": "exposure"}
```

The basic `SimpleExperiment` class logs to a local file by default. More
advanced behavior, such as Vimeo's methodology [described below][logging], can
easily be introduced to better integrate with your existing logging stack.

[logging]: #application-to-an-existing-logging-stack

### Installation

ABLincoln is maintained as an independent PHP [Composer][] package hosted on
[Packagist][]. Include it in in your `composer.json` file for nice autoloading
and dependency management:

```
{
    "require": {
        "vimeo/ablincoln": "~1.0"
    }
}
```

[Composer]: https://getcomposer.org/
[Packagist]: https://packagist.org/packages/vimeo/ablincoln

### Comparison with the PlanOut Reference Implementation

ABLincoln and the original Python release of PlanOut are very similar in both
functionality and usage. Both packages implement abstract and concrete versions
of the Experiment and Namespace classes, parameter overrides to facilitate
testing, exposure logging systems, and various random assignment operators
tested and confirmed to produce identical outputs.

Notable differences between the two releases currently include:
  - ABLincoln features native support for logging either to local files or
  to any PSR-compliant stack through the use of included PHP logging traits
  - ABLincoln does not currently include an interpreter for the
  [PlanOut language][].

[PlanOut language]: http://facebook.github.io/planout/docs/planout-language.html

### Usage in Production Environments

ABLincoln was ported and designed with scalability in mind. Here are a couple
ways that Vimeo has chosen to extend it to meet the needs of our testing
process:

##### Application to an Existing Logging Stack

The Experiment [logging traits][] provided with this port make it easy to log
exposure data in the most convenient way possible for your existing stack. A
quick implementation of the plug-and-play [FileLoggerTrait][] and`tail -f` of
the log file is all you need to monitor parameter exposures in real-time.
Alternatively, the [PSRLoggerTrait][] allows more customizable integration with
existing PSR-compliant logging code. Here at Vimeo, we use a basic
[Monolog Handler][] to enforce PSR-3 compliance and allow PlanOut to talk
nicely to our existing logging infrastructure.

[logging traits]: https://github.com/vimeo/ABLincoln/tree/master/src/Vimeo/ABLincoln/Experiments/Logging
[FileLoggerTrait]: src/Vimeo/ABLincoln/Experiments/Logging/FileLoggerTrait.php
[PSRLoggerTrait]: src/Vimeo/ABLincoln/Experiments/Logging/PSRLoggerTrait.php
[Monolog Handler]: https://github.com/Seldaek/monolog

##### URL Overrides

ABLincoln already supports [parameter overrides][] for quickly examining the
effects of difficult-to-test experiments. A simple way to integrate this
behavior with a live site is to pass overrides into your endpoint as a query
parameter:

```
http://my.site/home.php?overrides=button_color:green,button_text:hello
```

Then it's a relatively simple task to parse the overrides from the query and
pass them into the PHP Experiment API override methods after instantiation.

[parameter overrides]: http://facebook.github.io/planout/docs/testing.html

### Thanks

[PlanOut][], the software from which ABLincoln was ported, was originally
developed by Eytan Bakshy, Dean Eckles, and Michael S. Bernstein, and is
currently maintained by [Eytan Bakshy][] at Facebook. Learn more
about good practice in large-scale testing by reading their paper,
[Designing and Deploying Online Field Experiments][PlanOut Paper].

[Eytan Bakshy]: https://github.com/eytan
[PlanOut]: https://github.com/facebook/planout
[PlanOut Paper]: http://www-personal.umich.edu/~ebakshy/planout.pdf
