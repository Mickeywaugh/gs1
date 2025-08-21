<?php

namespace Mickeywaugh\Gs1\Epc;

class EpcMesg
{

    const PARAM_MISSING = 1002;
    const PARAM_SHOULD_NOT_EMPTY = 1004;
    const PARAM_COUNT_ERROR = 1006;
    const PARAM_TYPE_ERROR = 1008;
    const PARAM_RANGE_ERROR = 1010;
    const PARAM_FORMAT_ERROR = 1012;
    const PARAM_OUTOF_RANGE = 1014;

    const EPC_SCHEMA_UNSUPPORT = 2002;
    const EPC_OPTION_MISSING =  2004;
    const EPC_OPTION_ERROR = 2006;
    const EPC_OPTION_RANGE_ERROR = 2008;
    const EPC_BINARY_FORMAT_ERROR = 2010;
    const EPC_BINARY_LENGTH_ERROR = 2012;
    const EPC_PURE_URI_FORMAT_ERROR = 2014;
    const EPC_TAG_URI_FORMAT_ERROR = 2016;
    const EPC_RAW_URI_FORMAT_ERROR = 2018;
    const EPC_DEMAND_DATA_EMPTY = 2020;
    const EPC_HEX_FORMAT_ERROR = 2022;
    const EPC_HEX_LENGTH_ERROR = 2024;
    const EPC_OPTION_SHOULD_NOT_EMPTY = 2026;


    static $msgMap = [
        self::PARAM_MISSING => "%s required.",
        self::PARAM_SHOULD_NOT_EMPTY => "%s should not empty.",
        self::PARAM_COUNT_ERROR => "Parameter count error",
        self::PARAM_TYPE_ERROR => "Parameter type error",
        self::PARAM_RANGE_ERROR => "Parameter range error",
        self::PARAM_FORMAT_ERROR => "%s format error",
        self::PARAM_OUTOF_RANGE => "%s out of range",

        self::EPC_SCHEMA_UNSUPPORT => "EPC schema unsupport:%s",
        self::EPC_OPTION_MISSING => "EPC option:%s missing",
        self::EPC_OPTION_ERROR => "EPC option error",
        self::EPC_OPTION_RANGE_ERROR => "EPC option range error",
        self::EPC_BINARY_FORMAT_ERROR => "EPC binary format error",
        self::EPC_BINARY_LENGTH_ERROR => "EPC binary length error",
        self::EPC_PURE_URI_FORMAT_ERROR => "EPC pure URI format error",
        self::EPC_TAG_URI_FORMAT_ERROR => "EPC tag URI format error",
        self::EPC_RAW_URI_FORMAT_ERROR => "EPC raw URI format error",
        self::EPC_DEMAND_DATA_EMPTY => "Demanded data is empty",
        self::EPC_HEX_FORMAT_ERROR => "EPC hex format error",
        self::EPC_HEX_LENGTH_ERROR => "EPC hex length error",
        self::EPC_OPTION_SHOULD_NOT_EMPTY => "EPC option:%s should not empty"
    ];

    public static function getMessage($code, ...$argc): ?string
    {
        if (!isset(self::$msgMap[$code])) {
            return null;
        }
        if (count($argc) < substr_count(self::$msgMap[$code], "%s")) {
            return null;
        }
        return vsprintf(self::$msgMap[$code], ...$argc);
    }
}
