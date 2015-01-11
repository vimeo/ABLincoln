# ABLincoln ![](https://travis-ci.org/vimeo/ABLincoln.svg?branch=master)

**Note: the code examples provided in this README pertain to release 0.2.0 and
not the current master development branch. A new stable release (1.0.0) and
revamped README should be up soon!**

ABLincoln is a PHP-based toolkit for online field experimentation, ported from
Facebook's [PlanOut][] software and released as an easily extendable
[Composer][] package. ABLincoln makes it easy to deploy and maintain
sophisticated experiments and to quickly iterate on these experiments, while
satisfying the constraints of large-scale Internet services with many users.

[PlanOut]: http://facebook.github.io/planout/
[Composer]: https://getcomposer.org/

Developers integrate ABLincoln by defining experiments that detail how _inputs_
(e.g. users, cookie IDs) should get mapped onto conditions. For example, to
set up an experiment randomizing both the color and text of a button, you would
create a class like this:

```php
class MyExperiment extends SimpleExperiment
{
    public function assign($params, $inputs)
    {
        $params['button_color'] = new UniformChoice(
            array('choices' => array('a', 'b')),
            $inputs
        );
        $params['button_text'] = new UniformChoice(
            array('choices' => array('I voted', 'I am a voter')),
            $inputs
        );
    }
}
```

Then, in the application code, you query the Experiment object to find what
values the current user should be mapped onto:

```php
$my_exp = new MyExperiment(array('userid' => 101));
$my_exp->get('button_color');
$my_exp->get('button_text');
```

ABLincoln takes care of randomizing each `userid` into the right bucket. It
does so by hashing the input, so each `userid` will always map onto the same
values for that experiment.

This release currently includes:
  - An extendable PHP class for defining experiments. This toolkit comes
  with objects that make it easy to implement randomized assignments while
  consistently formatting and logging key data.
  - An extendable class for managing multiple mutually exclusive experiment
  namespaces to facilitate the deployment of iterative tests.
  - Native support for any [PSR-3 compliant logger][PSR logger].
  Developers can either utilize existing PSR loggers such as [Monolog][] or
  write their own simple adapters to make existing logging stacks PSR-3
  compliant.

[PSR logger]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
[Monolog]: https://github.com/Seldaek/monolog

### Who is ABLincoln for?

This software release is a PHP implementation of Facebook's PlanOut code
designed for researchers, students, and small businesses wanting to run
experiments. It is designed to be extensible, so that it may be adapted for use
with large production environments. The implementation here serves as a basis
for Vimeo's A/B Testing environment used to deploy experiments to hundreds of
millions of users each month.

### Full Example

To create a basic ABLincoln experiment, you subclass the SimpleExperiment
object and implement an assignment method. You can use ABLincoln's random
assignment operators by setting `$params[$varname]`, where `$params` is the
first argument passed to the `assign()` method and `$varname` is the name of
the variable you are setting.

```php
use \Vimeo\ABLincoln\Experiments\SimpleExperiment;
use \Vimeo\ABLincoln\Operators\Random as Random;

class FirstExperiment extends SimpleExperiment
{
    public function assign($params, $inputs)
    {
        $params['button_color'] = new Random\UniformChoice(
            array('choices' => array('#ff0000', '#00ff00')),
            $inputs
        );
        $params['button_text'] = new Random\WeightedChoice(
            array(
                'choices' => array('Join now!', 'Sign up.'),
                'weights' => array(0.3, 0.7)
            ),
            $inputs
        );
    }

    protected function _log($data)
    {
        echo json_encode($data);
    }
}

$my_exp = new FirstExperiment(array('userid' => 12));
echo $my_exp->get('button_text') . ' ' . $my_exp->get('button_color');
```

The `_log()` method is run every time experiment parameters are queried to
generate an appropriate exposure log. By default, the method exposure logs
according to the object's corresponding PSR-logger. For demonstration purposes,
we have overriden the method here and allowed it to simply print a log string
to the console. Running the above generates the following output, consisting
of the JSON log and the parameter values we have determined:

```
{"name": "FirstExperiment", "time": 1415140890, "salt": "FirstExperiment", "inputs": {"userid": 12}, "params": {"button_color": "#ff0000", "button_text": "Sign up."}, "event": "exposure"}

Sign up. #ff0000
```

The `SimpleExperiment` class will automatically concatenate the name of the
experiment, `FirstExperiment`, the variable name, and the input data (`userid`)
and hash that string to perform the random assignment. Parameter assignments
and inputs are automatically logged according to the specifications of the PSR
logger instance provided (or the `_log()` method if overridden).

### Installation

ABLincoln is maintained as an independent PHP [Composer][] package hosted on
[Packagist][]. While you can download the source code directly from this
repository, we recommend including it as a project dependency in your
`composer.json` file instead.

[Composer]: https://getcomposer.org/
[Packagist]: https://packagist.org/

### Thanks

[PlanOut][], the software from which ABLincoln was ported, is developed and
maintained by Eytan Bakshy, Dean Eckles, and Michael S. Bernstein. Learn more
about good practice in large-scale testing by reading their paper,
[Designing and Deploying Online Field Experiments][PlanOut Paper].

[PlanOut]: https://github.com/facebook/planout
[PlanOut Paper]: http://www-personal.umich.edu/~ebakshy/planout.pdf
