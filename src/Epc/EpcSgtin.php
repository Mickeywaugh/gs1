<?php

namespace Mickeywaugh\Gs1\Epc;

use Mickeywaugh\Gs1\Epc\EpcBase;
use Mickeywaugh\Gs1\EpcSpec;

/**
 * Description:
 * class for EPC schemes: SGTIN
 * 
 * According to specification Gs1 EPC Tag Data Standard Release 1.13 
 * 
 * Author: Mickey Wu <Mickey dot Wu at boingtech dot com>
 * Copyright (c) 2024- Boing Technologies Ltd.  All rights reserved.
 */

class EpcSgtin extends EpcBase
{

    // scheme Sgtin parameters
    private $CI = "";

    public function __construct(int $companyPrefixLength = 0, int $tagSize = 0, int $filterValue = 0, array $schemeParameters = [])
    {
        $this->setScheme("SGTIN")
            ->setCompanyPrefixLength($companyPrefixLength)
        ;
    }

    public static function getInstance(int $companyPrefixLength = 0, int $tagSize = 0, int $filterValue = 0, array $schemeParameters = []): ?self
    {
        $instance = new self();
        return $instance->setScheme("SGTIN")
            ->setCompanyPrefixLength($companyPrefixLength)
            ->setTagSize($tagSize)
            ->setFilterValue($filterValue)
            ->setSchemeParameters($schemeParameters)
        ;
    }
    /** 
     * @param _schemeParameters check and set the input fields for current EPC scheme:SGTIN;
     * @return this EpcSgtin class entity with valide filter value;
     */
    public function setSchemeParameters($_schemeParameters): ?static
    {
        $params = ["CI", "serial"];
        foreach ($params as $param) {
            if (!key_exists($param, $_schemeParameters)) {
                return $this->setError(EpcMesg::EPC_OPTION_MISSING, $param);
            }
            if (empty($_schemeParameters[$param])) {
                return $this->setError(EpcMesg::EPC_OPTION_SHOULD_NOT_EMPTY, $param);
            }
        }
        return $this->setCI($_schemeParameters['CI'])->setSerial($_schemeParameters['serial']);
    }

    public function setCI($CI): static
    {
        $this->CI = $CI;
        return $this;
    }

    public function getCI(): string
    {
        return $this->CI;
    }

    // Encoding EPC Pure Identify URI
    public function setURI(string $_uri = ""): static
    {
        $this->epcURI = $_uri ?: sprintf("%s:%s:%s.%s.%s", $this->uriPrefix, $this->scheme, $this->companyPrefix, $this->itemReference, $this->serial);
        return $this;
    }

    // Encoding EPC Tag URI
    public function setTagURI(string $tagUri = ""): static
    {

        $this->epcTagURI = $tagUri ?: sprintf("%s:%s-%d:%s.%s.%s.%s", $this->tagPrefix, $this->scheme, $this->tagSize, $this->filterValue, $this->companyPrefix, $this->itemReference, $this->serial);
        return $this;
    }

    // Encoding EPC Raw URI
    public function setRawURI(string $_rawUri): static
    {
        $this->epcRawURI = $_rawUri;
        return $this;
    }

    // Encoding Hex Code for Tag memory bank(Where the binary code saved);
    public function setHexaDecimal(string $epcHex): static
    {
        $this->epcHexaDecimal = $epcHex;
        return $this;
    }

    // 
    public function getSchemeParameterFields(): array
    {
        return [
            "CI" => [
                "label" => "GTIN (01)",
                "max" => 99999999999999,
                "min" => 0,
                "type" => "int",
                "pattern" => "\/^[0-9]{14}$\/g",
                "msg" => "Should be exactly 14 digits long",
                "id" => "inputCI",
                "hascompany" => true,
                "AI" => "01"
            ],
            "serial" => [
                "label" => "Serial (21)",
                "max" => 274877906943,
                "min" => 0,
                "type" => "int",
                "pattern" => "\/^[!%-?A-Z_a-z\\x22]{1,20}$\/g",
                "msg" => "Should be 1~20 digits long",
                "hascompany" => false,
                "AI" => "21"
            ]
        ];
    }

    // Get tag size(Epc binary coding scheme) options for current EPC scheme
    public function getTagSizeOptions(): array
    {
        return [
            "96" => "96 bits",
            "198" => "198 bits"
        ];
    }

