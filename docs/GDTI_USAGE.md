# GDTI EPC 编码解码使用说明

## 概述

GDTI (Global Document Type Identifier) 是 GS1 标准下用于唯一标识文档类型的 EPC 编码方案。本实现遵循 GS1 EPC Tag Data Standard Release 1.13 规范。

## 主要特性

- 支持 96 位和 113 位标签编码
- 支持公司前缀长度 6-12 位
- 支持过滤值 0-7
- 完整的编码和解码功能
- 符合 GS1 标准规范

## 基本用法

### 编码示例

```php
<?php
use Mickeywaugh\Gs1\Gs1;

// 创建 GDTI 实例
$companyPrefixLength = 7;  // 公司前缀长度 (6-12)
$tagSize = 96;             // 标签大小 (96 或 113)
$filterValue = 0;          // 过滤值 (0-7)

// CI (Company Identifier) = 公司前缀 + 文档类型，总共13位
$CI = "1234567123456";     // 7位公司前缀 + 6位文档类型
$serial = "ABC123";        // 序列号

$schemeParameters = [
    'CI' => $CI,
    'serial' => $serial
];

$gdtiEpc = Gs1::Gdti([
    $companyPrefixLength,
    $tagSize,
    $filterValue,
    $schemeParameters
]);

// 执行编码
$result = $gdtiEpc->encode();

if ($result->hasError()) {
   echo "编码失败: " . $gdtiEpc->getErrorMsg() . "\n";
} else {
     // 获取编码结果
    echo "EPC URI: " . $gdtiEpc->getEpcUri() . "\n";
    echo "EPC Tag URI: " . $gdtiEpc->getEpcTagURI() . "\n";
    echo "EPC Raw URI: " . $gdtiEpc->getEpcRawURI() . "\n";
    echo "EPC 二进制: " . $gdtiEpc->getEpcBinary() . "\n";
    echo "EPC 十六进制: " . $gdtiEpc->getEpcHexaDecimal() . "\n";
}
```

### 解码示例

```php
<?php
use Mickeywaugh\Gs1\Epc\Gdti;

// 从十六进制字符串解码
$epcHex = "3074257BF7194E7340000000";
$decodedEpc = Gdti::decode($epcHex);

if (!$decodedEpc->hasError()) {
    echo "公司前缀长度: " . $decodedEpc->getCompanyPrefixLength() . "\n";
    echo "标签大小: " . $decodedEpc->getTagSize() . " bits\n";
    echo "过滤值: " . $decodedEpc->getFilterValue() . "\n";
    echo "公司前缀: " . $decodedEpc->getCompanyPrefix() . "\n";
    echo "文档类型: " . $decodedEpc->getItemReference() . "\n";
    echo "序列号: " . $decodedEpc->getSerial() . "\n";
    echo "EPC URI: " . $decodedEpc->getEpcUri() . "\n";
    echo "EPC Tag URI: " . $decodedEpc->getEpcTagURI() . "\n";
} else {
    echo "解码失败!\n";
}
```

## GDTI 结构说明

### 字段组成

GDTI 由以下部分组成：

1. **Header (8位)**: 固定为 `00101100`
2. **Filter (3位)**: 过滤值，范围 0-7
3. **Partition (3位)**: 分区值，根据公司前缀长度确定
4. **Company Prefix (可变)**: 公司前缀，长度取决于分区
5. **Document Type (可变)**: 文档类型，长度与公司前缀互补（总共13位）
6. **Serial (可变)**: 序列号
   - 96位标签：41位二进制，最大值为 2,199,023,255,551
   - 113位标签：可变长度字符串编码

### 公司前缀与文档类型关系

| 公司前缀长度 | 文档类型长度 | 分区值 |
|------------|------------|--------|
| 12         | 1          | 000    |
| 11         | 2          | 001    |
| 10         | 3          | 010    |
| 9          | 4          | 011    |
| 8          | 5          | 100    |
| 7          | 6          | 101    |
| 6          | 7          | 110    |

### URI 格式

- **Pure Identity URI**: `urn:epc:id:gdti:{companyPrefix}.{doctype}.{serial}`
- **Tag URI**: `urn:epc:tag:gdti-{tagSize}:{filter}.{companyPrefix}.{doctype}.{serial}`
- **Raw URI**: `urn:epc:raw:{tagSize}:{binary}`

## 参数说明

### setSchemeParameters

需要传入的参数：

- `CI`: 公司标识符，由公司前缀和文档类型组成，共13位数字
- `serial`: 序列号，可以是数字或字符串

### getSchemeParameterFields

返回 GDTI 参数字段的定义：

```php
[
    "CI" => [
        "label" => "GDTI (253)",
        "max" => 999999999999999,
        "min" => 0,
        "type" => "int",
        "pattern" => "/^[0-9]{13}$/g",
        "msg" => "Should be exactly 13 digits long (Company Prefix + Document Type)",
        "AI" => "253"
    ],
    "serial" => [
        "label" => "Serial",
        "max" => 81985529216486895,
        "min" => 0,
        "type" => "int",
        "pattern" => "/^[!%-?A-Z_a-z\x22]{1,17}$/g",
        "msg" => "Should be 1~17 characters long"
    ]
]
```

## 应用场景

GDTI 适用于以下场景：

- 文档管理和追踪
- 证书和许可证标识
- 合同和协议管理
- 票据和发票系统
- 任何需要唯一标识文档的物联网应用

## 注意事项

1. GDTI 不需要校验位计算（与 SGTIN 不同）
2. CI 参数必须是13位数字，由公司前缀和文档类型组成
3. 96位标签的序列号限制为41位二进制数
4. 113位标签支持更长的可变长度序列号
5. 过滤值通常设置为0（All Others），除非有特殊需求

## 完整示例

更多示例请查看 `examples/gdti_example.php` 文件。
