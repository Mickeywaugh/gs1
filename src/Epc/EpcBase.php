<?php

namespace Mickeywaugh\Gs1\Epc;

use Mickeywaugh\Gs1\Epc\EpcInterface;
use Mickeywaugh\Gs1\Epc\EpcMesg;
use Mickeywaugh\Gs1\EpcSpec;

/**
 * Description:
 * EPC base class for all supported EPC schemes 
 * 
 * According to specification Gs1 EPC Tag Data Standard Release 1.13 
 * 
 * Author: Mickey Wu <Mickey dot Wu at boingtech dot com>
 * Copyright (c) 2024- Boing Technologies Ltd.  All rights reserved.
 */

abstract class EpcBase implements EpcInterface
{
    protected $uriPrefix = 'urn:epc:id';
    protected $tagPrefix = 'urn:epc:tag';
    protected $rawPrefix = "urn:epc:raw";

    #support EPC Tag data translation schemes;
    const SupportSchemes = ["Gs1" => [
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
    ], "Others" => [
        "gid" => "GID",
        "usdod" => "USDoD",
        "adi" => "ADI"
    ]];

    // EPC parameter options
    protected $companyPrefixLengthOptions = [6, 7, 8, 9, 10, 11, 12];
    protected $tagSizeOptions = [
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
    protected $filterValueOptions = [
        0 => "All Others",
        1 => "Reserved",
        2 => "Reserved",
        3 => "Reserved",
        4 => "Reserved",
        5 => "Reserved",
        6 => "Reserved",
        7 => "Reserved"
    ];

    // EPC parameters
    protected $scheme;
    protected $encodeScheme;
    protected $schemeParameters = [];
    protected $companyPrefixLength = 0, $tagSize = 0, $filterValue = 0;
    protected $companyPrefix = "";
    protected $itemReference = ""; //indicator or item reference
    protected $serial = "";

    // EPC instance properties, $epcURI means EPC Pure Identify URI;
    protected $epcURI = "", $epcTagURI = "", $epcRawURI = "", $epcBinary = "", $epcHexaDecimal = "", $errorCode = null, $errorMsg = null;

    protected $headerStruct = [];

    public function setError(int $_errorCode, ...$parameters): null
    {
        $this->errorCode = $_errorCode;
        $this->errorMsg = EpcMesg::getMessage($_errorCode, $parameters);
        return null;
    }

    public function getErrorMsg(): ?string
    {
        return $this->errorMsg;
    }

    /** 
     * @param _scheme set the current EPC class entity scheme;
     * @return this Epc class entity with valide scheme;
     */
    public function setScheme(?string $_scheme = ""): ?static
    {
        if (!$_scheme) {
            if (isset($this->headerStruct['scheme'])) {
                $_scheme = $this->headerStruct['scheme'];
            } else {
                return $this->setError(EpcMesg::PARAM_MISSING, $_scheme);
            };
        }
        if (!is_string($_scheme)) {
            return $this->setError(EpcMesg::PARAM_TYPE_ERROR, $_scheme);
        }
        if (!key_exists($_scheme, array_merge(self::SupportSchemes["Gs1"], self::SupportSchemes["Others"]))) {
            return $this->setError(EpcMesg::EPC_SCHEMA_UNSUPPORT, $_scheme);
        }
        $this->scheme = strtolower($_scheme);
        return $this;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getTagSize(): int
    {
        return $this->tagSize;
    }

    /** 
     * @param _companyPrefixLength set the length attribute of companyPrefixLength;
     * @return this Epc class entity with valide company prefix lenth value;
     */
    public function setCompanyPrefixLength($_companyPrefixLength): ?static
    {
        // //check the input company prefix lenth value is among the allowed options
        if (!in_array($_companyPrefixLength, $this->getCompanyPrefixLengthOptions())) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "Company prefix length");
        }
        $this->companyPrefixLength = $_companyPrefixLength;
        return $this;
    }

    public function getCompanyPrefixLength(): int
    {
        return $this->companyPrefixLength;
    }

    public function setItemReference(string $_itemReference): static
    {
        $this->itemReference = $_itemReference;
        return $this;
    }

    public function setSerial(string $_serial): static
    {
        $this->serial = $_serial;
        return $this;
    }

