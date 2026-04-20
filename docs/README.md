# GS1 EPC 编码库文档索引

欢迎使用 GS1 EPC 编码库！本文档索引帮助您快速找到所需的信息。

## 📚 文档导航

### 快速开始

- **[README.md](../README.md)** - 项目概述和基本使用方法
- **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** ⚡ - 快速参考卡片（5分钟上手）

### 详细使用指南

#### SGTIN (Serialized Global Trade Item Number)
- **[SGTIN_USAGE.md](SGTIN_USAGE.md)** - SGTIN 完整使用指南
  - 编码和解码示例
  - GTIN 校验位计算
  - 过滤值说明
  - 错误处理
  - 最佳实践

#### GDTI (Global Document Type Identifier)
- **[GDTI_USAGE.md](GDTI_USAGE.md)** - GDTI 完整使用指南
  - 编码和解码示例
  - 文档类型标识
  - 参数说明
  - 应用场景

### 对比和选择

- **[EPC_COMPARISON.md](EPC_COMPARISON.md)** - EPC 编码类型对比指南
  - 各编码方案详细对比
  - 选择指南
  - 公司前缀长度选择
  - 常见错误和解决方案
  - 性能考虑

### 示例代码

- **[examples/sgtin_example.php](../examples/sgtin_example.php)** - SGTIN 完整示例
- **[examples/gdti_example.php](../examples/gdti_example.php)** - GDTI 完整示例

## 🎯 我应该从哪里开始？

### 如果您是新手

1. 先阅读 [README.md](../README.md) 了解项目基本情况
2. 根据您的业务需求选择编码类型：
   - 商品/贸易项目 → 查看 [SGTIN_USAGE.md](SGTIN_USAGE.md)
   - 文档/证书 → 查看 [GDTI_USAGE.md](GDTI_USAGE.md)
3. 运行对应的示例文件查看实际效果
4. 如有多个编码需求，参考 [EPC_COMPARISON.md](EPC_COMPARISON.md)

### 如果您需要对比不同编码方案

直接查看 [EPC_COMPARISON.md](EPC_COMPARISON.md)，它提供了：
- 详细的对比表格
- 选择建议
- 实际应用场景分析

### 如果您需要快速参考

查看示例代码文件：
- `examples/sgtin_example.php`
- `examples/gdti_example.php`

这些文件包含完整的、可运行的代码示例。

## 📋 支持的 EPC 编码类型

| 编码类型 | 用途 | 文档链接 | 示例文件 |
|---------|------|---------|---------|
| **SGTIN** | 贸易项目单品标识 | [SGTIN_USAGE.md](SGTIN_USAGE.md) | [sgtin_example.php](../examples/sgtin_example.php) |
| **GDTI** | 文档类型标识 | [GDTI_USAGE.md](GDTI_USAGE.md) | [gdti_example.php](../examples/gdti_example.php) |
| SSCC | 物流单元标识 | 待补充 | 待补充 |
| SGLN | 位置标识 | 待补充 | 待补充 |
| GRAI | 可回收资产标识 | 待补充 | 待补充 |
| GIAI | 单个资产标识 | 待补充 | 待补充 |
| GSRN | 服务关系标识 | 待补充 | 待补充 |
| GID | 通用标识 | 待补充 | 待补充 |

## 🔧 核心功能

### 编码 (Encoding)

将业务数据转换为 EPC 编码格式：

```php
use Mickeywaugh\Gs1\Gs1;

// SGTIN 编码示例
$sgtin = Gs1::Sgtin([
    $companyPrefixLength,  // 公司前缀长度 (6-12)
    $tagSize,              // 标签大小 (96, 198等)
    $filterValue,          // 过滤值 (0-7)
    [
        'CI' => $gtin,     // GTIN 或 CI
        'serial' => $serial // 序列号
    ]
]);

if ($sgtin->encode()) {
    $hex = $sgtin->getEpcHexaDecimal();  // 十六进制
    $uri = $sgtin->getEpcUri();          // URI
}
```

### 解码 (Decoding)

将 EPC 编码还原为业务数据：

```php
use Mickeywaugh\Gs1\Epc\Sgtin;

$decoded = Sgtin::decode($epcHex);
if ($decoded) {
    $gtin = $decoded->getCI();           // GTIN
    $serial = $decoded->getSerial();     // 序列号
    $uri = $decoded->getEpcUri();        // URI
}
```

## 📖 关键概念

### 公司前缀 (Company Prefix)

