<?php

namespace Mickeywaugh\Gs1;

use Mickeywaugh\Gs1\Epc\Sgtin;
use Mickeywaugh\Gs1\Epc\Gdti;
use InvalidArgumentException;

/**
 * GS1 EPC编码解码库主入口类
 * 
 * 提供工厂方法创建各种EPC编码方案实例
 * 
 * @package Mickeywaugh\Gs1
 * @author Mickeywaugh <mickeywaugh@163.com>
 * @license MIT
 */
class Gs1
{
    /**
     * 魔术方法，支持静态调用
     * 
     * @param string $method 方法名
     * @param array $arguments 参数列表
     * @return mixed
     * @throws InvalidArgumentException 当方法不存在时抛出异常
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        if (!method_exists(self::class, $method)) {
            throw new InvalidArgumentException("Method {$method} does not exist in Gs1 class");
        }
        return self::{$method}(...$arguments);
    }

    /**
     * 创建SGTIN (Serialized Global Trade Item Number) 实例
     * SGTIN用于标识带序列号的贸易项目
     * @param array $arguments 包含以下键的关联数组:
     *   - companyPrefixLength (int): 公司前缀长度，范围6-12
     *   - tagSize (int): 标签大小，96或198位
     *   - filterValue (int): 过滤值，范围0-7
     *   - schemeParameters (array): 方案参数，包含'CI'(GTIN)和'serial'(序列号)
     * @return Sgtin SGTIN实例
     */
    public static function Sgtin(array $arguments): Sgtin
    {
        $tagSize = $arguments['tagSize'] ?? 96;
        $filterValue = $arguments['filterValue'] ?? 1;
        $schemeParameters = $arguments['schemeParameters'] ?? ['CI' => '', 'serial' => ''];

        return new Sgtin($schemeParameters, $tagSize, $filterValue);
    }

    /**
     * 创建GDTI (Global Document Type Identifier) 实例
     * 
     * GDTI用于标识带序列号的文档类型
     * 
     * @param array $arguments 包含以下键的关联数组:
     *   - companyPrefixLength (int): 公司前缀长度，范围6-12
     *   - tagSize (int): 标签大小，96或113位
     *   - filterValue (int): 过滤值，范围0-7
     *   - schemeParameters (array): 方案参数，包含'CI'(公司前缀+文档类型)和'serial'(序列号)
     * @return Gdti GDTI实例
     * 
     * @example
     * ```php
     * $gdti = Gs1::Gdti([
     *     'companyPrefixLength' => 7,
     *     'tagSize' => 96,
     *     'filterValue' => 0,
     *     'schemeParameters' => [
     *         'CI' => '1234567123456',
     *         'serial' => 'DOC001'
     *     ]
     * ]);
     * ```
     */
    public static function Gdti(array $arguments): Gdti
    {
        $tagSize = $arguments['tagSize'] ?? 96;
        $filterValue = $arguments['filterValue'] ?? 0;
        $schemeParameters = $arguments['schemeParameters'] ?? ['CI' => '', 'serial' => ''];

        return new Gdti($schemeParameters, $tagSize, $filterValue);
    }
}
