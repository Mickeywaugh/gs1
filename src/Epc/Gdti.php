<?php

namespace Mickeywaugh\Gs1\Epc;

use Mickeywaugh\Gs1\Epc\EpcBase;
use Mickeywaugh\Gs1\Epc\EpcMesg;
use Mickeywaugh\Gs1\Spec\EpcSpec;

/**
 * GDTI (Global Document Type Identifier) EPC编码类
 * 
 * 实现GS1标准下带序列号的文档类型标识编码
 * 支持96位和113位标签格式
 * 
 * @package Mickeywaugh\Gs1\Epc
 * @author Mickeywaugh <mickeywaugh@qq.com>
 * @license MIT
 * @link https://www.gs1.org/standards/epc-rfid-tag-data-standard
 */
class Gdti extends EpcBase
{
    /**
     * 构造函数
     * 
     * @param array $schemeParameters 方案参数，必须包含'CI'(公司前缀+文档类型)和'serial'(序列号)
     * @param int $tagSize 标签大小，96或113位，默认96
     * @param int $filterValue 过滤值，0-7，默认0
     */
    public function __construct(
        array $schemeParameters = ["CI" => "", "serial" => ""],
        int $tagSize = 96,
        int $filterValue = 1
    ) {
        $this->setScheme("GDTI")
            ->setSchemeParameters($schemeParameters)
            ->setTagSize($tagSize)
            ->setFilterValue($filterValue);
    }

    /**
     * 设置并验证方案参数
     * 
     * @param array $schemeParameters 包含'CI'和'serial'的关联数组
     * @return static 返回自身
     */
    public function setSchemeParameters(array $schemeParameters): static
    {
        $requiredParams = ["CI", "serial"];

        foreach ($requiredParams as $param) {
            if (!array_key_exists($param, $schemeParameters)) {
                return $this->setError(EpcMesg::EPC_OPTION_MISSING, $param);
            }

            if (empty($schemeParameters[$param]) && $schemeParameters[$param] !== 0 && $schemeParameters[$param] !== '0') {
                return $this->setError(EpcMesg::EPC_OPTION_SHOULD_NOT_EMPTY, $param);
            }
        }

        // 验证CI格式（13位数字：公司前缀 + 文档类型）
        $ci = (string)$schemeParameters['CI'];
        if (!preg_match('/^\d{13}$/', $ci)) {
            return $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "CI must be 13 digits (company prefix + document type)");
        }

