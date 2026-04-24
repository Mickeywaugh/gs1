<?php

/**
 * SGTIN EPC 编码解码示例
 * 
 * SGTIN (Serialized Global Trade Item Number) 是GS1标准下用于标识贸易项目的EPC编码方案
 */

require_once __DIR__ . '/vendor/autoload.php';

use Mickeywaugh\Gs1\Gs1;

echo "========================================\n";
echo "SGTIN EPC 编码解码示例\n";
echo "========================================\n\n";

// ==================== 编码示例 ====================
echo "【编码示例】\n\n";

// 示例1: 96位标签编码
echo "1. 96位 SGTIN 编码:\n";
$companyPrefixLength = 7;  // 公司前缀长度 (6-12)
$tagSize = 96;             // 标签大小 (96 或 198)
$filterValue = 1;          // 过滤值 (0-7)，1表示POS零售商品

// CI (Company Identifier) = GTIN，共14位（包含校验位）
// 结构：指示符(1位) + 公司前缀 + 项目参考 + 校验位(1位)
$CI = "01234567890128";    // 14位GTIN
$serial = "ABC123";        // 序列号

$schemeParameters = [
    'CI' => $CI,
    'serial' => $serial
];

$sgtinEpc = Gs1::Sgtin(
    $companyPrefixLength,
    $tagSize,
    $filterValue,
    $schemeParameters
);

$result = $sgtinEpc->encode();

if ($result) {
    echo "   公司前缀长度: {$companyPrefixLength}\n";
    echo "   标签大小: {$tagSize} bits\n";
    echo "   过滤值: {$filterValue} (Point of Sale Trade Item)\n";
    echo "   GTIN (CI): {$CI}\n";
    echo "   序列号: {$serial}\n";
    echo "   公司前缀: " . $sgtinEpc->getCompanyPrefix() . "\n";
    echo "   项目参考: " . $sgtinEpc->getItemReference() . "\n";
    echo "   EPC URI: " . $sgtinEpc->getEpcUri() . "\n";
    echo "   EPC Tag URI: " . $sgtinEpc->getEpcTagURI() . "\n";
    echo "   EPC Raw URI: " . $sgtinEpc->getEpcRawURI() . "\n";
    echo "   EPC 二进制: " . $sgtinEpc->getEpcBinary() . "\n";
    echo "   EPC 十六进制: " . $sgtinEpc->getEpcHexaDecimal() . "\n";
} else {
    echo "   编码失败: " . $sgtinEpc->getErrorMsg() . "\n";
}

echo "\n";

// 示例2: 198位标签编码（可变长度序列号）
echo "2. 198位 SGTIN 编码 (可变长度):\n";
$tagSize = 198;
$filterValue = 6;  // 6表示单元负载
$serial = "SERIAL-2024-001-XYZ";  // 更长的序列号

$schemeParameters = [
    'CI' => $CI,
    'serial' => $serial
];

$sgtinEpc2 = Gs1::Sgtin(
    $companyPrefixLength,
    $tagSize,
    $filterValue,
    $schemeParameters
);

$result2 = $sgtinEpc2->encode();

if ($result2) {
    echo "   标签大小: {$tagSize} bits\n";
    echo "   过滤值: {$filterValue} (Unit Load)\n";
    echo "   序列号: {$serial}\n";
    echo "   EPC URI: " . $sgtinEpc2->getEpcUri() . "\n";
    echo "   EPC Tag URI: " . $sgtinEpc2->getEpcTagURI() . "\n";
    echo "   EPC 十六进制: " . $sgtinEpc2->getEpcHexaDecimal() . "\n";
} else {
    echo "   编码失败: " . $sgtinEpc2->getErrorMsg() . "\n";
}

echo "\n";

// 示例3: 不同公司前缀长度的编码
echo "3. 不同公司前缀长度的 SGTIN 编码:\n";
$testCases = [
    ['prefixLen' => 6, 'CI' => '01234567890128'],
    ['prefixLen' => 8, 'CI' => '01234567890128'],
    ['prefixLen' => 10, 'CI' => '01234567890128'],
    ['prefixLen' => 12, 'CI' => '01234567890128']
];

foreach ($testCases as $case) {
    $sgtinTest = Gs1::Sgtin([
        $case['prefixLen'],
        96,
        0,
        ['CI' => $case['CI'], 'serial' => '1']
    ]);

    if ($sgtinTest->encode()) {
        echo "   公司前缀长度 {$case['prefixLen']}: 公司前缀=" .
            $sgtinTest->getCompanyPrefix() . ", 项目参考=" .
            $sgtinTest->getItemReference() . "\n";
    }
}

echo "\n\n";

// ==================== 解码示例 ====================
echo "【解码示例】\n\n";

