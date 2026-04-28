<?php

namespace Mickeywaugh\Gs1\Epc;

use Mickeywaugh\Gs1\Epc\EpcBase;
use Mickeywaugh\Gs1\Epc\EpcMesg;
use Mickeywaugh\Gs1\Spec\EpcSpec;

/**
 * SGTIN (Serialized Global Trade Item Number) EPC编码类
 * 
 * 实现GS1标准下带序列号的贸易项目标识编码
 * 支持96位和198位标签格式
 * 
 * @package Mickeywaugh\Gs1\Epc
 * @author Mickeywaugh <mickeywaugh@qq.com>
 * @license MIT
 * @link https://www.gs1.org/standards/epc-rfid-tag-data-standard
 */
class Sgtin extends EpcBase
{
    /**
     * 构造函数
     * @param array $schemeParameters 方案参数，必须包含'CI'(GTIN)和'serial'(序列号)
     * @param int $tagSize 标签大小，96或198位，默认96
     * @param int $filterValue 过滤值，0-7，默认1（POS零售商品）
     */
    public function __construct(
        array $schemeParameters = ["CI" => "", "serial" => ""],
        int $tagSize = 96,
        int $filterValue = 1
    ) {
        $this->setScheme("SGTIN")
            ->setTagSize($tagSize)
            ->setFilterValue($filterValue)
            ->setSchemeParameters($schemeParameters);
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

        // 验证GTIN格式（14位数字）
        $ci = (string)$schemeParameters['CI'];
        if (!preg_match('/^\d{14}$/', $ci)) {
            return $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "CI (GTIN must be 14 digits)");
        }

