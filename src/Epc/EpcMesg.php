<?php

namespace Mickeywaugh\Gs1\Epc;

/**
 * EPC错误消息管理类
 * 
 * 定义所有EPC编码解码过程中可能出现的错误代码和消息模板
 * 
 * @package Mickeywaugh\Gs1\Epc
 * @author Mickeywaugh <mickeywaugh@qq.com>
 * @license MIT
 */
class EpcMesg
{
    // ==================== 参数相关错误 (1000-1099) ====================

    /** 缺少必需参数 */
    const PARAM_MISSING = 1002;

    /** 参数不应为空 */
    const PARAM_SHOULD_NOT_EMPTY = 1004;

    /** 参数数量错误 */
    const PARAM_COUNT_ERROR = 1006;

    /** 参数类型错误 */
    const PARAM_TYPE_ERROR = 1008;

    /** 参数范围错误 */
    const PARAM_RANGE_ERROR = 1010;

    /** 参数格式错误 */
    const PARAM_FORMAT_ERROR = 1012;

    /** 参数超出范围 */
    const PARAM_OUTOF_RANGE = 1014;

    // ==================== EPC相关错误 (2000-2099) ====================

    /** 不支持的EPC方案 */
    const EPC_SCHEMA_UNSUPPORT = 2002;

    /** 缺少EPC选项 */
    const EPC_OPTION_MISSING = 2004;

    /** EPC选项错误 */
    const EPC_OPTION_ERROR = 2006;

    /** EPC选项范围错误 */
    const EPC_OPTION_RANGE_ERROR = 2008;

    /** EPC二进制格式错误 */
    const EPC_BINARY_FORMAT_ERROR = 2010;

    /** EPC二进制长度错误 */
    const EPC_BINARY_LENGTH_ERROR = 2012;

    /** EPC纯标识URI格式错误 */
    const EPC_PURE_URI_FORMAT_ERROR = 2014;

    /** EPC标签URI格式错误 */
    const EPC_TAG_URI_FORMAT_ERROR = 2016;

    /** EPC原始URI格式错误 */
    const EPC_RAW_URI_FORMAT_ERROR = 2018;

    /** 请求的数据为空 */
    const EPC_DEMAND_DATA_EMPTY = 2020;

    /** EPC十六进制格式错误 */
    const EPC_HEX_FORMAT_ERROR = 2022;

    /** EPC十六进制长度错误 */
    const EPC_HEX_LENGTH_ERROR = 2024;

    /** EPC选项不应为空 */
    const EPC_OPTION_SHOULD_NOT_EMPTY = 2026;


    const EPC_HEADER_ERROR = 3010;
    const EPC_PARSER_ERROR = 3012;
    const EPC_STANDARD_ERROR = 3014;
    const EPC_ENCODING_ERROR = 3016;

    // ==================== 错误消息映射表 ====================

    /**
     * 错误代码到消息模板的映射
     * 
     * @var array<string>
     */
    static array $msgMap = [
        // 参数错误
        self::PARAM_MISSING => "Parameter '%s' is required.",
        self::PARAM_SHOULD_NOT_EMPTY => "Parameter '%s' should not be empty.",
        self::PARAM_COUNT_ERROR => "Parameter count error: expected %d, got %d.",
        self::PARAM_TYPE_ERROR => "Parameter type error: '%s' should be %s.",
        self::PARAM_RANGE_ERROR => "Parameter '%s' value %s is out of valid range [%s, %s].",
        self::PARAM_FORMAT_ERROR => "Parameter format error: %s",
        self::PARAM_OUTOF_RANGE => "Parameter '%s' value is out of allowed range.",

        // EPC错误
        self::EPC_SCHEMA_UNSUPPORT => "Unsupported EPC scheme: '%s'. Supported schemes: SGTIN, GDTI, SSCC, SGLN, GRAI, GIAI, GSRN, etc.",
        self::EPC_OPTION_MISSING => "Required EPC option '%s' is missing.",
        self::EPC_OPTION_ERROR => "Invalid EPC option provided.",
        self::EPC_OPTION_RANGE_ERROR => "EPC option value is outside the valid range.",
        self::EPC_BINARY_FORMAT_ERROR => "EPC binary data format is invalid or corrupted.",
        self::EPC_BINARY_LENGTH_ERROR => "EPC binary data length does not match expected tag size.",
        self::EPC_PURE_URI_FORMAT_ERROR => "EPC Pure Identity URI format is invalid.",
        self::EPC_TAG_URI_FORMAT_ERROR => "EPC Tag URI format is invalid.",
        self::EPC_RAW_URI_FORMAT_ERROR => "EPC Raw URI format is invalid.",
        self::EPC_DEMAND_DATA_EMPTY => "Required data field is empty or null.",
        self::EPC_HEX_FORMAT_ERROR => "EPC hexadecimal string contains invalid characters. Only 0-9 and A-F are allowed.",
        self::EPC_HEX_LENGTH_ERROR => "EPC hexadecimal string length is invalid for the specified tag size.",
        self::EPC_OPTION_SHOULD_NOT_EMPTY => "EPC option '%s' should not be empty.",
        self::EPC_HEADER_ERROR => "EPC header data is invalid.",
        self::EPC_PARSER_ERROR => "EPC parsing error occurred.",
        self::EPC_STANDARD_ERROR => "EPC standard definition is missing or invalid.",
        self::EPC_ENCODING_ERROR => "EPC encoding error occurred.",
    ];

    /**
     * 获取错误消息
     * 
     * @param int $code 错误代码
     * @param array ...$argc 消息参数
     * @return string|null 格式化后的错误消息，如果代码无效则返回null
     * 
     * @example
     * ```php
     * $msg = EpcMesg::getMessage(EpcMesg::PARAM_MISSING, ['companyPrefix']);
     * // Returns: "Parameter 'companyPrefix' is required."
     * ```
     */
    public static function getMessage(int $code, ...$argc): ?string
    {
        if (!isset(self::$msgMap[$code])) {
            return "Unknown error code: {$code}";
        }

        $template = self::$msgMap[$code];
        $placeholderCount = substr_count($template, "%s") + substr_count($template, "%d");

        if (count($argc) < $placeholderCount) {
            // 如果参数不足，返回未替换的模板
            return $template;
        }

        return vsprintf($template, $argc);
    }

    /**
     * 获取所有错误代码列表
     * 
     * @return array 错误代码常量名到值的映射
     */
    public static function getAllErrorCodes(): array
    {
        $reflection = new \ReflectionClass(self::class);
        return $reflection->getConstants();
    }

    /**
     * 检查错误代码是否有效
     * 
     * @param int $code 错误代码
     * @return bool 代码是否有效
     */
    public static function isValidErrorCode(int $code): bool
    {
        return isset(self::$msgMap[$code]);
    }
}
