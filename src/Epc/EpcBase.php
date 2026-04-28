<?php

namespace Mickeywaugh\Gs1\Epc;

use Mickeywaugh\Gs1\Epc\EpcMesg;
use Mickeywaugh\Gs1\Spec\EpcSpec;

/**
 * EPC基类 - 所有EPC编码方案的抽象基类
 * 
 * 根据GS1 EPC标签数据标准版本1.13实现
 * 提供通用的EPC编码解码功能和属性管理
 * 
 * @package Mickeywaugh\Gs1\Epc
 * @author Mickey Wu <mickey.wu@boingtech.com>
 * @license MIT
 * @abstract
 */
abstract class EpcBase
{
    /** URI前缀常量 */
    protected const URI_PREFIX = 'urn:epc:id';
    protected const TAG_PREFIX = 'urn:epc:tag';
    protected const RAW_PREFIX = 'urn:epc:raw';

    /** 支持的EPC方案 */
    public const SUPPORTED_SCHEMES = [
        "Gs1" => [
            "sgtin" => "GTIN with serial",
            "sscc" => "SSCC",
            "sgln" => "GLN with extension",
            "grai" => "GRAI with serial",
            "giai" => "GIAI",
            "gsrn" => "GSRN",
            "gsrnp" => "GSRN-P",
            "gdti" => "GDTI with serial",
            "cpi" => "CPI, serial",
            "sgcn" => "GCN with serial",
            "itip" => "ITIP with piece and total, serial"
        ],
        "Others" => [
            "gid" => "GID",
            "usdod" => "USDoD",
            "adi" => "ADI"
        ]
    ];

    // ==================== 实例属性 ====================

    /** URI前缀 */
    protected string $uriPrefix = self::URI_PREFIX;
    protected string $tagPrefix = self::TAG_PREFIX;
    protected string $rawPrefix = self::RAW_PREFIX;

    /** EPC参数选项 */
    protected array $companyPrefixLengthOptions = [6, 7, 8, 9, 10, 11, 12];
    protected array $tagSizeOptions = [
        96 => "96 bits",
        110 => "110 bits",
        113 => "113 bits",
        170 => "170 bits",
        174 => "174 bits",
        195 => "195 bits",
        198 => "198 bits",
        202 => "202 bits",
        212 => "212 bits",
        "var" => "Variable"
    ];
    protected array $filterValueOptions = [
        0 => "All Others",
        1 => "Reserved",
        2 => "Reserved",
        3 => "Reserved",
        4 => "Reserved",
        5 => "Reserved",
        6 => "Reserved",
        7 => "Reserved"
    ];

    /** EPC方案相关属性 */
    protected ?string $scheme = null;
    protected ?string $encodeScheme = null;
    protected array $schemeParameters = [];

    /** EPC参数值 */
    protected int $companyPrefixLength = 0;
    protected int $tagSize = 0;
    protected int $filterValue = 0;

    /** EPC数据字段 */
    protected string $companyPrefix = "";
    protected string $itemReference = "";
    protected string $CI = "";
    protected string $serial = "";

    /** EPC输出结果 */
    protected string $epcURI = "";
    protected string $epcTagURI = "";
    protected string $epcRawURI = "";
    protected string $epcBinary = "";
    protected string $epcHexaDecimal = "";

    /** 错误信息 */
    protected ?int $errorCode = null;
    protected ?string $errorMsg = null;

    /** 头部结构信息 */
    protected array $headerStruct = [];

    // ==================== 错误处理方法 ====================

    /**
     * 设置错误信息
     * 
     * @param int $_errorCode 错误代码
     * @param mixed ...$parameters 错误消息参数
     * @return static 始终返回自身
     */
    public function setError(int $_errorCode, ...$parameters): static
    {
        $this->errorCode = $_errorCode;
        $this->errorMsg = EpcMesg::getMessage($_errorCode, ...$parameters);
        return $this;
    }

    /**
     * 获取错误消息
     * 
     * @return ?string 错误消息，无错误时返回null
     */
    public function getErrorMsg(): ?string
    {
        return $this->errorMsg;
    }

    /**
     * 获取错误代码
     * 
     * @return int|null 错误代码，无错误时返回null
     */
    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    /**
     * 检查是否有错误
     * 
     * @return bool 有错误时返回true
     */
    public function hasError(): bool
    {
        return $this->errorCode !== null;
    }