由 GS1 组织分配的唯一标识符，长度 6-12 位。长度越短，可用的项目编码空间越大。

### 标签大小 (Tag Size)

EPC 二进制编码的位数，常见的有：
- **96位**: 固定长度，适合大多数场景
- **198位/113位**: 可变长度，支持更长的序列号

### 过滤值 (Filter Value)

用于区分不同的应用场景，SGTIN 中含义丰富，GDTI 中通常为 0。

### URI 格式

- **Pure Identity URI**: 纯净标识符 URI
- **Tag URI**: 标签 URI（包含过滤值和标签大小）
- **Raw URI**: 原始 URI（包含二进制数据）

## ⚠️ 常见问题

### Q1: 如何选择公司前缀长度？

A: 公司前缀长度由 GS1 组织分配，取决于您的企业规模和产品数量。详见 [EPC_COMPARISON.md](EPC_COMPARISON.md) 中的"公司前缀长度选择"章节。

### Q2: SGTIN 和 GDTI 有什么区别？

A: SGTIN 用于标识贸易项目（商品），需要14位 GTIN 和校验位；GDTI 用于标识文档，使用13位 CI，不需要校验位。详见 [EPC_COMPARISON.md](EPC_COMPARISON.md)。

### Q3: 96位和198位标签如何选择？

A: 如果序列号较短（数字或简短字符串），使用96位；如果需要长序列号或描述性标识符，使用198位。详见各类型的详细文档。

### Q4: 如何处理编码错误？

A: 调用 `encode()` 后检查返回值，如果返回 null，使用 `getErrorMsg()` 获取错误信息。示例代码中包含完整的错误处理演示。

### Q5: 是否支持批量处理？

A: 是的，可以循环创建实例并编码。建议在批量处理时复用对象以减少内存占用。

## 🚀 快速示例

### SGTIN 示例

``php
<?php
require_once 'vendor/autoload.php';
use Mickeywaugh\Gs1\Gs1;

// 编码
$sgtin = Gs1::Sgtin([
    7, 96, 1,
    ['CI' => '01234567890128', 'serial' => 'ABC123']
]);

if ($sgtin->encode()) {
    echo "十六进制: " . $sgtin->getEpcHexaDecimal() . "\n";
    echo "URI: " . $sgtin->getEpcUri() . "\n";
}

// 解码
$decoded = \Mickeywaugh\Gs1\Epc\Sgtin::decode($sgtin->getEpcHexaDecimal());
if ($decoded) {
    echo "GTIN: " . $decoded->getCI() . "\n";
    echo "序列号: " . $decoded->getSerial() . "\n";
}
```

### GDTI 示例

``php
<?php
require_once 'vendor/autoload.php';
use Mickeywaugh\Gs1\Gs1;

// 编码
$gdti = Gs1::Gdti([
    7, 96, 0,
    ['CI' => '1234567123456', 'serial' => 'DOC001']
]);

if ($gdti->encode()) {
    echo "十六进制: " . $gdti->getEpcHexaDecimal() . "\n";
    echo "URI: " . $gdti->getEpcUri() . "\n";
}

// 解码
$decoded = \Mickeywaugh\Gs1\Epc\Gdti::decode($gdti->getEpcHexaDecimal());
if ($decoded) {
    echo "CI: " . $decoded->getCI() . "\n";
    echo "序列号: " . $decoded->getSerial() . "\n";
}
```

## 📝 更新日志

### 当前版本
- ✅ SGTIN 完整实现和文档
- ✅ GDTI 完整实现和文档
- ✅ 对比指南
- ✅ 完整示例代码

### 计划中
- ⏳ SSCC 实现和文档
- ⏳ SGLN 实现和文档
- ⏳ GRAI 实现和文档
- ⏳ GIAI 实现和文档
- ⏳ 更多示例和最佳实践

## 🔗 相关链接

- **GS1 官方网站**: https://www.gs1.org
- **EPC Tag Data Standard**: https://www.gs1.org/standards/epc-rfid-tag-data-standard
- **GitHub 仓库**: [项目地址]
- **问题反馈**: [Issues 页面]

## 💡 贡献指南

欢迎贡献代码、文档或示例！请遵循以下步骤：

1. Fork 本仓库
2. 创建特性分支
3. 提交更改
4. 推送到分支
5. 创建 Pull Request

## 📧 联系方式

如有问题或建议，请通过以下方式联系：

- 提交 Issue
- 发送邮件至: Mickeywaugh@qq.com

---

**最后更新**: 2024年  
**维护者**: Mickeywaugh