        return $this->setCI($ci)->setSerial((string)$schemeParameters['serial']);
    }

    /**
     * 获取方案参数字段定义
     * 
     * @return array 字段定义数组
     */
    public function getSchemeParameterFields(): array
    {
        return [
            "CI" => [
                "label" => "GDTI (253)",
                "description" => "Company Prefix + Document Type Identifier",
                "maxLength" => 13,
                "minLength" => 13,
                "type" => "string",
                "pattern" => "/^\d{13}$/",
                "message" => "Must be exactly 13 digits (Company Prefix + Document Type)",
                "hasCompanyPrefix" => true,
                "applicationIdentifier" => "253",
                "example" => "1234567123456"
            ],
            "serial" => [
                "label" => "Serial Number",
                "description" => "Document serial number",
                "maxLength" => 17,
                "minLength" => 1,
                "type" => "string",
                "pattern" => "/^[!%-?A-Z_a-z\x22]{1,17}$/",
                "message" => "Serial must be 1-17 characters (alphanumeric and special chars)",
                "hasCompanyPrefix" => false,
                "applicationIdentifier" => "",
                "example" => "DOC001"
            ]
        ];
    }

    /**
     * 获取支持的标签大小选项
     * 
     * @return array 标签大小选项
     */
    public function getTagSizeOptions(): array
    {
        return [
            96 => "96 bits (fixed serial length)",
            113 => "113 bits (variable serial length)"
        ];
    }

    /**
     * 获取支持的过滤值选项
     * 
     * @return array 过滤值选项
     */
    public function getFilterValueOptions(): array
    {
        return [
            0 => "All Others",
            1 => "Reserved",
            2 => "Reserved",
            3 => "Reserved",
            4 => "Reserved",
            5 => "Reserved",
            6 => "Reserved",
            7 => "Reserved"
        ];
    }

    /**
     * GDTI不需要校验位计算
     * 
     * @param string $number 未使用
     * @return string 空字符串
     */
    public function getCheckDigit(string $number): string
    {
        // GDTI doesn't require check digit calculation per GS1 specification
        return "";
    }

    /**
     * 验证GDTI数据
     * 
     * @return bool 验证是否成功
     */
    private function validateGdti(): bool
    {
        if (strlen($this->CI) !== 13) {
            $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "GDTI CI must be 13 digits");
            return false;
        }

        if (!preg_match('/^\d{13}$/', $this->CI)) {
            $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "GDTI CI must contain only digits");
            return false;
        }

        return true;
    }

    /**
     * 编码GDTI数据为EPC格式
     * 将公司前缀、文档类型和序列号转换为EPC二进制、URI和十六进制格式
     * @return static 返回自身
     */
    public function encode(): static
    {
        // 验证GDTI数据
        if (!$this->validateGdti()) {
            return $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "Invalid GDTI data");
        }

        $companyPrefixLength = $this->companyPrefixLength;

        // 从CI中提取公司前缀和文档类型
        $companyPrefix = substr($this->CI, 0, $companyPrefixLength);
        $doctype = substr($this->CI, $companyPrefixLength);

        $this->setCompanyPrefix($companyPrefix);
        $this->setItemReference($doctype);

        // 获取编码标准配置
        $standard = $this->getEpcStandard('BINARY');
        if (empty($standard)) {
            return $this->setError(EpcMesg::EPC_SCHEMA_UNSUPPORT, "GDTI-{$this->tagSize}");
        }

        $prefixMatch = $standard['prefixMatch'];

        // 验证公司前缀长度是否在支持范围内
        if (!array_key_exists($companyPrefixLength, $standard['option'])) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "companyPrefixLength (must be 6-12)");
        }

        $field = $standard['option'][$companyPrefixLength]["field"];

        // 提取字段配置
        $filterLength = $field['filter']['bitLength'];
        $companyLength = $field['gs1companyprefix']['bitLength'];
        $doctypeLength = $field['doctype']['bitLength'];
        $serialLength = $field['serial']['bitLength'];

        // 编码各个字段为二进制
        $filterBin = str_pad(decbin($this->filterValue), $filterLength, '0', STR_PAD_LEFT);
        $partitionBin = $standard['option'][$companyPrefixLength]['filter'];
        $companyBin = str_pad(decbin((int)$companyPrefix), $companyLength, '0', STR_PAD_LEFT);
        $doctypeBin = str_pad(decbin((int)$doctype), $doctypeLength, '0', STR_PAD_LEFT);

        // 序列号编码（96位固定长度，113位可变长度）
        if ($this->tagSize == 96) {
            $serialBin = str_pad(decbin((int)$this->serial), $serialLength, '0', STR_PAD_LEFT);
        } else {
            $serialBin = EpcSpec::encodingString($this->serial);
            if (empty($serialBin)) {
                return $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "Invalid serial number");
            }
            $serialBin = str_pad($serialBin, $serialLength, '0', STR_PAD_LEFT);
        }

        // 计算总长度并对齐到16位边界
        $totalLength = ceil($this->tagSize / 16) * 16;
        $binary = $prefixMatch . $filterBin . $partitionBin . $companyBin . $doctypeBin . $serialBin;

        // 调整二进制字符串长度
        if (strlen($binary) < $totalLength) {
            $binary = str_pad($binary, $totalLength, '0', STR_PAD_RIGHT);
        } elseif (strlen($binary) > $totalLength) {
            $binary = substr($binary, 0, $totalLength);
        }

        // 转换为十六进制
        $hexadecimal = EpcSpec::numberBaseConvert($binary, 2, 16);

        // 生成各种URI格式
        $uri = sprintf(
            "%s:%s:%s.%s.%s",
            $this->uriPrefix,
            strtolower($this->scheme),
            $this->companyPrefix,
            $this->itemReference,
            $this->serial
        );

        $tagUri = sprintf(
            "%s:%s-%d:%s.%s.%s.%s",
            $this->tagPrefix,
            strtolower($this->scheme),
            $this->tagSize,
            $this->filterValue,
            $this->companyPrefix,
            $this->itemReference,
            $this->serial
        );

        $rawUri = sprintf(
            "%s:%s-%d:%s.%s.%s.%s.%s",
            $this->rawPrefix,
            strtolower($this->scheme),
            $this->tagSize,
            $this->filterValue,
            $this->companyPrefix,
            $this->itemReference,
            $this->serial,
            $this->serial
        );

        return $this->setEpcBinary($binary)
            ->setEpcURI($uri)
            ->setEpcTagURI($tagUri)
            ->setEpcRawURI($rawUri)
            ->setEpcHexaDecimal($hexadecimal);
    }

    /**
     * 从十六进制字符串解码GDTI数据
     * 
     * @param string $epcHex EPC十六进制字符串
     * @return static 返回Gdti实例
     */
    public static function decode(string $epcHex): static
    {
        $instance = new self();
        try {
            // 验证十六进制格式
            if (!EpcSpec::isHexChars($epcHex)) {
                return $instance->setError(EpcMesg::EPC_HEX_FORMAT_ERROR);
            }

            $instance->setEpcHexaDecimal($epcHex);

            // 解析头部信息
            $headerHex = substr($epcHex, 0, 2);
            $instance->setHeaderStruct($headerHex);

            $headerStruct = $instance->getHeaderStruct();
            if (empty($headerStruct)) {
                return self::setError(EpcMesg::EPC_HEADER_ERROR);
            }

            // 转换并验证二进制长度
            $epcBinary = EpcSpec::numberBaseConvert($epcHex, 16, 2);
            $expectedLength = $headerStruct['tagSize'];
            $paddedBinary = str_pad($epcBinary, $expectedLength, '0', STR_PAD_LEFT);

            if (strlen($paddedBinary) !== $expectedLength) {
                return $instance->setError(EpcMesg::EPC_BINARY_LENGTH_ERROR);
            }

            $instance->setEpcBinary($paddedBinary)
                ->setScheme()
                ->setEncodeScheme()
                ->setTagSize($expectedLength);

            // 获取编码标准
            $pattern = $instance->getEpcStandard('BINARY');
            if (empty($pattern)) {
                return $instance->setError(EpcMesg::EPC_STANDARD_ERROR);
            }

            // 解析各个字段
            $filterBin = substr($paddedBinary, 8, 3);
            $partitionBin = substr($paddedBinary, 11, 3);

            // 查找对应的分段表
            $segmentTable = null;
            foreach ($pattern['option'] as $val) {
                if ($val["filter"] === $partitionBin) {
                    $segmentTable = $val;
                    break;
                }
            }

            if ($segmentTable === null) {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            $tableField = $segmentTable['field'];
            $companyPrefixLength = $segmentTable['optionKey'];

            // 提取各个字段
            $offset = 14;
            $companyPrefixBin = substr($paddedBinary, $offset, $tableField['gs1companyprefix']['bitLength']);
            $offset += $tableField['gs1companyprefix']['bitLength'];

            $doctypeBin = substr($paddedBinary, $offset, $tableField['doctype']['bitLength']);
            $offset += $tableField['doctype']['bitLength'];

            $serialBin = substr($paddedBinary, $offset, $tableField['serial']['bitLength']);

            // 转换二进制为十进制
            $companyPrefix = str_pad((string)bindec($companyPrefixBin), $companyPrefixLength, '0', STR_PAD_LEFT);
            $doctype = str_pad((string)bindec($doctypeBin), 13 - $companyPrefixLength, '0', STR_PAD_LEFT);

            // 解码序列号
            $serial = ($instance->tagSize == 96)
                ? (string)bindec($serialBin)
                : EpcSpec::decodingString($serialBin);

            if ($serial === false || ($serial === '' && $instance->tagSize != 96)) {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            // 重建CI（公司前缀 + 文档类型）
            $ci = $companyPrefix . $doctype;

            // 转换序列号为URI格式
            $uriSerial = EpcSpec::stringElement2Uri($serial);
            if ($uriSerial === false || $uriSerial === null) {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            // 设置所有属性
            return $instance->setFilterValue(bindec($filterBin))
                ->setCompanyPrefixLength($companyPrefixLength)
                ->setCompanyPrefix($companyPrefix)
                ->setItemReference($doctype)
                ->setSerial($uriSerial)
                ->setCI($ci)
                ->setURI()
                ->setTagURI()
                ->setEpcHexaDecimal($epcHex);
        } catch (\Exception $e) {
            return $instance->setError(EpcMesg::EPC_PARSER_ERROR, "Exception during GDTI decoding: " . $e->getMessage());
        }
    }

    /**
     * 设置EPC URI（用于解码后重建）
     * 
     * @param string $uri URI字符串，为空时自动生成
     * @return static 返回自身以支持链式调用
     */
    public function setURI(?string $uri = NULL): static
    {
        if (empty($uri)) {
            $uri = sprintf(
                "%s:%s:%s.%s.%s",
                $this->uriPrefix,
                strtolower($this->scheme),
                $this->companyPrefix,
                $this->itemReference,
                $this->serial
            );
        }
        $this->epcURI = $uri;
        return $this;
    }

    /**
     * 设置EPC Tag URI（用于解码后重建）
     * 
     * @param string $tagUri Tag URI字符串，为空时自动生成
     * @return static 返回自身以支持链式调用
     */
    public function setTagURI(string $tagUri = ""): static
    {
        if (empty($tagUri)) {
            $tagUri = sprintf(
                "%s:%s-%d:%s.%s.%s.%s",
                $this->tagPrefix,
                strtolower($this->scheme),
                $this->tagSize,
                $this->filterValue,
                $this->companyPrefix,
                $this->itemReference,
                $this->serial
            );
        }
        $this->epcTagURI = $tagUri;
        return $this;
    }

    /**
     * 设置EPC Raw URI（用于解码后重建）
     * 
     * @param string $rawUri Raw URI字符串
     * @return static 返回自身以支持链式调用
     */
    public function setRawURI(string $rawUri): static
    {
        $this->epcRawURI = $rawUri;
        return $this;
    }

    /**
     * 设置十六进制值（别名方法，用于向后兼容）
     * 
     * @param string $epcHex 十六进制字符串
     * @return static 返回自身以支持链式调用
     */
    public function setHexaDecimal(string $epcHex): static
    {
        return $this->setEpcHexaDecimal($epcHex);
    }
}
