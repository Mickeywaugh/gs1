<?php

namespace Mickeywaugh\Gs1\Epc;

/**
 * Description:
 * EPC base class for all supported EPC schemes 
 * According to specification Gs1 EPC Tag Data Standard Release 1.13 
 * Author: Mickeywaugh <Mickeywaugh at qq dot com>
 */

interface EpcInterface
{

    // return all the input and output data for EPC Tag data translation;
    // encode EPC Tag binary(Hex) with given parameters;
    public function encode(): ?static;

    // decoding EPC Tag Hex code to Pure ID URI& Tag raw URI;
    public static function decode(string $epcHexString): ?self;

    // Encoding EPC Tag URI
    public function setTagURI(string $epcURI): ?static;

    // Encoding EPC Raw URI
    public function setRawURI(string $epcRawURI): ?static;

    // Encoding EPC Pure Identify URI
    public function setURI(string $epcURI): ?static;

    // Encoding Hex Code for Tag memory bank(Where the binary code saved);
    public function setEpcHexaDecimal(string $epcHexString): ?static;

    public function setEpcBinary(string $epcBinString): static;
    public function getEpcBinary(): string;
    // Get Company prefix length
    public function setCompanyPrefixLength(int $length): ?static;
    public function getCompanyPrefixLength(): int;

    // set company prefix
    public function setCompanyPrefix(string $companyPrefix): ?static;
    //set indicator or item reference
    public function setItemReference(string $itemRefer): ?static;

    public function setSerial(string $serial): ?static;

    // Get tag size(Epc binary coding scheme) options for current EPC scheme
    public function setTagSize(int $tagSize): ?static;

    // Get Filter value options for current EPC scheme
    public function setFilterValue(int $value): ?static;

    public function setEncodeScheme(): ?static;

    // Get Required fields definition for EPC schema of all
    public function getSchemeParameterFields(): array;

    // Get Company prefix length options
    public function getCompanyPrefixLengthOptions(): array;

    // Get tag size(Epc binary coding scheme) options for current EPC scheme
    public function getTagSizeOptions(): array;

    // Get Filter values options for current EPC scheme
    public function getFilterValueOptions(): array;

    // Get Company prefix string by $companyPrefixLength
    public function getCompanyPrefix(): string;

    // get EPC tag data standard array from json;
    public function getEpcStandard(string $nodeName): array;

    // get EPC tag header value from epc hexadecimal string;
    public function getHeaderStruct(): array;

    public function setHeaderStruct(string $header): ?static;

    public function getCheckDigit(string $string): string;
}
