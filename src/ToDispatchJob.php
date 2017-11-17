<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20/10/2017
 * Time: 16:53
 */

namespace KahlanHelper;

use Illuminate\Support\Facades\Bus;
use PHPUnit_Framework_ExpectationFailedException;

class ToDispatchJob
{
    public static function init($kahlan){
        return static::class;
    }

    public static function match($actual, $expected = null)
    {
        try {
            Bus::assertDispatched($expected);
            return true;
        } catch (PHPUnit_Framework_ExpectationFailedException $exception) {
            return false;
        }
    }

    public static function description()
    {
        return "despachar um job.";
    }
}
