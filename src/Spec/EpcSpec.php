<?php

namespace Mickeywaugh\Gs1\Spec;

/**
 * Description:
 * 获取指定规则的EPC编码属性相关的约束
 * class for get the range of specified scheme EPC coding options 
 *
 * According to specification Gs1 EPC Tag Data Standard release 1.13.
 * 
 * Author: Mickey Wu <Mickey dot Wu at boingtech dot com>
 * Copyright (c) 2024- Boing Technologies Ltd.  All rights reserved.
 */


class EpcSpec
{
    public static string $nonZeroDigit = "123456789";
    public static string $digit = "0123456789";
    public static string $upperAlpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    public static string $lowerAlpha = "abcdefghijklmnopqrstuvwxyz";
    public static string $otherAlpha = "!'()*+,-.:;=_";
    public static string $hexChar = "0123456789ABCDEFabcdef";
    public static string $upperHexChar = "0123456789ABCDEF";
    public static string $escape = "%FF";
    public static string $gs3A3Char;
    public static string $cpRefChar;

    // 将正则表达式存储为静态属性以避免每次调用时重新编译
    public static string $regUpperAlpha = '/^[A-Z]*$/';
    public static string $regLowerAlpha = '/^[a-z]*$/';
    public static string $regOtherAlpha = "/^[!'\(\)\*\+,-\.:=_\x5C]*$/";
    public static string $regHexChar = '/^[0-9A-Fa-f]*$/';
    public static string $regUpperHexChar = '/^[0-9A-F]*$/';
    public static string $regEscape = '/^%[0-9A-Fa-f]{2}*$/';
    public static string $regZeroComponent = "0";
    public static string $regNumericComponent = '/^[0-9]*$/';
    public static string $regNonZeroComponent = '/^[1-9]*$/';
    public static string $regPaddedNumericComponent = "/^[d+]*$/";
    public static string $regPaddedNumericComponentOrEmpty = "/^[d*]*$/";
    public static string $regHexComponent = "/^[0-9A-F]*$/";
    public static string $regHexComponentOrEmpty = "/^[0-9A-F*]*$/";
    public static string $regGS3A3Component = '/^[0-9A-Za-z!%-?]*$/';
    public static string $regCPRefComponent = '/^[0-9A-Za-z!%-?\%2F\%23]*$/';


    public static array $mapHexChar = [
        '21' => '!',
        '22' => '"',
        '25' => '%',
        '26' => '&',
        '27' => '\'',
        '28' => '(',
        '29' => ')',
        '2A' => '*',
        '2B' => '+',
        '2C' => ',',
        '2D' => '-',
        '2E' => '.',
        '2F' => '/',
        '30' => '0',
        '31' => '1',
        '32' => '2',
        '33' => '3',
        '34' => '4',
        '35' => '5',
        '36' => '6',
        '37' => '7',
        '38' => '8',
        '39' => '9',
        '3A' => ':',
        '3B' => ';',
        '3C' => '<',
        '3D' => '=',
        '3E' => '>',
        '3F' => '?',
        '41' => 'A',
        '42' => 'B',
        '43' => 'C',
        '44' => 'D',
        '45' => 'E',
        '46' => 'F',
        '47' => 'G',
        '48' => 'H',
        '49' => 'I',
        '4A' => 'J',
        '4B' => 'K',
        '4C' => 'L',
        '4D' => 'M',
        '4E' => 'N',
        '4F' => 'O',
        '50' => 'P',
        '51' => 'Q',
        '52' => 'R',
        '53' => 'S',
        '54' => 'T',
        '55' => 'U',
        '56' => 'V',
        '57' => 'W',
        '58' => 'X',
        '59' => 'Y',
        '5A' => 'Z',
        '5F' => '_',
        '61' => 'a',
        '62' => 'b',
        '63' => 'c',
        '64' => 'd',
        '65' => 'e',
        '66' => 'f',
        '67' => 'g',
        '68' => 'h',
        '69' => 'i',
        '6A' => 'j',
        '6B' => 'k',
        '6C' => 'l',
        '6D' => 'm',
        '6E' => 'n',
        '6F' => 'o',
        '70' => 'p',
        '71' => 'q',
        '72' => 'r',
        '73' => 's',
        '74' => 't',
        '75' => 'u',
        '76' => 'v',
        '77' => 'w',
        '78' => 'x',
        '79' => 'y',
        '7A' => 'z',
    ];

    public static array $LEGACYAI = [
        'sgtin' => '01',
        'sscc' => '00',
        'sgln' => '414',
        'grai' => '8003',
        'giai' => '8004',
        'gsrn' => '8018',
        'gsrnp' => '8017',
        'gdti' => '253',
        'cpi' => '8010',
        'sgcn' => '255',
        'itip' => '8006',
    ];

    public function __construct()
    {
        self::$gs3A3Char = self::$digit . self::$upperAlpha . self::$lowerAlpha . self::$otherAlpha;
        self::$cpRefChar = self::$digit . self::$upperAlpha .  "-" . "%2F" . "%23";
    }

