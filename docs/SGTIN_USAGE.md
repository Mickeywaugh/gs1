# SGTIN EPC 编码解码使用说明

## 概述

SGTIN (Serialized Global Trade Item Number) 是 GS1 标准下用于唯一标识贸易项目的 EPC 编码方案。它在 GTIN (Global Trade Item Number) 的基础上增加了序列号，使得每个单独的贸易项目都可以被唯一标识。本实现遵循 GS1 EPC Tag Data Standard Release 1.13 规范。

## 主要特性

- 支持 96 位和 198 位标签编码
- 支持公司前缀长度 6-12 位
- 支持过滤值 0-7（具有特定含义）
- 完整的编码和解码功能
- 自动计算 GTIN 校验位
- 符合 GS1 标准规范

## 基本用法

### 编码示例

```php
<?php
use Mickeywaugh\Gs1\Gs1;

// 创建 SGTIN 实例
$companyPrefixLength = 7;  // 公司前缀长度 (6-12)
$tagSize = 96;             // 标签大小 (96 或 198)
$filterValue = 1;          // 过滤值 (0-7)

// CI (Company Identifier) = GTIN，共14位（包含校验位）
// 结构：指示符(1位) + 公司前缀 + 项目参考 + 校验位(1位)
$CI = "01234567890128";    // 14位GTIN
$serial = "ABC123";        // 序列号

$schemeParameters = [
    'CI' => $CI,
    'serial' => $serial
];

$sgtinEpc = Gs1::Sgtin([
    $companyPrefixLength,
    $tagSize,
    $filterValue,
    $schemeParameters
]);

// 执行编码
$result = $sgtinEpc->encode();

if ($result) {
    // 获取编码结果
    echo "EPC URI: " . $sgtinEpc->getEpcUri() . "\n";
    echo "EPC Tag URI: " . $sgtinEpc->getEpcTagURI() . "\n";
    echo "EPC Raw URI: " . $sgtinEpc->getEpcRawURI() . "\n";
    echo "EPC 二进制: " . $sgtinEpc->getEpcBinary() . "\n";
    echo "EPC 十六进制: " . $sgtinEpc->getEpcHexaDecimal() . "\n";
} else {
    echo "编码失败: " . $sgtinEpc->getErrorMsg() . "\n";
}
```

### 解码示例

```php
<?php
use Mickeywaugh\Gs1\Epc\Sgtin;

// 从十六进制字符串解码
$epcHex = "3074257BF7194E7340000000";
$decodedEpc = Sgtin::decode($epcHex);

if ($decodedEpc) {
    echo "公司前缀长度: " . $decodedEpc->getCompanyPrefixLength() . "\n";
    echo "标签大小: " . $decodedEpc->getTagSize() . " bits\n";
    echo "过滤值: " . $decodedEpc->getFilterValue() . "\n";
    echo "公司前缀: " . $decodedEpc->getCompanyPrefix() . "\n";
    echo "项目参考: " . $decodedEpc->getItemReference() . "\n";
    echo "序列号: " . $decodedEpc->getSerial() . "\n";
    echo "GTIN (CI): " . $decodedEpc->getCI() . "\n";
    echo "EPC URI: " . $decodedEpc->getEpcUri() . "\n";
    echo "EPC Tag URI: " . $decodedEpc->getEpcTagURI() . "\n";
} else {
    echo "解码失败!\n";
}
```

## SGTIN 结构说明

### 字段组成

SGTIN 由以下部分组成：

1. **Header (8位)**: 根据标签大小不同
   - 96位: `00110000`
   - 198位: `00110001`
2. **Filter (3位)**: 过滤值，范围 0-7，具有特定含义
3. **Partition (3位)**: 分区值，根据公司前缀长度确定
4. **Company Prefix (可变)**: 公司前缀，长度取决于分区
5. **Item Reference (可变)**: 项目参考，长度与公司前缀互补
6. **Serial (可变)**: 序列号
   - 96位标签：38位二进制，最大值为 274,877,906,943
   - 198位标签：可变长度字符串编码