    // Get Filter values options for current EPC scheme
    public function getFilterValueOptions(): array
    {
        return [
            0 => "All Others",
            1 => "Point of Sale (POS) Trade Item",
            2 => "Full Case for Transport",
            3 => "Reserved",
            4 => "Inner Pack Trade Item Grouping for Handling 4 100",
            5 => "Reserved",
            6 => "Unit Load",
            7 => "Unit inside Trade Item or component inside a product not intended for individual sale"
        ];
    }


    /**
     * @param $num Company Prefix and indicator/item reference;
     * @return string;
     */
    public function getCheckDigit(string $num): string
    {
        $CIArr = str_split($num);
        $checkDigit =  (10 - ((3 * ($CIArr[0] + $CIArr[2] + $CIArr[4] + $CIArr[6] + $CIArr[8] + $CIArr[10] + $CIArr[12]) + ($CIArr[1] + $CIArr[3] + $CIArr[5] +
            $CIArr[7] + $CIArr[9] + $CIArr[11])) % 10)) % 10;
        return $checkDigit;
    }

    /**
     * @return self|null;
     */
    public function encode(): ?static
    {
        // EpcUtil::log(sprintf("CI: %s", $this->CI));
        $companyPrefixLength = $this->companyPrefixLength;
        $companyPrefix = substr($this->CI, 1, $companyPrefixLength);
        $itemReference = substr($this->CI, 0, 1) . substr($this->CI, $companyPrefixLength + 1, -1);
        $this->setCompanyPrefix($companyPrefix);
        $this->setItemReference($itemReference);
        $standard = $this->getEpcStandard('BINARY');
        $prefixMatch = $standard['prefixMatch'];
        if (!array_key_exists($companyPrefixLength, $standard['option'])) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "[companyPrefixLength]");
        }

        $field = $standard['option'][$companyPrefixLength]["field"];
        $filterLength = $field['filter']['bitLength'];
        $filterPad = $field['filter']['bitPadDir'] == 'LEFT' ? 0 : 1;
        $companyLength = $field['gs1companyprefix']['bitLength'];
        $companyPad = $field['gs1companyprefix']['bitPadDir'] == 'LEFT' ? 0 : 1;
        $itemLength = $field['itemref']['bitLength'];
        $itemPad = $field['itemref']['bitPadDir'] == 'LEFT' ? 0 : 1;
        $serialLength = $field['serial']['bitLength'];
        $serialPad = $field['serial']['bitPadDir'] == 'LEFT' ? 0 : 1;
        //filter 二进制
        $filterBin = str_pad(decbin($this->filterValue), $filterLength, 0, $filterPad);
        //partition 二进制
        $partitionBin = $standard['option'][$companyPrefixLength]['filter'];
        //company 二进制
        $companyBin = str_pad(decbin($companyPrefix), $companyLength, 0, $companyPad);

        //item 二进制
        $itemBin = str_pad(decbin($itemReference), $itemLength, 0, $itemPad);

        //serial 二进制
        if ($this->tagSize == 96) {
            $serialBin = str_pad(decbin($this->serial), $serialLength, 0, $serialPad);
        } else {
            $serialBin = EpcSpec::encodingString($this->serial);
            if (!$serialBin) return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "[TAG SIZE]");
            $serialBin = str_pad($serialBin, $serialLength, 0, $serialPad);
        }

        $length = ceil($this->tagSize / 16) * 16;

        $binary = $prefixMatch . $filterBin . $partitionBin . $companyBin . $itemBin . $serialBin;

        // EpcUtil::log(sprintf("output1: %s", $binary));
        if (strlen($binary) <= $length) {
            $binary = str_pad($binary, $length, 0, 1);
            // EpcUtil::log(sprintf("output2: %s", $binary));
        } else {
            $binary = substr($binary, 0, $length);
            // EpcUtil::log(sprintf("output3: %s", $binary));
        }

        $hexadecimal = EpcSpec::numberBaseConvert($binary, 2, 16);

        return  $this->setEpcBinary($binary)
            ->setURI(sprintf("%s:%s:%s.%s.%s", $this->uriPrefix, $this->scheme, $this->companyPrefix, $this->itemReference, $this->serial))
            ->setTagURI(sprintf("%s:%s-%d:%s.%s.%s.%s", $this->tagPrefix, $this->scheme, $this->tagSize, $this->filterValue, $this->companyPrefix, $this->itemReference, $this->serial))
            ->setEpcRawURI(sprintf("%s:%s-%d:%s.%s.%s.%s.%s", $this->rawPrefix, $this->scheme, $this->tagSize, $this->filterValue, $this->companyPrefix, $this->itemReference, $this->serial))
            ->setEpcHexaDecimal($hexadecimal);
    }

    /**
     * @param  EpcHex  EPC Hex string
     * @return ?self
     */
    public static function decode(string $epcHex): ?self
    {
        try {
            $instance = new self();
            $instance->setEpcHexaDecimal($epcHex);
            // get header form hex string;
            $headerHex = mb_substr($epcHex, 0, 2);
            // get epc base properties;
            $instance->setHeaderStruct($headerHex)->setScheme();

            $epcBinary = EpcSpec::numberBaseConvert($epcHex, 16, 2);
            $headerStruct = $instance->getHeaderStruct();
            // EpcUtil::log(sprintf("epcBinary: %s", $epcBinary));
            $padedEpcBinary = substr('00' . $epcBinary, 0, $headerStruct['tagSize']);
            if (!$padedEpcBinary || strlen($padedEpcBinary) != $headerStruct['tagSize']) {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            $instance->setEpcBinary($epcBinary)
                ->setEncodeScheme()
                ->setTagSize($headerStruct['tagSize']);

            $pattern = $instance->getEpcStandard('BINARY');
            // EpcUtil::debug($pattern);
            //截取filter 二进制段
            $filterBin = substr($epcBinary, 8, 3);
            // EpcUtil::log(sprintf("filterBin: %s", $filterBin));
            //截取partition 二进制段
            $partitionBin =  substr($epcBinary, 11, 3);
            // EpcUtil::log(sprintf("partitionBin: %s", $partitionBin));
            //获取二进制分段表;
            $segmentTable = [];

            foreach ($pattern['option'] as $val) {
                if ($val["filter"] == $partitionBin) {
                    $segmentTable = $val;
                    break;
                }
            }

            $tableFiled = $segmentTable['field'];
            // EpcUtil::log($segmentTable);
            //截取company prefix 二进制段
            $companyPrefixBin = substr($epcBinary, 14, $tableFiled['gs1companyprefix']['bitLength']);
            // EpcUtil::log(sprintf("companyPrefixBin: %s", $companyPrefixBin));
            //截取item reference 二进制段
            $itemReferBin = substr($epcBinary, 14 + $tableFiled['gs1companyprefix']['bitLength'], $tableFiled['itemref']['bitLength']);
            // EpcUtil::log(sprintf("itemReferBin: %s", $itemReferBin));
            //截取serial 二进制段
            $serialBin = substr($epcBinary, 14 + $tableFiled['gs1companyprefix']['bitLength'] + $tableFiled['itemref']['bitLength'], $tableFiled['serial']['bitLength']);
            // EpcUtil::log(sprintf("serialBin: %s", $serialBin));
            $companyPrefixLength = $segmentTable['optionKey'];

            // EpcUtil::log(sprintf("companyPrefixLength: %s", $companyPrefixLength));
            // EpcUtil::log(sprintf("companyPrefixDec: %s", bindec($companyPrefixBin)));
            $companyPrefix = str_pad(bindec($companyPrefixBin), $companyPrefixLength, 0, 0);
            // EpcUtil::log(sprintf("companyPrefix: %s", $companyPrefix));

            $instance->setFilterValue(bindec($filterBin))
                ->setCompanyPrefixLength($companyPrefixLength)
                ->setCompanyPrefix($companyPrefix)
                ->setItemReference(str_pad(bindec($itemReferBin), 13 - $companyPrefixLength, 0, 0));

            //校验serial段二进制
            if ($instance->tagSize == 96) {
                $serial = bindec($serialBin);
            } else {
                $serial = EpcSpec::decodingString($serialBin);
            }
            // EpcUtil::log(sprintf("serial: %s", $serial));
            if (!$serial) {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            $CI = substr($instance->itemReference, 0, 1) . $instance->companyPrefix . substr($instance->itemReference, 1);
            $checkDigit = $instance->getCheckDigit($CI);
            $CI = $CI . $checkDigit;

            $uriSerial = EpcSpec::stringElement2Uri($serial);
            // EpcUtil::log(sprintf("uriSerial: %s", $uriSerial));
            if ($uriSerial == "") {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }
            //检验通过后set serial;
            return $instance->setSerial($uriSerial)
                ->setURI()
                ->setTagURI()
                ->setHexaDecimal($epcHex);
        } catch (\Exception $e) {
            return null;
        }
    }
}