    // 通用辅助方法，用于简化布尔返回的正则匹配
    private static function matchRegex(string $string, string $regex): bool
    {
        if ($string === '') return false;
        return preg_match($regex, $string) > 0;
    }

    public static function isPaddedNumericComponent(string $string, bool $allowEmpty = false): bool
    {
        $regex = $allowEmpty ? self::$regPaddedNumericComponentOrEmpty : self::$regPaddedNumericComponent;
        return self::matchRegex($string, $regex);
    }

    public static function isNumericComponent(string $string, bool $nonZero = false): bool
    {
        $regex = $nonZero ? self::$regNonZeroComponent : self::$regNumericComponent;
        return self::matchRegex($string, $regex);
    }


    public static function isHexChars(string $string): bool
    {
        return self::matchRegex($string, self::$regHexChar);
    }

    public static function isHexComponent(string $string, bool $allowEmpty = false): bool
    {
        $regex = $allowEmpty ? self::$regHexComponentOrEmpty : self::$regHexComponent;
        return self::matchRegex($string, $regex);
    }

    public static function isGS3A3Component(string $string): bool
    {
        return self::matchRegex($string, self::$regGS3A3Component);
    }

    public static function isCPRefComponent(string $string): bool
    {
        return self::matchRegex($string, self::$regCPRefComponent);
    }


    /*
     * @param binary The binary number to be transfer;
     * @return string The ascii character string;
     */
    public static function bin2Char(string $binString): string
    {
        $retChars = "";
        $len = strlen($binString);
        // split the binary string into 7-bit binary strings
        for ($i = 0; $i < $len; $i += 7) {
            $strBin = mb_substr($binString, $i, 7);
            $retChars .= chr(bindec($strBin));
        }
        return $retChars;
    }

    // encode char string to binary string
    public static function char2Bin(string $chars): string
    {
        $binString = "";
        $i = 0;
        while ($i <= strlen($chars)) {
            // get single character
            $char = substr($chars, $i, 1);
            // get character hexadecimal value
            $hex = dechex(ord($char));
            $i += 1;
            // convert hexadecimal to binary, string padding 7-bit with 0
            $binString .= str_pad(base_convert($hex, 16, 2), 7, 0, 0);
        }
        return $binString;
    }

    public static function char2hex(string $char): string
    {
        if ($char === '') return $char;
        return strtoupper(dechex(ord($char)));
    }

    public static function hex2char(string $hex): string
    {
        if (isset(self::$mapHexChar[$hex])) {
            return self::$mapHexChar[$hex];
        } else {
            return false;
        }
    }

    //string
    public static function hex2symbol(): array
    {

        $hexArr = [
            '21' => '!',
            '22' => '"',
            '25' => '%',
            '26' => '&',
            '27' => '\'',
            '28' => '(',
            '29' => ')',
            '2A' => '*',
            '2B' => '+',
            '2C' => ',',
            '2D' => '-',
            '2E' => '.',
            '2F' => '/',
            '30' => '0',
            '31' => '1',
            '32' => '2',
            '33' => '3',
            '34' => '4',
            '35' => '5',
            '36' => '6',
            '37' => '7',
            '38' => '8',
            '39' => '9',
            '3A' => ':',
            '3B' => ';',
            '3C' => '<',
            '3D' => '=',
            '3E' => '>',
            '3F' => '?',
            '41' => 'A',
            '42' => 'B',
            '43' => 'C',
            '44' => 'D',
            '45' => 'E',
            '46' => 'F',
            '47' => 'G',
            '48' => 'H',
            '49' => 'I',
            '4A' => 'J',
            '4B' => 'K',
            '4C' => 'L',
            '4D' => 'M',
            '4E' => 'N',
            '4F' => 'O',
            '50' => 'P',
            '51' => 'Q',
            '52' => 'R',
            '53' => 'S',
            '54' => 'T',
            '55' => 'U',
            '56' => 'V',
            '57' => 'W',
            '58' => 'X',
            '59' => 'Y',
            '5A' => 'Z',
            '5F' => '_',
            '61' => 'a',
            '62' => 'b',
            '63' => 'c',
            '64' => 'd',
            '65' => 'e',
            '66' => 'f',
            '67' => 'g',
            '68' => 'h',
            '69' => 'i',
            '6A' => 'j',
            '6B' => 'k',
            '6C' => 'l',
            '6D' => 'm',
            '6E' => 'n',
            '6F' => 'o',
            '70' => 'p',
            '71' => 'q',
            '72' => 'r',
            '73' => 's',
            '74' => 't',
            '75' => 'u',
            '76' => 'v',
            '77' => 'w',
            '78' => 'x',
            '79' => 'y',
            '7A' => 'z',
        ];
        return $hexArr;
    }

