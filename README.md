# ABLincoln [![Latest Stable Version][version image]][packagist link] [![Build Status][build image]][build link]

[version image]: https://poser.pugx.org/vimeo/ablincoln/v/stable.svg
[packagist link]: https://packagist.org/packages/vimeo/ablincoln
[build image]: https://travis-ci.org/vimeo/ABLincoln.svg?branch=master
[build link]: https://travis-ci.org/vimeo/ABLincoln

ABLincoln is a PHP-based toolkit for online field experimentation, ported from
Facebook's [PlanOut][] software and released as an easily extendable
[Composer][installation] package. ABLincoln makes it easy to deploy and
maintain sophisticated randomized experiments and to quickly iterate on these
experiments, while satisfying the constraints of large-scale Internet services
with many users.

[PlanOut]: http://facebook.github.io/planout/
[installation]: #installation

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

### Comparison with Python Release

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

ABLincoln was ported and designed with scalability in mind. Here are a few ways
that Vimeo has chosen to extend it to meet the needs of our testing process:

##### Serialized Experiments

From the beginning, Vimeo's goal was to develop a software package that would
simplify the process of creating and running experiments for everyone, even
those who seldom find themselves writing code. We store all data relating to
currently running tests - experiment/namespace names, publish/stop dates,
bucket proportions, parameter names and values, and random operator weighting,
to name a few - in a SQL database populated by a simple yet comprehensive
mod panel that allows even the programming illiterate to quickly design
experiments to launch on the site. Another class handles the process of loading
experiment information from the database and recreating the test before
parameter querying.

The consequence of this is that a simple
`ABLincoln::load('namespace')->get('parameter')` within our stack is all that
is needed to recreate a given experiment set and query a parameter value from
the test.

We hope to make experiment serialization even more accessible than it
currently is with a future implementation of an interpreter for the
[PlanOut language][].

##### Application to an Existing Logging Stack

TODO

##### Traffic Segmentation

What if you want to deploy a test comparing users from a specific location or
demographic to the overall population? Vimeo refers to this process as traffic
"segmentation", and found it easy to implement on top of existing PlanOut code.
We simply extend the Experiment class and overwrote its `get()` method - if a
user is bucketed to the experiment but does not belong to the isolated
demographic, he or she receives a default parameter value instead of the
test value.

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

### Installation

ABLincoln is maintained as an independent PHP [Composer][] package hosted on
[Packagist][]. Include it in in your `composer.json` file for nice autoloading
and dependency management:

```
{
    "require": {
        "vimeo/ablincoln": "0.1.2"
    }
}
```

[Composer]: https://getcomposer.org/
[Packagist]: https://packagist.org/packages/vimeo/ablincoln

### Thanks

[PlanOut][], the software from which ABLincoln was ported, is developed and
maintained by Eytan Bakshy, Dean Eckles, and Michael S. Bernstein. Learn more
about good practice in large-scale testing by reading their paper,
[Designing and Deploying Online Field Experiments][PlanOut Paper].

[PlanOut]: https://github.com/facebook/planout
[PlanOut Paper]: http://www-personal.umich.edu/~ebakshy/planout.pdf