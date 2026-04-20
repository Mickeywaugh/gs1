<?php

namespace Mickeywaugh\Gs1;

use Mickeywaugh\Gs1\Epc\Sgtin;
use Mickeywaugh\Gs1\Epc\Gdti;

class Gs1
{
    public static function __callStatic(string $method, array $arguments)
    {
        $gs1 = new static();
        return $gs1->{$method}(...$arguments);
    }

    public function Sgtin($arguments)
    {
        return new Sgtin($arguments);
    }

    public function Gdti($arguments)
    {
        return new Gdti($arguments);
    }
}
