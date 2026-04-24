<?php

/**
 * GDTI EPC 编码解码示例
 * 
 * GDTI (Global Document Type Identifier) 是GS1标准下用于标识文档类型的EPC编码方案
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mickeywaugh\Gs1\Gs1;
use Mickeywaugh\Gs1\Epc\Gdti;

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
$CI = "1234567123456";     // 7位公司前缀 + 6位文档类型
$serial = "ABC123";        // 序列号

$gdtiEpc = Gs1::Gdti(...[
    'tagSize' => $tagSize,
    'filterValue' => $filterValue,
    'schemeParameters' => [
        'CI' => $CI,
        'serial' => $serial
    ]
]);

$result = $gdtiEpc->encode();

if ($result) {
    echo "   ✓ 编码成功!\n";
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
    echo "   ✗ 编码失败: " . $gdtiEpc->getErrorMsg() . "\n";
}

echo "\n";

// 示例2: 113位标签编码（可变长度序列号）
echo "2. 113位 GDTI 编码 (可变长度):\n";
$tagSize = 113;
$serial = "DOC-2024-001";  // 更长的序列号

$gdtiEpc2 = Gs1::Gdti([
    'companyPrefixLength' => $companyPrefixLength,
    'tagSize' => $tagSize,
    'filterValue' => $filterValue,
    'schemeParameters' => [
        'CI' => $CI,
        'serial' => $serial
    ]
]);

$result2 = $gdtiEpc2->encode();

if ($result2) {
    echo "   ✓ 编码成功!\n";
    echo "   标签大小: {$tagSize} bits\n";
    echo "   序列号: {$serial}\n";
    echo "   EPC URI: " . $gdtiEpc2->getEpcUri() . "\n";
    echo "   EPC Tag URI: " . $gdtiEpc2->getEpcTagURI() . "\n";
    echo "   EPC 十六进制: " . $gdtiEpc2->getEpcHexaDecimal() . "\n";
} else {
    echo "   ✗ 编码失败: " . $gdtiEpc2->getErrorMsg() . "\n";
}

echo "\n";

// 示例3: 不同公司前缀长度的编码
echo "3. 不同公司前缀长度的 GDTI 编码:\n";
$testCases = [
    ['prefixLen' => 6, 'CI' => '1234561234567'],
    ['prefixLen' => 8, 'CI' => '1234567812345'],
    ['prefixLen' => 10, 'CI' => '1234567890123'],
    ['prefixLen' => 12, 'CI' => '1234567890123']
];

foreach ($testCases as $case) {
    $gdtiTest = Gs1::Gdti(...[
        'tagSize' => 96,
        'filterValue' => 0,
        'schemeParameters' => [
            'CI' => $case['CI'],
            'serial' => 'DOC001'
        ]
    ])->setCompanyPrefixLength($case['prefixLen']);

    if ($gdtiTest->encode()) {
        echo "   ✓ 公司前缀长度 {$case['prefixLen']}: 公司前缀=" .
            $gdtiTest->getCompanyPrefix() . ", 文档类型=" .
            $gdtiTest->getItemReference() . "\n";
    } else {
        echo "   ✗ 公司前缀长度 {$case['prefixLen']}: " . $gdtiTest->getErrorMsg() . "\n";
    }
}

echo "\n\n";

// ==================== 解码示例 ====================
echo "【解码示例】\n\n";

if ($result) {
    $epcHex = $gdtiEpc->getEpcHexaDecimal();
    echo "1. 从十六进制解码:\n";
    echo "   输入十六进制: {$epcHex}\n";

    $decodedEpc = Gdti::decode($epcHex);

    if ($decodedEpc) {
        echo "   ✓ 解码成功!\n";
        echo "   公司前缀长度: " . $decodedEpc->getCompanyPrefixLength() . "\n";
        echo "   标签大小: " . $decodedEpc->getTagSize() . " bits\n";
        echo "   过滤值: " . $decodedEpc->getFilterValue() . "\n";
        echo "   公司前缀: " . $decodedEpc->getCompanyPrefix() . "\n";
        echo "   文档类型: " . $decodedEpc->getItemReference() . "\n";
        echo "   序列号: " . $decodedEpc->getSerial() . "\n";
        echo "   CI: " . $decodedEpc->getCI() . "\n";
        echo "   EPC URI: " . $decodedEpc->getEpcUri() . "\n";
        echo "   EPC Tag URI: " . $decodedEpc->getEpcTagURI() . "\n";

        // 验证编码和解码的一致性
        echo "\n   【验证】编码与解码一致性检查:\n";
        echo "   原始CI: {$CI}\n";
        echo "   解码CI: " . $decodedEpc->getCI() . "\n";
        echo "   原始序列号: {$serial}\n";
        echo "   解码序列号: " . $decodedEpc->getSerial() . "\n";
        $isConsistent = ($CI === $decodedEpc->getCI() && $serial === $decodedEpc->getSerial());
        echo "   一致性: " . ($isConsistent ? "✓ 通过" : "✗ 失败") . "\n";
    } else {
        echo "   ✗ 解码失败!\n";
    }
}

echo "\n";

// 示例2: 从另一个十六进制字符串解码
echo "2. 从已知十六进制解码:\n";
$testHex = "2C00123456789ABCDEF01234";  // 示例十六进制
echo "   输入十六进制: {$testHex}\n";

$decodedEpc2 = Gdti::decode($testHex);

if ($decodedEpc2) {
    echo "   ✓ 解码成功!\n";
    echo "   公司前缀长度: " . $decodedEpc2->getCompanyPrefixLength() . "\n";
    echo "   标签大小: " . $decodedEpc2->getTagSize() . " bits\n";
    echo "   公司前缀: " . $decodedEpc2->getCompanyPrefix() . "\n";
    echo "   文档类型: " . $decodedEpc2->getItemReference() . "\n";
    echo "   EPC URI: " . $decodedEpc2->getEpcUri() . "\n";
} else {
    echo "   ✗ 解码失败: 无效的EPC格式\n";
}

echo "\n";

// 示例3: 获取参数选项
echo "3. GDTI 参数选项:\n";
$gdtiInfo = new Gdti();

echo "   支持的公司前缀长度: " . implode(", ", $gdtiInfo->getCompanyPrefixLengthOptions()) . "\n";
echo "   支持的标签大小:\n";
foreach ($gdtiInfo->getTagSizeOptions() as $size => $desc) {
    echo "      - {$size}: {$desc}\n";
}
echo "   支持的过滤值:\n";
foreach ($gdtiInfo->getFilterValueOptions() as $value => $desc) {
    echo "      - {$value}: {$desc}\n";
}

echo "\n";

// 示例4: 错误处理演示
echo "4. 错误处理演示:\n";

// 测试错误的CI格式
$badGdti = Gs1::Gdti(...[
    'tagSize' => 96,
    'filterValue' => 0,
    'schemeParameters' => [
        'CI' => 'INVALID',  // 无效的CI（应该是13位数字）
        'serial' => 'DOC001'
    ]
])->setCompanyPrefixLength(7);

if (!$badGdti->encode()) {
    echo "   ✓ 正确捕获错误: " . $badGdti->getErrorMsg() . "\n";
}

// 测试缺失参数
$badGdti2 = Gs1::Gdti(...[
    'tagSize' => 96,
    'filterValue' => 0,
    'schemeParameters' => [
        'CI' => '1234567123456'  // 缺少 serial 参数
    ]
])->setCompanyPrefixLength(7);

if (!$badGdti2->encode()) {
    echo "   ✓ 正确捕获错误: " . $badGdti2->getErrorMsg() . "\n";
}

echo "\n";

// 示例5: 获取完整输出
echo "5. 获取完整输出数据:\n";
if ($result) {
    $output = $gdtiEpc->getOutput();
    echo "   输出数据结构:\n";
    echo "   - scheme: " . $output['scheme']['name'] . "\n";
    echo "   - tagSize: " . $output['tagSize'] . "\n";
    echo "   - filterValue: " . $output['filterValue'] . "\n";
    echo "   - companyPrefix: " . $output['companyPrefix'] . "\n";
    echo "   - itemReference (Document Type): " . $output['itemReference'] . "\n";
    echo "   - serial: " . $output['serial'] . "\n";
    echo "   - epcURI: " . $output['epcURI'] . "\n";
    echo "   - epcHexaDecimal: " . $output['epcHexaDecimal'] . "\n";
    echo "   - hasError: " . ($output['error']['code'] ? 'Yes' : 'No') . "\n";
}

echo "\n========================================\n";
echo "示例结束\n";
echo "========================================\n";