    /** 
     * @param _tagSize value of the tag size;
     * @return this Epc class entity with valide tig size; 
     */
    public function setTagSize($_tagSize): ?static
    {
        // //check the tag size value is among the allowed options
        if (!array_key_exists($_tagSize, $this->getTagSizeOptions())) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "Tag size");
        }
        $this->tagSize = $_tagSize;
        return $this;
    }

    /** 
     * @param length set the length attribute of companyPrefixLength;
     * @return this Epc class entity with valide filter value; 
     */
    public function setFilterValue($_filterValue): ?static
    {
        // //check the filter value is among the allowed options
        if (!array_key_exists($_filterValue, $this->getFilterValueOptions())) {
            return $this->setError(EpcMesg::PARAM_OUTOF_RANGE, "Filter value");
        }
        $this->filterValue = $_filterValue;
        return $this;
    }

    /** 
     * @param _schemeParameters set the length attribute of companyPrefixLength;
     * @return this Epc class entity with valide filter value;
     * @throws null;
     */
    public function setSchemeParameters($_schemeParameters): ?static
    {
        if (empty($_schemeParameters)) {
            return $this->setError("At least 1 scheme parameters needed!");
        } else {
            foreach ($_schemeParameters as $k => $parameter) {
                if (empty($parameter)) {
                    return $this->setError("Parameter with index $k can not be empty!");
                }
            }
        }
        $this->schemeParameters = $_schemeParameters;
        return $this;
    }

    public function setEncodeScheme(): static
    {
        $this->encodeScheme = $this->scheme . "-" . $this->tagSize;
        return $this;
    }

    public function setEpcHexaDecimal(string $epcHexaDecimal): ?static
    {
        if (empty($epcHex)) return $this->setError(EpcMesg::PARAM_SHOULD_NOT_EMPTY, "epcHex");
        if (!EpcSpec::isHexChars($epcHex)) {
            return $this->setError(EpcMesg::EPC_HEX_FORMAT_ERROR);
        }
        $this->epcHexaDecimal = $epcHexaDecimal;
        return $this;
    }

    public function getEpcHexaDecimal(): string
    {
        return $this->epcHexaDecimal;
    }

    public function setEpcBinary(string $epcBinString): static
    {
        $this->epcBinary = $epcBinString;
        return $this;
    }

    public function getEpcBinary(): string
    {
        return $this->epcBinary;
    }

    // Encode all URIs if required fields are conformed
    /**
     * @return self|null;
     */
    public function encode(): ?static
    {
        return $this;
    }

    public static function decode(string $epcHexString): ?self
    {
        $instance = new self();
        return $instance->setEpcHexaDecimal($epcHexString);
    }

    /** 
     * @param _companyPrefix set the company prefix value;
     * @return this Epc class entity with valide company prefix value; 
     */
    public function setCompanyPrefix($_companyPrefix): static
    {
        $this->companyPrefix = $_companyPrefix;
        return $this;
    }

    public function getCompanyPrefix(): string
    {
        return $this->companyPrefix;
    }
    // Get Company prefix length options 6~12 digits
    public function getCompanyPrefixLengthOptions(): array
    {
        return $this->companyPrefixLengthOptions;
    }

    // Get Filter values options for current EPC scheme
    public function getFilterValueOptions(): array
    {
        return $this->filterValueOptions;
    }

    // Get Tag size options for web of current EPC scheme
    public function getTagSizeOptions(): array
    {
        return $this->tagSizeOptions;
    }

    public function setEpcURI(string $epcUri): static
    {
        $this->epcURI = $epcUri;
        return $this;
    }
    public function getEpcUri(): string
    {
        return $this->epcURI;
    }

    public function setEpcTagURI(string $epcTagURI): static
    {
        $this->epcTagURI = $epcTagURI;
        return $this;
    }

    public function getEpcTagURI(): string
    {
        return $this->epcTagURI;
    }

    public function setEpcRawURI(string $epcRawURI): static
    {
        $this->epcRawURI = $epcRawURI;
        return $this;
    }

    public function getEpcRawURI(): string
    {
        return $this->epcRawURI;
    }

    public function getOutput(): array
    {
        return [
            "scheme" => ["name" => $this->scheme, "parameters" => $this->schemeParameters],
            "tagSize" => $this->tagSize,
            "filterValue" => $this->filterValue,
            "companyPrefixLength" => $this->companyPrefixLength,
            "companyPrefix" => $this->companyPrefix,
            "epcURI" => $this->epcURI,
            "epcTagURI" => $this->epcTagURI,
            "epcHexaDecimal" => $this->epcHexaDecimal,
            "epcBinary" => $this->epcBinary,
            "epcRawURI" => $this->epcRawURI
        ];
    }


    /** 以下为标准库操作函数区 */

    public function setHeaderStruct(string $headerValue): ?static
    {
        if (empty($headerValue)) return null;
        $this->headerStruct = EpcSpec::getHeaderValues($headerValue);
        return $this;
    }

    public function getHeaderStruct(): array
    {
        return $this->headerStruct;
    }

    /**
     * @param $nodeName Node name;
     * @return Array;
     */
    public function getEpcStandard(string $nodeName): array
    {
        if (empty($nodeName)) return [];
        $nodeName = strtoupper($nodeName);
        $standArr = EpcSpec::getDataStandard($this->scheme . '-' . $this->tagSize);
        if (!key_exists($nodeName, $standArr)) return [];
        return $standArr[$nodeName];
    }
}