if ($result) {
    $epcHex = $sgtinEpc->getEpcHexaDecimal();
    echo "1. 从十六进制解码:\n";
    echo "   输入十六进制: {$epcHex}\n";

    $decodedEpc = \Mickeywaugh\Gs1\Epc\Sgtin::decode($epcHex);

    if ($decodedEpc) {
        echo "   ✓ 解码成功!\n";
        echo "   公司前缀长度: " . $decodedEpc->getCompanyPrefixLength() . "\n";
        echo "   标签大小: " . $decodedEpc->getTagSize() . " bits\n";
        echo "   过滤值: " . $decodedEpc->getFilterValue() . "\n";
        echo "   公司前缀: " . $decodedEpc->getCompanyPrefix() . "\n";
        echo "   项目参考: " . $decodedEpc->getItemReference() . "\n";
        echo "   序列号: " . $decodedEpc->getSerial() . "\n";
        echo "   GTIN (CI): " . $decodedEpc->getCI() . "\n";
        echo "   EPC URI: " . $decodedEpc->getEpcUri() . "\n";
        echo "   EPC Tag URI: " . $decodedEpc->getEpcTagURI() . "\n";

        // 验证编码和解码的一致性
        echo "\n   【验证】编码与解码一致性检查:\n";
        echo "   原始GTIN: {$CI}\n";
        echo "   解码GTIN: " . $decodedEpc->getCI() . "\n";
        echo "   原始序列号: {$serial}\n";
        echo "   解码序列号: " . $decodedEpc->getSerial() . "\n";
        echo "   一致性: " . (($CI === $decodedEpc->getCI() && $serial === $decodedEpc->getSerial()) ? "✓ 通过" : "✗ 失败") . "\n";
    } else {
        echo "   ✗ 解码失败!\n";
    }
}

echo "\n";

// 示例2: 从另一个十六进制字符串解码
echo "2. 从已知十六进制解码:\n";
$testHex = "3074257BF7194E7340000000";  // 示例十六进制
echo "   输入十六进制: {$testHex}\n";

$decodedEpc2 = \Mickeywaugh\Gs1\Epc\Sgtin::decode($testHex);

if ($decodedEpc2) {
    echo "   ✓ 解码成功!\n";
    echo "   公司前缀长度: " . $decodedEpc2->getCompanyPrefixLength() . "\n";
    echo "   标签大小: " . $decodedEpc2->getTagSize() . " bits\n";
    echo "   GTIN: " . $decodedEpc2->getCI() . "\n";
    echo "   EPC URI: " . $decodedEpc2->getEpcUri() . "\n";
} else {
    echo "   ✗ 解码失败: 无效的EPC格式\n";
}

echo "\n";

// 示例3: 校验位计算演示
echo "3. GTIN 校验位计算演示:\n";
$testGTINs = [
    "0123456789012",  // 缺少校验位的13位
    "0614141999996",  // 完整的14位GTIN
    "1234567890123"   // 另一个测试用例
];

foreach ($testGTINs as $gtin) {
    if (strlen($gtin) == 13) {
        // 计算校验位
        $sgtinTemp = new \Mickeywaugh\Gs1\Epc\Sgtin();
        $checkDigit = $sgtinTemp->getCheckDigit($gtin . "0");  // 临时添加0占位
        echo "   GTIN-13: {$gtin} -> 校验位: {$checkDigit} -> 完整GTIN: {$gtin}{$checkDigit}\n";
    } else {
        echo "   GTIN-14: {$gtin} (已包含校验位)\n";
    }
}

echo "\n";

// 示例4: 获取参数选项
echo "4. SGTIN 参数选项:\n";
$sgtinInfo = new \Mickeywaugh\Gs1\Epc\Sgtin();

echo "   支持的公司前缀长度: " . implode(", ", $sgtinInfo->getCompanyPrefixLengthOptions()) . "\n";
echo "   支持的标签大小:\n";
foreach ($sgtinInfo->getTagSizeOptions() as $size => $desc) {
    echo "      - {$size}: {$desc}\n";
}
echo "   支持的过滤值:\n";
foreach ($sgtinInfo->getFilterValueOptions() as $value => $desc) {
    echo "      - {$value}: {$desc}\n";
}

echo "\n";

// 示例5: 错误处理演示
echo "5. 错误处理演示:\n";

// 测试错误的公司前缀长度
$badSgtin = Gs1::Sgtin([
    15,  // 无效的公司前缀长度（应该是6-12）
    96,
    0,
    ['CI' => '01234567890128', 'serial' => '1']
]);

if (!$badSgtin->encode()) {
    echo "   ✓ 正确捕获错误: " . $badSgtin->getErrorMsg() . "\n";
}

// 测试缺失参数
$badSgtin2 = Gs1::Sgtin([
    7,
    96,
    0,
    ['CI' => '01234567890128']  // 缺少 serial 参数
]);

if (!$badSgtin2->encode()) {
    echo "   ✓ 正确捕获错误: " . $badSgtin2->getErrorMsg() . "\n";
}

echo "\n========================================\n";
echo "示例结束\n";
echo "========================================\n";
