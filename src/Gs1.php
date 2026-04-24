<?php

namespace Mickeywaugh\Gs1;

use Mickeywaugh\Gs1\Epc\Sgtin;
use Mickeywaugh\Gs1\Epc\Gdti;

class Gs1
{
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return SELF::{$method}(...$arguments);
    }

    /**
     * @param int companyPrefixLength
     * @param int tagSize
     * @param int filterValue
     * @param array schemeParameters["CI"=>"","serial"]
     * @return Sgtin
     */
    public static function Sgtin($arguments): Sgtin
    {
        return new Sgtin($arguments);
    }

    /**
     * @param int companyPrefixLength
     * @param int tagSize
     * @param int filterValue
     * @param array schemeParameters["CI"=>"","serial"]
     * @return Sgtin
     */
    public static function Gdti($arguments): Gdti
    {
        return new Gdti($arguments);
    }
}
