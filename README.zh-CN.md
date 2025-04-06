# Backtrace Helper

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/backtrace-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/backtrace-helper)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/backtrace-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/backtrace-helper)

一个用于增强PHP堆栈跟踪处理和异常打印的包，提供有用的上下文信息。

## 功能特性

- 增强的堆栈跟踪信息，提供更清晰的输出
- 在生产环境中智能过滤无关的堆栈帧
- 带有丰富上下文信息的异常打印工具
- 支持上下文感知的异常
- 清晰的类名格式化（尤其是对AOP代理类）
- 易于与现有PHP应用程序集成

## 安装

```bash
composer require tourze/backtrace-helper
```

## 快速开始

```php
<?php

use Tourze\BacktraceHelper\Backtrace;
use Tourze\BacktraceHelper\ExceptionPrinter;

// 获取格式化的堆栈跟踪
$backtrace = Backtrace::create();
echo $backtrace->toString();

// 打印带有增强信息的异常
try {
    // 可能抛出异常的代码
    throw new \Exception("出现错误");
} catch (\Throwable $e) {
    echo ExceptionPrinter::exception($e);
}

// 创建上下文感知的异常
class MyException extends \Exception implements \Tourze\BacktraceHelper\ContextAwareInterface 
{
    use \Tourze\BacktraceHelper\ContextAwareTrait;
}

throw new MyException("带有上下文的错误", 0, null, ["user_id" => 123]);
```

## 贡献

欢迎贡献！请随时提交Pull Request。

## 许可证

MIT许可证。请查看[许可证文件](LICENSE)获取更多信息。
