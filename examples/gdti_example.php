<?php

/**
 * GDTI EPC 编码解码示例
 * 
 * GDTI (Global Document Type Identifier) 是GS1标准下用于标识文档类型的EPC编码方案
 */

require_once __DIR__ . '/vendor/autoload.php';

use Mickeywaugh\Gs1\Gs1;

echo "========================================\n";
echo "GDTI EPC 编码解码示例\n";
echo "========================================\n\n";

// ==================== 编码示例 ====================
echo "【编码示例】\n\n";

// 示例1: 96位标签编码
echo "1. 96位 GDTI 编码:\n";
$companyPrefixLength = 7;  // 公司前缀长度 (6-12)
$tagSize = 96;             // 标签大小 (96 或 113)
$filterValue = 0;          // 过滤值 (0-7)

// CI (Company Identifier) = 公司前缀 + 文档类型，总共13位
$CI = "1234567" . "123456";  // 7位公司前缀 + 6位文档类型
$serial = "ABC123";          // 序列号

$schemeParameters = [
    'CI' => $CI,
    'serial' => $serial
];

$gdtiEpc = Gs1::Gdti(
    $companyPrefixLength,
    $tagSize,
    $filterValue,
    $schemeParameters
);

$result = $gdtiEpc->encode();

if ($result) {
    echo "   公司前缀长度: {$companyPrefixLength}\n";
    echo "   标签大小: {$tagSize} bits\n";
    echo "   过滤值: {$filterValue}\n";
    echo "   CI (公司前缀+文档类型): {$CI}\n";
    echo "   序列号: {$serial}\n";
    echo "   公司前缀: " . $gdtiEpc->getCompanyPrefix() . "\n";
    echo "   文档类型: " . $gdtiEpc->getItemReference() . "\n";
    echo "   EPC URI: " . $gdtiEpc->getEpcUri() . "\n";
    echo "   EPC Tag URI: " . $gdtiEpc->getEpcTagURI() . "\n";
    echo "   EPC Raw URI: " . $gdtiEpc->getEpcRawURI() . "\n";
    echo "   EPC 二进制: " . $gdtiEpc->getEpcBinary() . "\n";
    echo "   EPC 十六进制: " . $gdtiEpc->getEpcHexaDecimal() . "\n";
} else {
    echo "   编码失败: " . $gdtiEpc->getErrorMsg() . "\n";
}

echo "\n";

// 示例2: 113位标签编码（可变长度序列号）
echo "2. 113位 GDTI 编码 (可变长度):\n";
$tagSize = 113;
$serial = "DOC-2024-001";  // 更长的序列号

$schemeParameters = [
    'CI' => $CI,
    'serial' => $serial
];

$gdtiEpc2 = Gs1::Gdti(
    $companyPrefixLength,
    $tagSize,
    $filterValue,
    $schemeParameters
);

$result2 = $gdtiEpc2->encode();

if ($result2) {
    echo "   标签大小: {$tagSize} bits\n";
    echo "   序列号: {$serial}\n";
    echo "   EPC URI: " . $gdtiEpc2->getEpcUri() . "\n";
    echo "   EPC Tag URI: " . $gdtiEpc2->getEpcTagURI() . "\n";
    echo "   EPC 十六进制: " . $gdtiEpc2->getEpcHexaDecimal() . "\n";
} else {
    echo "   编码失败: " . $gdtiEpc2->getErrorMsg() . "\n";
}

echo "\n\n";

// ==================== 解码示例 ====================
echo "【解码示例】\n\n";

if ($result) {
    $epcHex = $gdtiEpc->getEpcHexaDecimal();
    echo "1. 从十六进制解码:\n";
    echo "   输入十六进制: {$epcHex}\n";

    $decodedEpc = \Mickeywaugh\Gs1\Epc\Gdti::decode($epcHex);

    if ($decodedEpc) {
        echo "   解码成功!\n";
        echo "   公司前缀长度: " . $decodedEpc->getCompanyPrefixLength() . "\n";
        echo "   标签大小: " . $decodedEpc->getTagSize() . " bits\n";
        echo "   过滤值: " . $decodedEpc->getFilterValue() . "\n";
        echo "   公司前缀: " . $decodedEpc->getCompanyPrefix() . "\n";
        echo "   文档类型: " . $decodedEpc->getItemReference() . "\n";
        echo "   序列号: " . $decodedEpc->getSerial() . "\n";
        echo "   EPC URI: " . $decodedEpc->getEpcUri() . "\n";
        echo "   EPC Tag URI: " . $decodedEpc->getEpcTagURI() . "\n";
    } else {
        echo "   解码失败!\n";
    }
}

echo "\n";

// 示例2: 从另一个十六进制字符串解码
echo "2. 从已知十六进制解码:\n";
$testHex = "3074257BF7194E7340000000";  // 示例十六进制
echo "   输入十六进制: {$testHex}\n";

$decodedEpc2 = \Mickeywaugh\Gs1\Epc\Gdti::decode($testHex);

if ($decodedEpc2) {
    echo "   解码成功!\n";
    echo "   公司前缀长度: " . $decodedEpc2->getCompanyPrefixLength() . "\n";
    echo "   标签大小: " . $decodedEpc2->getTagSize() . " bits\n";
    echo "   EPC URI: " . $decodedEpc2->getEpcUri() . "\n";
} else {
    echo "   解码失败: " . (\Mickeywaugh\Gs1\Epc\Gdti::decode($testHex) ? '' : '无效的EPC格式') . "\n";
}

echo "\n========================================\n";
echo "示例结束\n";
echo "========================================\n";
