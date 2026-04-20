<?php

namespace Mickeywaugh\Gs1\Epc;

use Mickeywaugh\Gs1\Epc\EpcBase;
use Mickeywaugh\Gs1\Epc\EpcMesg;
use Mickeywaugh\Gs1\Spec\EpcSpec;

/**
 * Description:
 * EPC class for GDTI (Global Document Type Identifier) scheme
 * According to specification Gs1 EPC Tag Data Standard Release 1.13 
 * Author: Mickeywaugh <Mickeywaugh at qq dot com>
 */

class Gdti extends EpcBase
{

    // scheme Gdti parameters
    private $CI = "";

    public function __construct(int $companyPrefixLength = 0, int $tagSize = 0, int $filterValue = 0, array $schemeParameters = [])
    {
        $this->setScheme("GDTI")
            ->setCompanyPrefixLength($companyPrefixLength)
            ->setTagSize($tagSize)
            ->setFilterValue($filterValue)
            ->setSchemeParameters($schemeParameters);
    }

    /** 
     * @param _schemeParameters check and set the input fields for current EPC scheme:GDTI;
     * @return this EpcGdti class entity with valide filter value;
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
                "label" => "GDTI (253)",
                "max" => 999999999999999,
                "min" => 0,
                "type" => "int",
                "pattern" => "\/^[0-9]{13}$\/g",
                "msg" => "Should be exactly 13 digits long (Company Prefix + Document Type)",
                "id" => "inputCI",
                "hascompany" => true,
                "AI" => "253"
            ],
            "serial" => [
                "label" => "Serial",
                "max" => 81985529216486895,
                "min" => 0,
                "type" => "int",
                "pattern" => "\/^[!%-?A-Z_a-z\\x22]{1,17}$\/g",
                "msg" => "Should be 1~17 characters long",
                "hascompany" => false,
                "AI" => ""
            ]
        ];
    }

    // Get tag size(Epc binary coding scheme) options for current EPC scheme
    public function getTagSizeOptions(): array
    {
        return [
            "96" => "96 bits",
            "113" => "113 bits"
        ];
    }

    // Get Filter values options for current EPC scheme
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
     * GDTI does not use check digit, this method is implemented to satisfy interface requirement
     * @param string $num Not used in GDTI
     * @return string Empty string
     */
    public function getCheckDigit(string $num): string
    {
        // GDTI doesn't require check digit calculation
        return "";
    }


    /**
     * @return self|null;
     */
    public function encode(): ?static
    {
        $companyPrefixLength = $this->companyPrefixLength;
        $companyPrefix = substr($this->CI, 0, $companyPrefixLength);
        $doctype = substr($this->CI, $companyPrefixLength);

        $this->setCompanyPrefix($companyPrefix);
        $this->setItemReference($doctype);

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
        $doctypeLength = $field['doctype']['bitLength'];
        $doctypePad = $field['doctype']['bitPadDir'] == 'LEFT' ? 0 : 1;
        $serialLength = $field['serial']['bitLength'];
        $serialPad = $field['serial']['bitPadDir'] == 'LEFT' ? 0 : 1;

        //filter 二进制
        $filterBin = str_pad(decbin($this->filterValue), $filterLength, 0, $filterPad);
        //partition 二进制
        $partitionBin = $standard['option'][$companyPrefixLength]['filter'];
        //company 二进制
        $companyBin = str_pad(decbin($companyPrefix), $companyLength, 0, $companyPad);
        //doctype 二进制
        $doctypeBin = str_pad(decbin($doctype), $doctypeLength, 0, $doctypePad);

        //serial 二进制
        if ($this->tagSize == 96) {
            $serialBin = str_pad(decbin($this->serial), $serialLength, 0, $serialPad);
        } else {
            $serialBin = EpcSpec::encodingString($this->serial);
            if (!$serialBin) return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "[TAG SIZE]");
            $serialBin = str_pad($serialBin, $serialLength, 0, $serialPad);
        }

        $length = ceil($this->tagSize / 16) * 16;

        $binary = $prefixMatch . $filterBin . $partitionBin . $companyBin . $doctypeBin . $serialBin;

        if (strlen($binary) <= $length) {
            $binary = str_pad($binary, $length, 0, 1);
        } else {
            $binary = substr($binary, 0, $length);
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
            $padedEpcBinary = substr('00' . $epcBinary, 0, $headerStruct['tagSize']);
            if (!$padedEpcBinary || strlen($padedEpcBinary) != $headerStruct['tagSize']) {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            $instance->setEpcBinary($epcBinary)
                ->setEncodeScheme()
                ->setTagSize($headerStruct['tagSize']);

            $pattern = $instance->getEpcStandard('BINARY');
            //截取filter 二进制段
            $filterBin = substr($epcBinary, 8, 3);
            //截取partition 二进制段
            $partitionBin =  substr($epcBinary, 11, 3);
            //获取二进制分段表;
            $segmentTable = [];

            foreach ($pattern['option'] as $val) {
                if ($val["filter"] == $partitionBin) {
                    $segmentTable = $val;
                    break;
                }
            }

            $tableFiled = $segmentTable['field'];
            //截取company prefix 二进制段
            $companyPrefixBin = substr($epcBinary, 14, $tableFiled['gs1companyprefix']['bitLength']);
            //截取doctype 二进制段
            $doctypeBin = substr($epcBinary, 14 + $tableFiled['gs1companyprefix']['bitLength'], $tableFiled['doctype']['bitLength']);
            //截取serial 二进制段
            $serialBin = substr($epcBinary, 14 + $tableFiled['gs1companyprefix']['bitLength'] + $tableFiled['doctype']['bitLength'], $tableFiled['serial']['bitLength']);
            $companyPrefixLength = $segmentTable['optionKey'];

            $companyPrefix = str_pad(bindec($companyPrefixBin), $companyPrefixLength, 0, 0);
            $doctype = str_pad(bindec($doctypeBin), 13 - $companyPrefixLength, 0, 0);

            $instance->setFilterValue(bindec($filterBin))
                ->setCompanyPrefixLength($companyPrefixLength)
                ->setCompanyPrefix($companyPrefix)
                ->setItemReference($doctype);

            //校验serial段二进制
            if ($instance->tagSize == 96) {
                $serial = bindec($serialBin);
            } else {
                $serial = EpcSpec::decodingString($serialBin);
            }
            if (!$serial && $serial !== "0") {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            $CI = $instance->companyPrefix . $instance->itemReference;

            $uriSerial = EpcSpec::stringElement2Uri($serial);
            if ($uriSerial === false || $uriSerial === null) {
                return $instance->setError(EpcMesg::EPC_BINARY_FORMAT_ERROR);
            }

            //检验通过后set serial;
            return $instance->setSerial($uriSerial)
                ->setCI($CI)
                ->setURI()
                ->setTagURI()
                ->setHexaDecimal($epcHex);
        } catch (\Exception $e) {
            return null;
        }
    }
}
