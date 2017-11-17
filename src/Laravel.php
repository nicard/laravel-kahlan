<?php

namespace Sofa\LaravelKahlan;

use PHPUnit_Framework_TestCase;
use Illuminate\Foundation\Testing\Concerns as FoundationConcerns;
use Laravel\Lumen\Testing\Concerns as LumenConcerns;

/**
 * This class is a wrapper for the Laravel's built-in testing features.
 */
class Laravel extends PHPUnit_Framework_TestCase
{
    use FoundationConcerns\InteractsWithContainer,
        FoundationConcerns\InteractsWithAuthentication,
        FoundationConcerns\InteractsWithConsole,
        FoundationConcerns\InteractsWithDatabase,
        FoundationConcerns\InteractsWithSession,
        FoundationConcerns\MocksApplicationServices;

    protected $afterEachCallbacks = [];

    public function __call($method, $params)
    {
        $makesHttpRequests = null;
        $self = $this;
        if ($this->app instanceof \Laravel\Lumen\Application) {
            $makesHttpRequests = new class($self)
            {
                use LumenConcerns\MakesHttpRequests;

                protected $self = null;

                public function __construct($self)
                {
                    $this->self = $self;
                }

                public function __get($name)
                {
                    if (property_exists($this, $name)) {
                        return $this->{$name};
                    } else if (property_exists($this->self, $name)) {
                        return $this->self->{$name};
                    } else if (property_exists($this->self->app, $name)) {
                        return $this->self->app{$name};
                    }

                    return null;
                }
            };
        } else {
            $makesHttpRequests = new class($self)
            {
                use FoundationConcerns\MakesHttpRequests;

                protected $self = null;

                public function __construct($self)
                {
                    $this->self = $self;
                }

                public function __get($name)
                {
                    if (property_exists($this, $name)) {
                        return $this->{$name};
                    } else if (property_exists($this->self, $name)) {
                        return $this->self->{$name};
                    } else if (property_exists($this->self->app, $name)) {
                        return $this->self->app{$name};
                    }

                    return null;
                }
            };
        }

        return method_exists($makesHttpRequests, $method)
            ? call_user_func_array([$makesHttpRequests, $method], $params)
            : call_user_func_array([$this->app, $method], $params);
    }

    /**
     * Make everything public because we access it from the outside.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->{$property} = $value;
    }

    /**
     * Make everything public because we access it from the outside.
     *
     * @param  string $property
     * @return mixed
     */
    public function __get($property)
    {
        return property_exists($this, $property) ? $this->{$property} : null;
    }

    /**
     * Laravel compatibility.
     *
     * For your own callbacks it is recommended to use kahlan before/after callbacks.
     *
     * @param  callable $callback
     * @return void
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->afterEachCallbacks[] = $callback;
    }

    /**
     * Call the laravel callbacks.
     *
     * @return void
     * @throws \Exception
     */
    public function afterEach()
    {
        foreach ($this->afterEachCallbacks as $callback) {
            call_user_func($callback);
        }
    }
}