    // ==================== 方案相关方法 ====================

    /**
     * 设置EPC方案
     * 
     * @param string $scheme 方案名称，为空时从headerStruct中获取
     * @return static 返回自身
     */
    public function setScheme(?string $scheme = ""): static
    {
        if (empty($scheme)) {
            if (isset($this->headerStruct['scheme'])) {
                $scheme = $this->headerStruct['scheme'];
            } else {
                return $this->setError(EpcMesg::PARAM_MISSING, 'scheme');
            }
        }

        if (!is_string($scheme)) {
            return $this->setError(EpcMesg::PARAM_TYPE_ERROR, 'scheme');
        }

        $normalizedScheme = strtolower($scheme);
        $allSchemes = array_merge(
            array_keys(self::SUPPORTED_SCHEMES["Gs1"]),
            array_keys(self::SUPPORTED_SCHEMES["Others"])
        );

        if (!in_array($normalizedScheme, $allSchemes)) {
            return $this->setError(EpcMesg::EPC_SCHEMA_UNSUPPORT, $scheme);
        }

        $this->scheme = $normalizedScheme;
        return $this;
    }

    /**
     * 获取当前方案
     * 
     * @return ?string 方案名称
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * 设置编码方案（方案-标签大小组合）
     * 
     * @return static 返回自身以支持链式调用
     */
    public function setEncodeScheme(): static
    {
        $this->encodeScheme = $this->scheme . "-" . $this->tagSize;
        return $this;
    }

    /**
     * 获取编码方案
     * 
     * @return string|null 编码方案名称
     */
    public function getEncodeScheme(): ?string
    {
        return $this->encodeScheme;
    }

    // ==================== 公司前缀相关方法 ====================

