# Backtrace Helper Tests

本目录包含 `tourze/backtrace-helper` 包的单元测试。

## 运行测试

确保已安装依赖：

```bash
composer install
```

运行所有测试：

```bash
vendor/bin/phpunit
```

运行特定测试类：

```bash
vendor/bin/phpunit tests/BacktraceTest.php
```

运行特定测试方法：

```bash
vendor/bin/phpunit --filter testFormatClassName
```

## 测试覆盖率报告

生成 HTML 覆盖率报告：

```bash
vendor/bin/phpunit --coverage-html coverage
```

然后打开 `coverage/index.html` 查看覆盖率报告。

## 测试说明

测试文件与源代码文件对应：

- `BacktraceTest.php` - 测试调用栈捕获和格式化功能
- `ExceptionPrinterTest.php` - 测试异常信息打印功能
- `NameCleanerTest.php` - 测试类名清理功能
- `ContextAwareTraitTest.php` - 测试上下文感知异常功能
- `LogDataInterfaceTest.php` - 测试日志数据接口功能

## 注意事项

- 测试使用的环境变量在 `bootstrap.php` 中设置
- 测试环境变量可以在 `phpunit.xml` 文件中配置