### GTIN 结构

GTIN (CI参数) 是一个14位的数字，结构如下：

```
[指示符(1位)][公司前缀(N位)][项目参考(M位)][校验位(1位)]
```

其中 N + M = 12，N 的范围是 6-12。

**示例：**
- GTIN: `01234567890128`
- 指示符: `0`
- 公司前缀 (7位): `1234567`
- 项目参考 (5位): `89012`
- 校验位: `8`

### 公司前缀与项目参考关系

| 公司前缀长度 | 项目参考长度 | 分区值 |
|------------|------------|--------|
| 12         | 1          | 000    |
| 11         | 2          | 001    |
| 10         | 3          | 010    |
| 9          | 4          | 011    |
| 8          | 5          | 100    |
| 7          | 6          | 101    |
| 6          | 7          | 110    |

### 过滤值含义

| 值 | 含义 |
|---|------|
| 0 | All Others (其他所有) |
| 1 | Point of Sale (POS) Trade Item (零售商品) |
| 2 | Full Case for Transport (运输整箱) |
| 3 | Reserved (保留) |
| 4 | Inner Pack Trade Item Grouping for Handling (内包装组合) |
| 5 | Reserved (保留) |
| 6 | Unit Load (单元负载) |
| 7 | Unit inside Trade Item or component inside a product not intended for individual sale (内部单元或组件) |

### URI 格式

- **Pure Identity URI**: `urn:epc:id:sgtin:{companyPrefix}.{itemReference}.{serial}`
- **Tag URI**: `urn:epc:tag:sgtin-{tagSize}:{filter}.{companyPrefix}.{itemReference}.{serial}`
- **Raw URI**: `urn:epc:raw:{tagSize}:{binary}`

## 参数说明

### setSchemeParameters

需要传入的参数：

- `CI`: GTIN (Global Trade Item Number)，必须是14位数字，包含校验位
- `serial`: 序列号，可以是数字或字符串

### getSchemeParameterFields

返回 SGTIN 参数字段的定义：

```php
[
    "CI" => [
        "label" => "GTIN (01)",
        "max" => 99999999999999,
        "min" => 0,
        "type" => "int",
        "pattern" => "/^[0-9]{14}$/g",
        "msg" => "Should be exactly 14 digits long",
        "AI" => "01"
    ],
    "serial" => [
        "label" => "Serial (21)",
        "max" => 274877906943,
        "min" => 0,
        "type" => "int",
        "pattern" => "/^[!%-?A-Z_a-z\x22]{1,20}$/g",
        "msg" => "Should be 1~20 digits long",
        "AI" => "21"
    ]
]
```

## GTIN 校验位计算

SGTIN 提供了 `getCheckDigit()` 方法来计算 GTIN 的校验位。校验位是根据前13位数字通过特定算法计算得出的。

### 计算方法

```php
<?php
use Mickeywaugh\Gs1\Epc\Sgtin;

$sgtin = new Sgtin();

// 计算校验位（输入14位GTIN，最后一位会被重新计算）
$gtin13 = "0123456789012";  // 前13位
$checkDigit = $sgtin->getCheckDigit($gtin13 . "0");  // 添加占位符
echo "校验位: " . $checkDigit;  // 输出: 8

// 完整的GTIN应该是: 01234567890128
```

### 校验位算法

校验位计算公式：
```
checkDigit = (10 - ((3 × (d1 + d3 + d5 + d7 + d9 + d11 + d13) + 
                      (d2 + d4 + d6 + d8 + d10 + d12)) % 10)) % 10
```

其中 d1, d2, ..., d13 是 GTIN 的前13位数字。

## 应用场景

SGTIN 适用于以下场景：

- 零售业商品追踪和管理
- 供应链中的单品级追踪
- 库存管理和物流追踪
- 防伪和产品认证
- 召回管理
- 消费者产品信息查询
- 电子商务产品标识

