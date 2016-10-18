<?php

namespace Luxury\Http\Middleware;

use Luxury\Middleware\Throttle as ThrottleMiddleware;

/**
 * Class Throttle
 *
 * @package Luxury\Http\Middleware
 */
class Throttle extends ThrottleMiddleware
{
    protected $name = 'rqwst';
}
