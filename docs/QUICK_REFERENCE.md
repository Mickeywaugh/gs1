# GS1 EPC 快速参考卡片

## 🚀 5分钟快速上手

### SGTIN - 商品标识

```php
use Mickeywaugh\Gs1\Gs1;

// 编码（3步）
$sgtin = Gs1::Sgtin([7, 96, 1, ['CI' => '01234567890128', 'serial' => 'SN001']]);
$sgtin->encode();
$hex = $sgtin->getEpcHexaDecimal();  // 获取十六进制编码

// 解码（1步）
$decoded = \Mickeywaugh\Gs1\Epc\Sgtin::decode($hex);
$gtin = $decoded->getCI();            // 获取 GTIN
$serial = $decoded->getSerial();      // 获取序列号
```

### GDTI - 文档标识

```php
use Mickeywaugh\Gs1\Gs1;

// 编码（3步）
$gdti = Gs1::Gdti([7, 96, 0, ['CI' => '1234567123456', 'serial' => 'DOC001']]);
$gdti->encode();
$hex = $gdti->getEpcHexaDecimal();    // 获取十六进制编码

// 解码（1步）
$decoded = \Mickeywaugh\Gs1\Epc\Gdti::decode($hex);
$ci = $decoded->getCI();              // 获取 CI
$serial = $decoded->getSerial();      // 获取序列号
```

## 📊 参数速查表

### SGTIN 参数

| 参数 | 说明 | 取值范围 | 示例 |
|-----|------|---------|------|
| companyPrefixLength | 公司前缀长度 | 6-12 | 7 |
| tagSize | 标签大小 | 96, 198 | 96 |
| filterValue | 过滤值 | 0-7 | 1 (POS商品) |
| CI | GTIN (14位) | 14位数字 | '01234567890128' |
| serial | 序列号 | 1-20字符 | 'ABC123' |

### GDTI 参数

| 参数 | 说明 | 取值范围 | 示例 |
|-----|------|---------|------|
| companyPrefixLength | 公司前缀长度 | 6-12 | 7 |
| tagSize | 标签大小 | 96, 113 | 96 |
| filterValue | 过滤值 | 0-7 | 0 (其他) |
| CI | 公司前缀+文档类型 | 13位数字 | '1234567123456' |
| serial | 序列号 | 1-17字符 | 'DOC001' |

## 🔍 常用方法

### 编码后获取结果

```php
$epc->getEpcBinary();        // 二进制字符串
$epc->getEpcHexaDecimal();   // 十六进制字符串
$epc->getEpcUri();           // Pure Identity URI
$epc->getEpcTagURI();        // Tag URI
$epc->getEpcRawURI();        // Raw URI
$epc->getCompanyPrefix();    // 公司前缀
$epc->getItemReference();    // 项目参考/文档类型
$epc->getSerial();           // 序列号
$epc->getCI();               // CI (GTIN 或 GDTI)
$epc->getErrorMsg();         // 错误信息（如果失败）
```

### 解码后获取信息

```php
$decoded->getCompanyPrefixLength();  // 公司前缀长度
$decoded->getTagSize();              // 标签大小
$decoded->getFilterValue();          // 过滤值
$decoded->getCompanyPrefix();        // 公司前缀
$decoded->getItemReference();        // 项目参考/文档类型
$decoded->getSerial();               // 序列号
$decoded->getCI();                   // CI
$decoded->getEpcUri();               // URI
```

## ⚡ 过滤值含义

### SGTIN 过滤值

| 值 | 含义 | 使用场景 |
|---|------|---------|
| 0 | All Others | 通用 |
| 1 | POS Trade Item | 零售商品 ✨常用 |
| 2 | Full Case | 整箱运输 |
| 4 | Inner Pack | 内包装 |
| 6 | Unit Load | 托盘/单元负载 ✨常用 |

### GDTI 过滤值

| 值 | 含义 | 使用场景 |
|---|------|---------|
| 0 | All Others | 几乎所有场景 ✨常用 |
| 1-7 | Reserved | 保留 |

## 🎯 选择指南

### 需要标识什么？

- **商品/产品** → 使用 **SGTIN**
  - CI = 14位 GTIN
  - 需要校验位
  
- **文档/证书** → 使用 **GDTI**
  - CI = 13位（公司前缀 + 文档类型）
  - 不需要校验位

### 标签大小选择？

- **序列号短（纯数字或短字符串）** → 96位
- **序列号长（描述性字符串）** → 198位(SGTIN) / 113位(GDTI)

## ❗ 常见错误

### 1. CI 长度错误

```php
// ❌ SGTIN: CI必须是14位
'CI' => '0123456789012'   // 13位 - 错误！

// ✓ 正确
'CI' => '01234567890128'  // 14位 - 正确！

// ❌ GDTI: CI必须是13位
'CI' => '123456712345'    // 12位 - 错误！

// ✓ 正确
'CI' => '1234567123456'   // 13位 - 正确！
```

### 2. 缺少参数

```php
// ❌ 缺少 serial
['CI' => '01234567890128']

// ✓ 完整参数
['CI' => '01234567890128', 'serial' => 'ABC123']
```

### 3. 公司前缀长度不匹配

```php
// CI = '01234567890128'，公司前缀是 '1234567' (7位)
// ❌ 错误
Gs1::Sgtin([10, 96, 0, ['CI' => '01234567890128', 'serial' => '1']]);

// ✓ 正确
Gs1::Sgtin([7, 96, 0, ['CI' => '01234567890128', 'serial' => '1']]);
```

## 💡 实用技巧

### 计算 GTIN 校验位

```php
$sgtin = new \Mickeywaugh\Gs1\Epc\Sgtin();
$gtin13 = '0123456789012';  // 前13位
$checkDigit = $sgtin->getCheckDigit($gtin13 . '0');
$fullGtin = $gtin13 . $checkDigit;  // '01234567890128'
```

### 批量编码

```php
$items = [
    ['CI' => '01234567890128', 'serial' => '001'],
    ['CI' => '01234567890128', 'serial' => '002'],
    ['CI' => '01234567890128', 'serial' => '003'],
];

$codes = [];
foreach ($items as $item) {
    $sgtin = Gs1::Sgtin([7, 96, 1, $item]);
    if (!$sgtin->encode()->hasError()) {
        $codes[] = $sgtin->getEpcHexaDecimal();
    }
}
```

### 错误处理

```php
$sgtin = Gs1::Sgtin([7, 96, 1, ['CI' => '01234567890128', 'serial' => 'ABC']]);

if ($sgtin->encode()->hasError()) {
    echo "编码失败: " . $sgtin->getErrorMsg();
} else {
    echo "编码成功: " . $sgtin->getEpcHexaDecimal();
}
```

## 📚 更多信息

- **详细文档**: 查看 `docs/` 目录
- **完整示例**: 查看 `examples/` 目录
- **对比指南**: `docs/EPC_COMPARISON.md`
- **文档索引**: `docs/README.md`

## 🔗 快速链接

| 主题 | 文档 | 示例 |
|-----|------|------|
| SGTIN | [SGTIN_USAGE.md](SGTIN_USAGE.md) | [sgtin_example.php](../examples/sgtin_example.php) |
| GDTI | [GDTI_USAGE.md](GDTI_USAGE.md) | [gdti_example.php](../examples/gdti_example.php) |
| 对比 | [EPC_COMPARISON.md](EPC_COMPARISON.md) | - |

---

**提示**: 将此文件保存为书签，方便快速查阅！