        return $this->setCI($ci)->setSerial((string)$schemeParameters['serial']);
    }

    /**
     * 获取方案参数字段定义
     * @return array 字段定义数组
     */
    public function getSchemeParameterFields(): array
    {
        return [
            "CI" => [
                "label" => "GTIN (01)",
                "description" => "Global Trade Item Number with check digit",
                "maxLength" => 14,
                "minLength" => 14,
                "type" => "string",
                "pattern" => "/^\d{14}$/",
                "message" => "GTIN must be exactly 14 digits",
                "hasCompanyPrefix" => true,
                "applicationIdentifier" => "01",
                "example" => "01234567890128"
            ],
            "serial" => [
                "label" => "Serial Number (21)",
                "description" => "Serial number for item identification",
                "maxLength" => 20,
                "minLength" => 1,
                "type" => "string",
                "pattern" => "/^[!%-?A-Z_a-z\x22]{1,20}$/",
                "message" => "Serial must be 1-20 characters (alphanumeric and special chars)",
                "hasCompanyPrefix" => false,
                "applicationIdentifier" => "21",
                "example" => "ABC123"
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
            198 => "198 bits (variable serial length)"
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
            1 => "Point of Sale (POS) Trade Item",
            2 => "Full Case for Transport",
            3 => "Reserved",
            4 => "Inner Pack Trade Item Grouping",
            5 => "Reserved",
            6 => "Unit Load",
            7 => "Component inside a product not intended for individual sale"
        ];
    }

    /**
     * 计算GTIN校验位
     * 使用模10算法计算最后一位校验位
     * @param string $number 13位GTIN数字字符串（不含校验位）
     * @return string 校验位数字（0-9）
     * 
     */
    public function getCheckDigit(string $number): string
    {
        // 确保输入是13位数字
        $number = preg_replace('/\D/', '', $number);
        if (strlen($number) !== 13) {
            return '0';
        }

        $digits = str_split($number);
        $sum = 0;

        // GS1校验位算法：奇数位*3 + 偶数位*1
        for ($i = 0; $i < 13; $i++) {
            $sum += ($i % 2 === 0) ? (int)$digits[$i] * 3 : (int)$digits[$i];
        }

        $checkDigit = (10 - ($sum % 10)) % 10;
        return (string)$checkDigit;
    }

    /**
     * 验证GTIN并提取公司前缀和项目参考
     * 
     * @return bool 验证是否成功
     */
    private function validateAndExtractGtin(): bool
    {
        if (strlen($this->CI) !== 14) {
            $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "GTIN must be 14 digits");
            return false;
        }

        // 验证校验位
        $checkDigit = substr($this->CI, -1);
        $calculatedCheckDigit = $this->getCheckDigit(substr($this->CI, 0, 13));

        if ($checkDigit !== $calculatedCheckDigit) {
            $this->setError(EpcMesg::PARAM_FORMAT_ERROR, "Invalid GTIN check digit");
            return false;
        }

        return true;
    }

    /**
     * 编码SGTIN数据为EPC格式
     * 将GTIN和序列号转换为EPC二进制、URI和十六进制格式
     * @return static 返回自身
     */
    public function encode(): static
    {
        // 验证GTIN
        if (!$this->validateAndExtractGtin()) {
            return $this->setError(EpcMesg::EPC_ENCODING_ERROR);
        }

        $companyPrefixLength = $this->getCompanyPrefixLength();

        // 从GTIN中提取公司前缀和项目参考
        // GTIN结构: 指示符(1位) + 公司前缀 + 项目参考 + 校验位(1位)
        $companyPrefix = substr($this->CI, 1, $companyPrefixLength);
        $itemReference = substr($this->CI, 0, 1) . substr($this->CI, $companyPrefixLength + 1, -1);

        $this->setCompanyPrefix($companyPrefix);
        $this->setItemReference($itemReference);

        // 获取编码标准配置
        $standard = $this->getEpcStandard('BINARY');
        if (empty($standard)) {
            return $this->setError(EpcMesg::EPC_SCHEMA_UNSUPPORT, "SGTIN-{$this->tagSize}");
        }

        $prefixMatch = $standard['prefixMatch'];

        // 验证公司前缀长度是否在支持范围内
        if (!array_key_exists($companyPrefixLength, $standard['option'])) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "companyPrefixLength (must be 6-12) now is {$companyPrefixLength}");
        }

        $field = $standard['option'][$companyPrefixLength]["field"];

        // 提取字段配置
        $filterLength = $field['filter']['bitLength'];
        $companyLength = $field['gs1companyprefix']['bitLength'];
        $itemLength = $field['itemref']['bitLength'];
        $serialLength = $field['serial']['bitLength'];

        // 编码各个字段为二进制
        $filterBin = str_pad(decbin($this->filterValue), $filterLength, '0', STR_PAD_LEFT);
        $partitionBin = $standard['option'][$companyPrefixLength]['filter'];
        $companyBin = str_pad(decbin((int)$companyPrefix), $companyLength, '0', STR_PAD_LEFT);
        $itemBin = str_pad(decbin((int)$itemReference), $itemLength, '0', STR_PAD_LEFT);

        // 序列号编码（96位固定长度，198位可变长度）
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
        $binary = $prefixMatch . $filterBin . $partitionBin . $companyBin . $itemBin . $serialBin;

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
     * 从十六进制字符串解码SGTIN数据
     * @param string $epcHex EPC十六进制字符串
     * @return static  返回Sgtin实例，如果解码失败则包含错误信息
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
                return $instance->setError(EpcMesg::EPC_HEADER_ERROR);
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

            $itemReferBin = substr($paddedBinary, $offset, $tableField['itemref']['bitLength']);
            $offset += $tableField['itemref']['bitLength'];

            $serialBin = substr($paddedBinary, $offset, $tableField['serial']['bitLength']);

            // 转换二进制为十进制
            $companyPrefix = str_pad((string)bindec($companyPrefixBin), $companyPrefixLength, '0', STR_PAD_LEFT);
            $itemReference = str_pad((string)bindec($itemReferBin), 13 - $companyPrefixLength, '0', STR_PAD_LEFT);

            // 解码序列号
            $serial = ($instance->tagSize == 96)
                ? (string)bindec($serialBin)
                : EpcSpec::decodingString($serialBin);

            if ($serial === false || $serial === '') {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            // 重建GTIN
            $gtinWithoutCheck = substr($itemReference, 0, 1) . $companyPrefix . substr($itemReference, 1);
            $checkDigit = $instance->getCheckDigit($gtinWithoutCheck);
            $gtin = $gtinWithoutCheck . $checkDigit;

            // 转换序列号为URI格式
            $uriSerial = EpcSpec::stringElement2Uri($serial);
            if ($uriSerial === '') {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            // 设置所有属性
            return $instance->setFilterValue(bindec($filterBin))
                ->setCompanyPrefixLength($companyPrefixLength)
                ->setCompanyPrefix($companyPrefix)
                ->setItemReference($itemReference)
                ->setSerial($uriSerial)
                ->setCI($gtin)
                ->setURI()
                ->setTagURI()
                ->setEpcHexaDecimal($epcHex);
        } catch (\Exception $e) {
            return $instance->setError(EpcMesg::EPC_PARSER_ERROR, "Exception during SGTIN decoding: " . $e->getMessage());
        }
    }

    /**
     * 设置EPC URI（用于解码后重建）
     * 
     * @param string $uri URI字符串，为空时自动生成
     * @return static 返回自身以支持链式调用
     */
    public function setURI(?string $uri = ""): static
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
    public function setTagURI(?string $tagUri = ""): static
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
