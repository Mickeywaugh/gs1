# GS1 EPC 编码解码库

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Composer](https://img.shields.io/badge/composer-mickeywaugh%2Fgs1-orange.svg)](https://packagist.org/packages/mickeywaugh/gs1)

## 📖 简介

这是一个基于 PHP 8.1+ 开发的 GS1 EPC（电子产品代码）编码解码库，完全遵循 **GS1 EPC Tag Data Standard Release 1.13** 规范。

### ✨ 特性

- ✅ 支持多种 EPC 编码方案（SGTIN、GDTI 等）
- ✅ 完整的编码和解码功能
- ✅ 支持多种标签大小（96位、113位、198位等）
- ✅ 生成标准 URI 格式（Pure Identity URI、Tag URI、Raw URI）
- ✅ 二进制和十六进制输出
- ✅ 完善的错误处理机制
- ✅ 详细的 PHPDoc 文档
- ✅ PSR-4 自动加载
- ✅ MIT 开源许可

### 🎯 支持的编码方案

| 方案 | 描述 | 标签大小 |
|------|------|----------|
| **SGTIN** | Serialized Global Trade Item Number（带序列号的全球贸易项目代码） | 96, 198 bits |
| **GDTI** | Global Document Type Identifier（全球文档类型标识符） | 96, 113 bits |

> 更多方案（SSCC、SGLN、GRAI、GIAI 等）正在开发中...

## 📦 安装

使用 Composer 安装：

```bash
composer require mickeywaugh/gs1
```

或在 `composer.json` 中添加：

```json
{
    "require": {
        "mickeywaugh/gs1": "^1.0"
    }
}
```

## 🚀 快速开始

### SGTIN 编码示例

```php
<?php

use Mickeywaugh\Gs1\Gs1;

// 创建 SGTIN 实例
$sgtin = Gs1::Sgtin([
    'companyPrefixLength' => 7,      // 公司前缀长度 (6-12)
    'tagSize' => 96,                  // 标签大小 (96 或 198)
    'filterValue' => 1,               // 过滤值 (0-7)
    'schemeParameters' => [
        'CI' => '01234567890128',     // 14位 GTIN（含校验位）
        'serial' => 'ABC123'          // 序列号
    ]
]);

// 执行编码
$result = $sgtin->encode();

if ($result) {
    // 获取编码结果
    echo "EPC URI: " . $sgtin->getEpcUri() . "\n";
    echo "Tag URI: " . $sgtin->getEpcTagURI() . "\n";
    echo "Raw URI: " . $sgtin->getEpcRawURI() . "\n";
    echo "Binary: " . $sgtin->getEpcBinary() . "\n";
    echo "Hex: " . $sgtin->getEpcHexaDecimal() . "\n";
} else {
    echo "编码失败: " . $sgtin->getErrorMsg() . "\n";
}
```

**输出示例：**
```
EPC URI: urn:epc:id:sgtin:1234567.0123456.ABC123
Tag URI: urn:epc:tag:sgtin-96:1.1234567.0123456.ABC123
Binary: 001100000101011101000010010101111111011100011001010011100111001101000000000000000000000000000000
Hex: 3074257BF7194E7340000000
```

### SGTIN 解码示例

```php
<?php

use Mickeywaugh\Gs1\Epc\Sgtin;

// 从十六进制字符串解码
$hexCode = "3074257BF7194E7340000000";
$decoded = Sgtin::decode($hexCode);

if ($decoded) {
    echo "GTIN: " . $decoded->getCI() . "\n";
    echo "序列号: " . $decoded->getSerial() . "\n";
    echo "公司前缀: " . $decoded->getCompanyPrefix() . "\n";
    echo "项目参考: " . $decoded->getItemReference() . "\n";
    echo "标签大小: " . $decoded->getTagSize() . " bits\n";
    echo "过滤值: " . $decoded->getFilterValue() . "\n";
} else {
    echo "解码失败\n";
}
```

### GDTI 编码示例

```php
<?php

use Mickeywaugh\Gs1\Gs1;

// 创建 GDTI 实例
$gdti = Gs1::Gdti([
    'companyPrefixLength' => 7,       // 公司前缀长度 (6-12)
    'tagSize' => 96,                   // 标签大小 (96 或 113)
    'filterValue' => 0,                // 过滤值 (0-7)
    'schemeParameters' => [
        'CI' => '1234567123456',       // 13位 CI（公司前缀 + 文档类型）
        'serial' => 'DOC-2024-001'     // 序列号
    ]
]);

// 执行编码
$result = $gdti->encode();

if ($result) {
    echo "EPC URI: " . $gdti->getEpcUri() . "\n";
    echo "Tag URI: " . $gdti->getEpcTagURI() . "\n";
    echo "Hex: " . $gdti->getEpcHexaDecimal() . "\n";
}
```

### GDTI 解码示例

```php
<?php

use Mickeywaugh\Gs1\Epc\Gdti;

// 从十六进制字符串解码
$hexCode = "2C00123456789ABCDEF01234";
$decoded = Gdti::decode($hexCode);

if ($decoded) {
    echo "公司前缀: " . $decoded->getCompanyPrefix() . "\n";
    echo "文档类型: " . $decoded->getItemReference() . "\n";
    echo "序列号: " . $decoded->getSerial() . "\n";
}
```

## 📚 详细文档

### SGTIN 参数说明

#### 公司前缀长度 (companyPrefixLength)
- **范围**: 6-12
- **说明**: GS1 分配给公司的唯一前缀长度

#### 标签大小 (tagSize)
- **96 bits**: 固定长度序列号（最大 38 位二进制，约 2740 亿）
- **198 bits**: 可变长度序列号（支持更长的序列号）

#### 过滤值 (filterValue)
| 值 | 含义 |
|----|------|
| 0 | 其他所有 |
| 1 | POS 零售商品 |
| 2 | 运输用整箱 |
| 3 | 保留 |
| 4 | 内包装贸易项目组 |
| 5 | 保留 |
| 6 | 单元负载 |
| 7 | 产品内部组件 |

#### 方案参数 (schemeParameters)

**CI (Control Indicator)**
- 14 位 GTIN 数字字符串
- 包含校验位
- 示例: `"01234567890128"`

**serial (序列号)**
- 96 位标签: 数字字符串
- 198 位标签: 1-20 个字符的字母数字字符串

### GDTI 参数说明

#### 标签大小 (tagSize)
- **96 bits**: 固定长度序列号
- **113 bits**: 可变长度序列号（支持 1-17 个字符）

#### 方案参数 (schemeParameters)

**CI (Company Identifier)**
- 13 位数字字符串
- 结构: 公司前缀 (6-12位) + 文档类型 (剩余位数)
- 示例: `"1234567123456"` (7位公司前缀 + 6位文档类型)

**serial (序列号)**
- 96 位标签: 数字字符串
- 113 位标签: 1-17 个字符的字母数字字符串

## 🔧 高级用法

### 获取完整输出数据

```php
$output = $sgtin->getOutput();
print_r($output);
```

输出包含：
- scheme: 方案信息
- tagSize: 标签大小
- filterValue: 过滤值
- companyPrefixLength: 公司前缀长度
- companyPrefix: 公司前缀
- itemReference: 项目参考
- serial: 序列号
- CI: 控制指示符
- epcURI: EPC URI
- epcTagURI: EPC Tag URI
- epcRawURI: EPC Raw URI
- epcBinary: 二进制字符串
- epcHexaDecimal: 十六进制字符串
- error: 错误信息（如果有）

### 错误处理

```php
$sgtin = Gs1::Sgtin([
    'companyPrefixLength' => 7,
    'tagSize' => 96,
    'filterValue' => 1,
    'schemeParameters' => [
        'CI' => 'INVALID_GTIN',  // 无效的 GTIN
        'serial' => '123'
    ]
]);

$result = $sgtin->encode();

if (!$result) {
    echo "错误代码: " . $sgtin->getErrorCode() . "\n";
    echo "错误消息: " . $sgtin->getErrorMsg() . "\n";
}
```

### 校验位计算

```php
use Mickeywaugh\Gs1\Epc\Sgtin;

$sgtin = new Sgtin();
$checkDigit = $sgtin->getCheckDigit("0123456789012");  // 返回 "8"
$fullGtin = "0123456789012" . $checkDigit;  // "01234567890128"
```

### 获取支持的选项

```php
$sgtin = new Sgtin();

// 获取支持的公司前缀长度
$prefixLengths = $sgtin->getCompanyPrefixLengthOptions();
// [6, 7, 8, 9, 10, 11, 12]

// 获取支持的标签大小
$tagSizes = $sgtin->getTagSizeOptions();
// [96 => "96 bits", 198 => "198 bits"]

// 获取支持的过滤值
$filters = $sgtin->getFilterValueOptions();
// [0 => "All Others", 1 => "Point of Sale...", ...]
```

## 📋 更多示例

查看 `examples/` 目录获取更多示例：

- [`examples/sgtin_example.php`](examples/sgtin_example.php) - SGTIN 完整示例
- [`examples/gdti_example.php`](examples/gdti_example.php) - GDTI 完整示例

运行示例：

```bash
php examples/sgtin_example.php
php examples/gdti_example.php
```

## 🏗️ 架构设计

```
src/
├── Gs1.php              # 主入口类（工厂模式）
├── Epc/
│   ├── EpcBase.php      # EPC 抽象基类
│   ├── Sgtin.php        # SGTIN 实现
│   ├── Gdti.php         # GDTI 实现
│   └── EpcMesg.php      # 错误消息管理
└── Spec/
    ├── EpcSpec.php      # EPC 规范工具类
    └── resData/         # JSON 配置文件
        ├── sgtin-96.json
        ├── sgtin-198.json
        ├── gdti-96.json
        ├── gdti-113.json
        └── ...
```

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 📄 许可证

本项目采用 MIT 许可证 - 详见 [LICENSE](LICENSE) 文件

## 👤 作者

**Mickeywaugh**
- Email: mickeywaugh@163.com
- GitHub: [@mickeywaugh](https://github.com/mickeywaugh)

## 🙏 致谢

- [GS1](https://www.gs1.org/) - 提供 EPC 标准规范
- [GS1 EPC Tag Data Standard Release 1.13](https://www.gs1.org/standards/epc-rfid-tag-data-standard)

## 📮 联系方式

如有问题或建议，请通过以下方式联系：
- 提交 GitHub Issue
- 发送邮件至 mickeywaugh@163.com

---

**⭐ 如果这个项目对你有帮助，请给我们一个 Star！**