    /**
     * 编码 transfer character string to binary string
     * @param string $string The source string to be transfer;
     * @return string The binary string;
     */
    public static function encodingString(string $string): string
    {
        $stringBin = '';
        $i = 0;
        while ($i <= strlen($string)) {
            $str = substr($string, $i, 1);
            $hex = self::char2hex($str);
            $i += 1;
            // EpcUtil::Log("$str=>$hex");
            $stringBin .= str_pad(base_convert($hex, 16, 2), 7, 0, 0);
        }
        // EpcUtil::Log("$string =>out put string binary:" . $stringBin);
        return $stringBin;
    }

    /**
     *解码, transfer binary string to character string
     * @param string $binString The binary string to be transfer;
     * @return string The character string;
     */
    public static function decodingString(string $binString): string
    {
        $retString = '';
        $len = strlen($binString);
        for ($i = 0; $i < $len; $i += 7) {
            $bin = mb_substr($binString, $i, 7);
            $hex = base_convert($bin, 2, 16);
            // EpcUtil::Log("$bin=>" . self::hex2char($hex));
            $retString .= self::hex2char($hex);
        }
        return $retString;
    }


    /**
     * @param string $number The source number to be transfer;
     * @param int $fromBase Source number system;
     * @param int $toBase Target number system;
     */
    public static function numberBaseConvert(string $number, int $fromBase, int $toBase): string
    {
        $number = strtolower($number);
        $digits = '0123456789abcdefghijklmnopqrstuvwxyz';
        $length = strlen($number);
        $result = '';

        $nibbles = array();
        for ($i = 0; $i < $length; ++$i) {
            $nibbles[$i] = strpos($digits, $number[$i]);
        }
        do {
            $value = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; ++$i) {
                $value = $value * $fromBase + $nibbles[$i];
                if ($value >= $toBase) {
                    $nibbles[$newlen++] = (int)($value / $toBase);
                    $value %= $toBase;
                } else if ($newlen > 0) {
                    $nibbles[$newlen++] = 0;
                }
            }
            $length = $newlen;
            $result = $digits[$value] . $result;
        } while ($newlen != 0);
        return strtoupper($result);
    }

    /**
     * @param string $string The string to be transfer;
     * @return string The ascii character string;
     */
    public static function uri2Char(string $string): string
    {
        return urldecode($string);
    }

    public static function dod2Bin(string $dodString): string
    {
        $binString = decbin(ord($dodString));
        return substr($binString, -6);
    }

    public static function stringElement2Uri(string $string): string
    {
        $i = 0;
        $uri = '';
        while ($i < strlen($string)) {
            $str = substr($string, $i, 1);
            $uri .= self::uri2symbol($str);
            $i += 1;
        }
        return $uri;
    }

    public static function getLEGACYAI(): array
    {
        return self::$LEGACYAI;
    }

    //6-bit
    public static function uri2symbol(string $string): string
    {
        $regStr = "/^[0-9a-fA-F-]$/";
        $regHex = "/^[\#\"\%\&\/\<\>\?]$/";
        if (preg_match($regStr, $string)) {
            return $string;
        }
        if (preg_match($regHex, $string)) {
            return "%" . dechex(ord($string));
        }
        return "";
    }

    /**
     * @param string $epcScheme The EPC scheme full name;
     */
    public static function getDataStandard(string $epcScheme): array
    {
        $jsonFile = sprintf(__DIR__ . '/resData/%s.json', strtolower($epcScheme));

        if (!is_file($jsonFile)) {
            return [];
        }
        $jsonString = file_get_contents($jsonFile);
        $data = json_decode($jsonString, true);
        return $data;
    }


    /**
     * @param string $header epc header value in Hexadecimal string;
     * @return array
     */
    public static function getHeaderValues(string $header): ?array
    {
        $jsonFile = __DIR__ . "/resData/header-values.json";

        if (!is_file($jsonFile)) {
            return [];
        }

        $jsonString = file_get_contents($jsonFile);
        $headerValues = json_decode($jsonString, true);
        if (!array_key_exists($header, $headerValues)) {
            return [];
        }
        return $headerValues[$header];
    }

    /**
     * @param string $gtinUpc The GTIN/UPC value;
     * @return int The company prefix length;
     */
    public static function getCompanyPrefixLength(string $gtinUpc): int
    {
        // 使用静态缓存避免重复加载XML文件
        static $xmlCache = null;
        static $prefixMap = null;

        if ($prefixMap === null) {
            $GCPFile = __DIR__ . '/resData/gcpprefixformatlist.xml';

            if (!is_file($GCPFile)) {
                return 0;
            }

            // 首次加载时解析XML并构建映射表
            if ($xmlCache === null) {
                $xmlCache = simplexml_load_file($GCPFile, "SimpleXMLElement", LIBXML_NOCDATA);
            }

            // 构建前缀到长度的映射表以提高查询性能
            $prefixMap = [];
            foreach ($xmlCache->xpath("//node()") as $node) {
                $prefix = (string)$node->attributes()->prefix;
                $gcpLength = (int)$node->attributes()->gcpLength;
                if (!empty($prefix)) {
                    $prefixMap[$prefix] = $gcpLength;
                }
            }
        }

        // 从映射表中查找公司前缀长度
        return $prefixMap[$gtinUpc] ?? 0;
    }
}
