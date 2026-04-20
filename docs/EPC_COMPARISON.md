# GS1 EPC 编码类型对比指南

## 概述

本库支持多种 GS1 EPC 编码方案，每种方案针对不同的应用场景。本文档提供各编码方案的详细对比和使用指导。

## 支持的 EPC 类型

### 1. SGTIN (Serialized Global Trade Item Number)

**用途**: 标识单个贸易项目（商品）

**特点**:
- CI 长度: 14位 GTIN（包含校验位）
- 标签大小: 96位、198位
- 需要校验位计算
- AI代码: 01 (GTIN), 21 (序列号)

**典型应用**:
- 零售业商品管理
- 供应链单品追踪
- 库存管理
- 防伪认证

**使用示例**:
```php
use Mickeywaugh\Gs1\Gs1;

$sgtin = Gs1::Sgtin([
    7,  // 公司前缀长度
    96, // 标签大小
    1,  // 过滤值 (POS商品)
    [
        'CI' => '01234567890128',  // 14位GTIN
        'serial' => 'ABC123'       // 序列号
    ]
]);
$sgtin->encode();
```

---

### 2. GDTI (Global Document Type Identifier)

**用途**: 标识文档类型

**特点**:
- CI 长度: 13位（公司前缀 + 文档类型）
- 标签大小: 96位、113位
- 不需要校验位计算
- AI代码: 253 (GDTI)

**典型应用**:
- 文档管理和追踪
- 证书和许可证标识
- 合同和协议管理
- 票据和发票系统

**使用示例**:
```php
use Mickeywaugh\Gs1\Gs1;

$gdti = Gs1::Gdti([
    7,  // 公司前缀长度
    96, // 标签大小
    0,  // 过滤值
    [
        'CI' => '1234567123456',  // 13位CI (7位公司前缀 + 6位文档类型)
        'serial' => 'DOC001'      // 序列号
    ]
]);
$gdti->encode();
```

---

## 详细对比表

### 基本属性对比

| 特性 | SGTIN | GDTI | SSCC | SGLN | GRAI | GIAI |
|-----|-------|------|------|------|------|------|
| **全称** | Serialized GTIN | Global Document Type Identifier | Serial Shipping Container Code | Serial Global Location Number | Global Returnable Asset Identifier | Global Individual Asset Identifier |
| **中文名称** | 序列化全球贸易项目编号 | 全球文档类型标识符 | 系列货运包装箱代码 | 序列化全球位置码 | 全球可回收资产标识符 | 全球单个资产标识符 |
| **主要用途** | 商品单品标识 | 文档标识 | 物流单元标识 | 位置标识 | 可回收资产标识 | 固定资产标识 |
| **AI代码** | 01, 21 | 253 | 00 | 414, 254 | 8003, 21 | 8004 |

### 技术参数对比

| 参数 | SGTIN | GDTI |
|-----|-------|------|
| **CI长度** | 14位 (GTIN) | 13位 |
| **CI组成** | 指示符(1) + 公司前缀 + 项目参考 + 校验位(1) | 公司前缀 + 文档类型 |
| **公司前缀范围** | 6-12位 | 6-12位 |
| **标签大小** | 96, 198位 | 96, 113位 |
| **校验位** | ✓ 需要 | ✗ 不需要 |
| **过滤值含义** | 有特定业务含义 | 通常为0 |
| **序列号最大长度** | 96位: 38bit<br>198位: 可变(最多20字符) | 96位: 41bit<br>113位: 可变(最多17字符) |

### URI格式对比

**SGTIN**:
```
Pure Identity: urn:epc:id:sgtin:{companyPrefix}.{itemReference}.{serial}
Tag URI:       urn:epc:tag:sgtin-{size}:{filter}.{companyPrefix}.{itemReference}.{serial}
Raw URI:       urn:epc:raw:{size}:{binary}
```

**GDTI**:
```
Pure Identity: urn:epc:id:gdti:{companyPrefix}.{doctype}.{serial}
Tag URI:       urn:epc:tag:gdti-{size}:{filter}.{companyPrefix}.{doctype}.{serial}
Raw URI:       urn:epc:raw:{size}:{binary}
```

