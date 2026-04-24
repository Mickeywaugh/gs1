# 变更日志

本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

## [1.1.0] - 2024-01-XX

### ✨ 新增功能
- 添加完整的类型声明和PHPDoc文档
- EpcBase基类新增方法：
  - `hasError()` - 检查是否有错误
  - `getErrorCode()` - 获取错误代码
  - `__toString()` - 转换为JSON字符串
- EpcMesg新增工具方法：
  - `getAllErrorCodes()` - 获取所有错误代码
  - `isValidErrorCode()` - 验证错误代码有效性
- getOutput()方法增加更多输出字段（itemReference、serial、CI、error）

### 🔧 改进优化
- **API设计**：Gs1工厂方法改用关联数组传参，提高可读性
  ```php
  // 旧版
  Gs1::Sgtin($companyPrefixLength, $tagSize, $filterValue, $schemeParameters);
  
  // 新版
  Gs1::Sgtin([
      'companyPrefixLength' => 7,
      'tagSize' => 96,
      'filterValue' => 1,
      'schemeParameters' => ['CI' => '...', 'serial' => '...']
  ]);
  ```
- **性能优化**：EpcSpec::getCompanyPrefixLength添加静态缓存，避免重复XML解析
- **错误处理**：改进错误消息模板，提供更详细的错误信息
- **数据验证**：
  - Sgtin添加GTIN校验位自动验证
  - Gdti添加CI格式验证（13位数字）
- **命名规范**：统一方法命名风格（如`getEpcUri`替代`GETURI`）
- **代码重构**：提取公共逻辑到基类，减少重复代码

### 🐛 Bug修复
- 修复方法名拼写错误：`getCompanyPrefixLenth` → `getCompanyPrefixLength`
- 修正二进制填充方向参数使用常量（STR_PAD_LEFT/RIGHT）
- 改进decode方法的错误处理和边界条件检查
- 修复序列号为"0"时的判断逻辑

### 📚 文档改进
- 完全重写README.md，添加：
  - 项目徽章（PHP版本、License、Composer）
  - 详细的安装和使用说明
  - 完整的编码/解码示例
  - 参数说明表格
  - 架构设计图
  - 中文说明
- 更新示例文件（sgtin_example.php、gdti_example.php）
- 为所有类和方法添加详细的PHPDoc注释
- 添加代码示例到文档注释中

### 📦 依赖更新
- composer.json添加开发依赖：phpunit/phpunit ^10.0
- 添加必需的PHP扩展：ext-simplexml、ext-mbstring
- 优化composer配置（sort-packages、optimize-autoloader）

### 🔄 向后兼容性
- 保留`setHexaDecimal()`等别名方法确保向后兼容
- 旧的调用方式仍然可用（但推荐使用新API）

---

## [1.0.0] - 2024-01-01

### 初始版本
- ✅ 支持SGTIN编码方案（96位、198位）
- ✅ 支持GDTI编码方案（96位、113位）
- ✅ 完整的编码和解码功能
- ✅ 生成标准URI格式（Pure Identity URI、Tag URI、Raw URI）
- ✅ 二进制和十六进制输出
- ✅ 基于GS1 EPC Tag Data Standard Release 1.13
- ✅ PSR-4自动加载
- ✅ MIT开源许可