## 完整示例代码

### 示例1: 基本编码和解码

```php
<?php
require_once 'vendor/autoload.php';
use Mickeywaugh\Gs1\Gs1;

// 编码
$sgtin = Gs1::Sgtin([
    7,      // 公司前缀长度
    96,     // 标签大小
    1,      // 过滤值 (POS商品)
    [
        'CI' => '01234567890128',  // GTIN
        'serial' => 'ABC123'       // 序列号
    ]
]);

if ($sgtin->encode()) {
    echo "十六进制: " . $sgtin->getEpcHexaDecimal() . "\n";
    echo "URI: " . $sgtin->getEpcUri() . "\n";
    
    // 解码验证
    $decoded = \Mickeywaugh\Gs1\Epc\Sgtin::decode($sgtin->getEpcHexaDecimal());
    if ($decoded) {
        echo "解码GTIN: " . $decoded->getCI() . "\n";
        echo "解码序列号: " . $decoded->getSerial() . "\n";
    }
}
```

### 示例2: 批量处理不同公司前缀长度

```php
<?php
$testCases = [
    ['prefixLen' => 6, 'CI' => '01234567890128'],
    ['prefixLen' => 7, 'CI' => '01234567890128'],
    ['prefixLen' => 8, 'CI' => '01234567890128'],
];

foreach ($testCases as $case) {
    $sgtin = Gs1::Sgtin([
        $case['prefixLen'],
        96,
        0,
        ['CI' => $case['CI'], 'serial' => '1']
    ]);
    
    if ($sgtin->encode()) {
        echo "前缀长度 {$case['prefixLen']}: " . 
             "公司前缀=" . $sgtin->getCompanyPrefix() . 
             ", 项目参考=" . $sgtin->getItemReference() . "\n";
    }
}
```

### 示例3: 错误处理

```php
<?php
// 测试无效的公司前缀长度
$badSgtin = Gs1::Sgtin([
    15,  // 无效：应该是6-12
    96,
    0,
    ['CI' => '01234567890128', 'serial' => '1']
]);

if (!$badSgtin->encode()) {
    echo "错误: " . $badSgtin->getErrorMsg() . "\n";
}

// 测试缺失参数
$badSgtin2 = Gs1::Sgtin([
    7,
    96,
    0,
    ['CI' => '01234567890128']  // 缺少 serial
]);

if (!$badSgtin2->encode()) {
    echo "错误: " . $badSgtin2->getErrorMsg() . "\n";
}
```

## 注意事项

1. **GTIN 格式**: CI 参数必须是14位数字，包含校验位
2. **校验位**: SGTIN 会自动验证和处理 GTIN 的校验位
3. **96位标签限制**: 序列号最大为 274,877,906,943 (38位二进制)
4. **198位标签**: 支持更长的可变长度序列号（最多20个字符）
5. **过滤值**: 应根据实际应用场景选择合适的过滤值
6. **公司前缀长度**: 必须与实际的公司前缀长度一致，否则会导致编码错误

## 与 GDTI 的区别

| 特性 | SGTIN | GDTI |
|-----|-------|------|
| 用途 | 贸易项目标识 | 文档类型标识 |
| CI长度 | 14位 (GTIN) | 13位 (公司前缀+文档类型) |
| 校验位 | 需要 | 不需要 |
| 标签大小 | 96, 198位 | 96, 113位 |
| AI代码 | 01 (GTIN), 21 (序列号) | 253 (GDTI) |
| 过滤值含义 | 有特定含义 | 通常为0 (All Others) |

## 相关资源

- GS1 EPC Tag Data Standard: https://www.gs1.org/standards/epc-rfid-tag-data-standard
- GS1 General Specifications: https://www.gs1.org/standards/barcodes-epcrfid-id-keys
- 更多示例请查看 `examples/sgtin_example.php` 文件