### 二进制结构对比

**SGTIN (96位)**:
```
Header (8) + Filter (3) + Partition (3) + Company Prefix (可变) + 
Item Reference (可变) + Serial (38)
```

**GDTI (96位)**:
```
Header (8) + Filter (3) + Partition (3) + Company Prefix (可变) + 
Document Type (可变) + Serial (41)
```

## 选择指南

### 何时使用 SGTIN？

✓ 您需要标识零售商品或贸易项目  
✓ 您需要单品级别的追踪能力  
✓ 您已有 GTIN 编码体系  
✓ 应用场景包括：
  - 零售收银
  - 库存管理
  - 产品追溯
  - 防伪验证
  - 消费者互动

**示例场景**:
```php
// 超市中的瓶装水单品
$waterBottle = Gs1::Sgtin([
    7,   // 公司前缀长度
    96,  // 标签大小
    1,   // POS商品
    [
        'CI' => '06901234567892',     // 水的GTIN
        'serial' => 'BATCH20240101'   // 批次序列号
    ]
]);
```

### 何时使用 GDTI？

✓ 您需要标识文档或文件  
✓ 您需要管理证书、许可证或合同  
✓ 应用场景包括：
  - 电子文档管理
  - 证书追踪
  - 合同管理
  - 票据系统
  - 档案管理

**示例场景**:
```php
// 产品质量证书
$certificate = Gs1::Gdti([
    8,   // 公司前缀长度
    96,  // 标签大小
    0,   // 其他
    [
        'CI' => '1234567812345',      // 8位公司前缀 + 5位文档类型
        'serial' => 'CERT-2024-001'   // 证书编号
    ]
]);
```

## 公司前缀长度选择

公司前缀长度由 GS1 组织分配，取决于您的公司规模和产品数量需求：

| 公司前缀长度 | 可用项目编码数量 | 适用企业类型 |
|------------|----------------|------------|
| 6位 | 100,000 | 大型企业，产品线广泛 |
| 7位 | 10,000 | 中大型企业 |
| 8位 | 1,000 | 中型企业 |
| 9位 | 100 | 小型企业 |
| 10位 | 10 | 微型企业 |
| 11位 | 1 | 单一产品企业 |
| 12位 | 0 (仅用于特殊场景) | 特殊情况 |

**注意**: 公司前缀越短，可用的项目编码空间越大。

## 过滤值最佳实践

### SGTIN 过滤值

```php
// 零售商品
$filterValue = 1;  // Point of Sale

// 整箱运输
$filterValue = 2;  // Full Case for Transport

// 内包装组合
$filterValue = 4;  // Inner Pack

// 托盘/单元负载
$filterValue = 6;  // Unit Load
```

### GDTI 过滤值

```php
// 大多数情况下使用 0
$filterValue = 0;  // All Others
```

## 编码解码完整流程

### SGTIN 完整流程

```php
<?php
use Mickeywaugh\Gs1\Gs1;
use Mickeywaugh\Gs1\Epc\Sgtin;

// 1. 编码
$sgtin = Gs1::Sgtin([
    7,
    96,
    1,
    [
        'CI' => '01234567890128',
        'serial' => 'SN001'
    ]
]);

if ($sgtin->encode()) {
    // 2. 获取编码结果
    $hex = $sgtin->getEpcHexaDecimal();
    $uri = $sgtin->getEpcUri();
    $tagUri = $sgtin->getEpcTagURI();
    
    echo "十六进制: {$hex}\n";
    echo "URI: {$uri}\n";
    echo "Tag URI: {$tagUri}\n";
    
    // 3. 存储或传输 $hex
    
    // 4. 解码（从存储或接收的数据）
    $decoded = Sgtin::decode($hex);
    if ($decoded) {
        echo "GTIN: " . $decoded->getCI() . "\n";
        echo "序列号: " . $decoded->getSerial() . "\n";
        echo "公司前缀: " . $decoded->getCompanyPrefix() . "\n";
    }
}
```