    /**
     * 设置公司前缀长度
     * 
     * @param ?int $_companyPrefixLength 公司前缀长度，为空时从CI自动计算
     * @return static 返回自身
     */
    public function setCompanyPrefixLength(?int $_companyPrefixLength = 7): static
    {
        $length = EpcSpec::getCompanyPrefixLength($this->CI) ?: $_companyPrefixLength;

        if (!in_array($length, $this->getCompanyPrefixLengthOptions())) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "Company prefix length is out of range. $length");
        }

        $this->companyPrefixLength = $length;
        return $this;
    }

    /**
     * 获取公司前缀长度
     * 
     * @return int 公司前缀长度
     */
    public function getCompanyPrefixLength(): int
    {
        return $this->companyPrefixLength;
    }

    /**
     * 获取支持的公司前缀长度选项
     * 
     * @return array 公司前缀长度数组
     */
    public function getCompanyPrefixLengthOptions(): array
    {
        return $this->companyPrefixLengthOptions;
    }

    /**
     * 设置公司前缀
     * 
     * @param string $companyPrefix 公司前缀字符串
     * @return static 返回自身以支持链式调用
     */
    public function setCompanyPrefix(string $companyPrefix): static
    {
        $this->companyPrefix = $companyPrefix;
        return $this;
    }

    /**
     * 获取公司前缀
     * 
     * @return string 公司前缀字符串
     */
    public function getCompanyPrefix(): string
    {
        return $this->companyPrefix;
    }

    // ==================== CI (Control Indicator) 相关方法 ====================

    /**
     * 设置CI（控制指示符）
     * 
     * @param string $CI CI值
     * @return static 返回自身以支持链式调用
     */
    public function setCI(string $CI): static
    {
        $this->CI = $CI;
        $this->setCompanyPrefixLength();
        return $this;
    }

    /**
     * 获取CI
     * 
     * @return string CI值
     */
    public function getCI(): string
    {
        return $this->CI;
    }

    // ==================== 项目参考相关方法 ====================

    /**
     * 设置项目参考
     * 
     * @param string $itemReference 项目参考字符串
     * @return static 返回自身以支持链式调用
     */
    public function setItemReference(string $itemReference): static
    {
        $this->itemReference = $itemReference;
        return $this;
    }

    /**
     * 获取项目参考
     * 
     * @return string 项目参考字符串
     */
    public function getItemReference(): string
    {
        return $this->itemReference;
    }

    // ==================== 序列号相关方法 ====================

    /**
     * 设置序列号
     * 
     * @param string $serial 序列号字符串
     * @return static 返回自身以支持链式调用
     */
    public function setSerial(string $serial): static
    {
        $this->serial = $serial;
        return $this;
    }

    /**
     * 获取序列号
     * 
     * @return string 序列号字符串
     */
    public function getSerial(): string
    {
        return $this->serial;
    }

    // ==================== 标签大小相关方法 ====================

    /**
     * 设置标签大小
     * 
     * @param int $tagSize 标签大小（位数）
     * @return static 返回自身
     */
    public function setTagSize(int $tagSize): static
    {
        if (!array_key_exists($tagSize, $this->getTagSizeOptions())) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "Tag size is out of range. $tagSize");
        }

        $this->tagSize = $tagSize;
        return $this;
    }

    /**
     * 获取标签大小
     * 
     * @return int 标签大小（位数）
     */
    public function getTagSize(): int
    {
        return $this->tagSize;
    }

    /**
     * 获取支持的标签大小选项
     * 
     * @return array 标签大小选项数组
     */
    public function getTagSizeOptions(): array
    {
        return $this->tagSizeOptions;
    }

    // ==================== 过滤值相关方法 ====================

    /**
     * 设置过滤值
     * 
     * @param int $filterValue 过滤值
     * @return static|null 返回自身
     */
    public function setFilterValue(int $filterValue): static
    {
        if (!array_key_exists($filterValue, $this->getFilterValueOptions())) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "Filter value is out of range. $filterValue");
        }

        $this->filterValue = $filterValue;
        return $this;
    }

    /**
     * 获取过滤值
     * 
     * @return int 过滤值
     */
    public function getFilterValue(): int
    {
        return $this->filterValue;
    }

    /**
     * 获取支持的过滤值选项
     * 
     * @return array 过滤值选项数组
     */
    public function getFilterValueOptions(): array
    {
        return $this->filterValueOptions;
    }

    // ==================== 方案参数相关方法 ====================

    /**
     * 设置方案参数
     * 
     * @param array $schemeParameters 方案参数字典
     * @return static 返回自身
     */
    public function setSchemeParameters(array $schemeParameters): static
    {
        if (empty($schemeParameters)) {
            return $this->setError(EpcMesg::EPC_OPTION_MISSING, 'scheme parameters');
        }

        foreach ($schemeParameters as $key => $parameter) {
            if (empty($parameter) && $parameter !== 0 && $parameter !== '0') {
                return $this->setError(EpcMesg::EPC_OPTION_SHOULD_NOT_EMPTY, $key);
            }
        }

        $this->schemeParameters = $schemeParameters;
        return $this;
    }

    /**
     * 获取方案参数
     * 
     * @return array 方案参数字典
     */
    public function getSchemeParameters(): array
    {
        return $this->schemeParameters;
    }

    // ==================== EPC输出相关方法 ====================

    /**
     * 设置EPC十六进制值
     * 
     * @param string $epcHexaDecimal 十六进制字符串
     * @return static 返回自身
     */
    public function setEpcHexaDecimal(string $epcHexaDecimal): static
    {
        if (empty($epcHexaDecimal)) {
            return $this->setError(EpcMesg::PARAM_SHOULD_NOT_EMPTY, "epcHexaDecimal");
        }

        if (!EpcSpec::isHexChars($epcHexaDecimal)) {
            return $this->setError(EpcMesg::EPC_HEX_FORMAT_ERROR);
        }

        $this->epcHexaDecimal = strtoupper($epcHexaDecimal);
        return $this;
    }

    /**
     * 获取EPC十六进制值
     * 
     * @return string 十六进制字符串
     */
    public function getEpcHexaDecimal(): string
    {
        return $this->epcHexaDecimal;
    }

    /**
     * 设置EPC二进制值
     * 
     * @param string $epcBinary 二进制字符串
     * @return static 返回自身以支持链式调用
     */
    public function setEpcBinary(string $epcBinary): static
    {
        $this->epcBinary = $epcBinary;
        return $this;
    }

    /**
     * 获取EPC二进制值
     * 
     * @return string 二进制字符串
     */
    public function getEpcBinary(): string
    {
        return $this->epcBinary;
    }

    /**
     * 设置EPC URI
     * 
     * @param string $epcURI EPC URI字符串
     * @return static 返回自身以支持链式调用
     */
    public function setEpcURI(string $epcURI): static
    {
        $this->epcURI = $epcURI;
        return $this;
    }

    /**
     * 获取EPC URI
     * 
     * @return string EPC URI字符串
     */
    public function getEpcUri(): string
    {
        return $this->epcURI;
    }

    /**
     * 设置EPC Tag URI
     * 
     * @param string $epcTagURI EPC Tag URI字符串
     * @return static 返回自身以支持链式调用
     */
    public function setEpcTagURI(string $epcTagURI): static
    {
        $this->epcTagURI = $epcTagURI;
        return $this;
    }

    /**
     * 获取EPC Tag URI
     * 
     * @return string EPC Tag URI字符串
     */
    public function getEpcTagURI(): string
    {
        return $this->epcTagURI;
    }

    /**
     * 设置EPC Raw URI
     * 
     * @param string $epcRawURI EPC Raw URI字符串
     * @return static 返回自身以支持链式调用
     */
    public function setEpcRawURI(string $epcRawURI): static
    {
        $this->epcRawURI = $epcRawURI;
        return $this;
    }

    /**
     * 获取EPC Raw URI
     * 
     * @return string EPC Raw URI字符串
     */
    public function getEpcRawURI(): string
    {
        return $this->epcRawURI;
    }

    // ==================== 头部结构相关方法 ====================

    /**
     * 设置头部结构
     * 
     * @param string $headerValue 头部十六进制值
     * @return static 返回自身
     */
    public function setHeaderStruct(string $headerValue): static
    {
        if (empty($headerValue)) {
            return $this->setError(EpcMesg::PARAM_SHOULD_NOT_EMPTY, "headerValue");
        }

        $this->headerStruct = EpcSpec::getHeaderValues($headerValue);
        return $this;
    }

    /**
     * 获取头部结构
     * 
     * @return array 头部结构数组
     */
    public function getHeaderStruct(): array
    {
        return $this->headerStruct;
    }

    // ==================== EPC标准配置相关方法 ====================

    /**
     * 获取EPC标准配置
     * 
     * @param string $nodeName 节点名称（BINARY、PURE_IDENTITY等）
     * @return array 标准配置数组
     */
    public function getEpcStandard(string $nodeName): array
    {
        if (empty($nodeName) || empty($this->scheme) || $this->tagSize === 0) {
            return [];
        }

        $nodeName = strtoupper($nodeName);
        return EpcSpec::getDataStandard($this->scheme . '-' . $this->tagSize)[$nodeName] ?? [];
    }

    // ==================== 编码解码方法（子类实现）====================

    /**
     * 编码EPC数据
     * 
     * 此方法应在子类中实现具体的编码逻辑
     * 
     * @return static 返回自身
     * @abstract
     */
    abstract public function encode(): static;

    /**
     * 解码EPC数据
     * 
     * 此方法应在子类中实现具体的解码逻辑
     * 
     * @param string $epcHexString EPC十六进制字符串
     * @return static|null 成功时返回实例，失败时返回null
     */
    abstract public static function decode(string $epcHexString): static;

    // ==================== 工具方法 ====================

    /**
     * 获取校验位（不同方案有不同的实现）
     * 
     * @param string $number 需要计算校验位的数字字符串
     * @return string 校验位字符
     * @abstract
     */
    abstract public function getCheckDigit(string $number): string;

    /**
     * 获取输出数据数组
     * 
     * @return array 包含所有EPC相关数据的关联数组
     */
    public function getOutput(): array
    {
        return [
            "scheme" => [
                "name" => $this->scheme,
                "parameters" => $this->schemeParameters
            ],
            "tagSize" => $this->tagSize,
            "filterValue" => $this->filterValue,
            "companyPrefixLength" => $this->companyPrefixLength,
            "companyPrefix" => $this->companyPrefix,
            "itemReference" => $this->itemReference,
            "serial" => $this->serial,
            "CI" => $this->CI,
            "epcURI" => $this->epcURI,
            "epcTagURI" => $this->epcTagURI,
            "epcRawURI" => $this->epcRawURI,
            "epcBinary" => $this->epcBinary,
            "epcHexaDecimal" => $this->epcHexaDecimal,
            "error" => [
                "code" => $this->errorCode,
                "message" => $this->errorMsg
            ]
        ];
    }

    /**
     * 转换为字符串表示
     * 
     * @return string 格式化的EPC信息字符串
     */
    public function __toString(): string
    {
        $output = $this->getOutput();
        return json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