### GDTI 完整流程

```php
<?php
use Mickeywaugh\Gs1\Gs1;
use Mickeywaugh\Gs1\Epc\Gdti;

// 1. 编码
$gdti = Gs1::Gdti([
    7,
    96,
    0,
    [
        'CI' => '1234567123456',
        'serial' => 'DOC001'
    ]
]);

if ($gdti->encode()) {
    // 2. 获取编码结果
    $hex = $gdti->getEpcHexaDecimal();
    $uri = $gdti->getEpcUri();
    
    echo "十六进制: {$hex}\n";
    echo "URI: {$uri}\n";
    
    // 3. 存储或传输 $hex
    
    // 4. 解码
    $decoded = Gdti::decode($hex);
    if ($decoded) {
        echo "CI: " . $decoded->getCI() . "\n";
        echo "序列号: " . $decoded->getSerial() . "\n";
    }
}
```

## 常见错误和解决方案

### 错误1: 公司前缀长度不匹配

```php
// ❌ 错误：CI是14位，但公司前缀长度设置不正确
$sgtin = Gs1::Sgtin([
    10,  // 错误：实际公司前缀是7位
    96,
    0,
    ['CI' => '01234567890128', 'serial' => '1']  // 公司前缀是1234567 (7位)
]);

// ✓ 正确
$sgtin = Gs1::Sgtin([
    7,  // 与公司前缀实际长度一致
    96,
    0,
    ['CI' => '01234567890128', 'serial' => '1']
]);
```

### 错误2: CI长度不正确

```php
// ❌ SGTIN: CI必须是14位
$sgtin = Gs1::Sgtin([
    7, 96, 0,
    ['CI' => '0123456789012', 'serial' => '1']  // 只有13位
]);

// ✓ 正确：14位GTIN
$sgtin = Gs1::Sgtin([
    7, 96, 0,
    ['CI' => '01234567890128', 'serial' => '1']  // 14位
]);

// ❌ GDTI: CI必须是13位
$gdti = Gs1::Gdti([
    7, 96, 0,
    ['CI' => '123456712345', 'serial' => '1']  // 只有12位
]);

// ✓ 正确：13位CI
$gdti = Gs1::Gdti([
    7, 96, 0,
    ['CI' => '1234567123456', 'serial' => '1']  // 13位
]);
```

### 错误3: 缺少必需参数

```php
// ❌ 缺少 serial 参数
$sgtin = Gs1::Sgtin([
    7, 96, 0,
    ['CI' => '01234567890128']  // 缺少 serial
]);

// ✓ 正确：包含所有必需参数
$sgtin = Gs1::Sgtin([
    7, 96, 0,
    [
        'CI' => '01234567890128',
        'serial' => 'ABC123'
    ]
]);
```

## 性能考虑

### 96位 vs 可变长度标签

**96位标签**:
- ✓ 固定长度，处理速度快
- ✓ 存储空间小
- ✗ 序列号长度受限
- 适用：大批量、序列号较短的场景

**可变长度标签 (198位/113位)**:
- ✓ 支持长序列号
- ✓ 更灵活
- ✗ 存储空间较大
- ✗ 处理稍慢
- 适用：需要描述性序列号的场景

### 批量处理建议

```php
// 高效批量编码
$items = [
    ['CI' => '01234567890128', 'serial' => '001'],
    ['CI' => '01234567890128', 'serial' => '002'],
    // ... 更多项目
];

$results = [];
foreach ($items as $item) {
    $sgtin = Gs1::Sgtin([7, 96, 1, $item]);
    if ($sgtin->encode()) {
        $results[] = $sgtin->getEpcHexaDecimal();
    }
}
```

## 相关文档

- [SGTIN 详细使用说明](SGTIN_USAGE.md)
- [GDTI 详细使用说明](GDTI_USAGE.md)
- [示例代码](../examples/)

## 参考资料

- GS1 EPC Tag Data Standard Release 1.13
- GS1 General Specifications
- GS1 Application Identifier Standards
