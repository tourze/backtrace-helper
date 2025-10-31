# Backtrace Helper

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/backtrace-helper.svg?style=flat-square)]
(https://packagist.org/packages/tourze/backtrace-helper)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/backtrace-helper.svg?style=flat-square)]
(https://packagist.org/packages/tourze/backtrace-helper)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/backtrace-helper.svg?style=flat-square)]
(https://packagist.org/packages/tourze/backtrace-helper)
[![License](https://img.shields.io/packagist/l/tourze/backtrace-helper.svg?style=flat-square)]
(https://packagist.org/packages/tourze/backtrace-helper)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/backtrace-helper.svg?style=flat-square)]
(https://codecov.io/gh/tourze/backtrace-helper)

一个用于增强 PHP 堆栈跟踪处理和异常打印的包，提供有用的上下文信息，基于 Spatie 的 backtrace 库构建。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
  - [系统要求](#系统要求)
- [快速开始](#快速开始)
  - [基本堆栈跟踪](#基本堆栈跟踪)
  - [异常打印](#异常打印)
  - [上下文感知异常](#上下文感知异常)
  - [清理代理类名](#清理代理类名)
- [高级用法](#高级用法)
  - [自定义日志数据](#自定义日志数据)
  - [生产环境优化](#生产环境优化)
  - [与日志系统集成](#与日志系统集成)
- [测试](#测试)
- [贡献](#贡献)
  - [开发规范](#开发规范)
- [许可证](#许可证)

## 功能特性

- **增强的堆栈跟踪**：清晰可读的堆栈跟踪格式化，智能过滤无关帧
- **生产环境优化**：自动过滤生产环境中的无关堆栈帧（自动加载、vendor 内部调用）
- **异常打印**：丰富的异常格式化，包含方法参数、文件位置和上下文
- **上下文感知异常**：通过接口和 trait 支持带有上下文数据的异常
- **代理类处理**：清晰格式化 AOP 和 Doctrine 代理类名
- **零配置**：开箱即用，具有合理的默认设置
- **PHP 8.1+ 支持**：现代 PHP 特性和类型安全

## 安装

```bash
composer require tourze/backtrace-helper
```

### 系统要求

- PHP 8.1 或更高版本
- ext-mbstring
- spatie/backtrace ^1.7.1
- symfony/event-dispatcher ^6.4
- symfony/http-kernel ^6.4

## 快速开始

### 基本堆栈跟踪

```php
<?php

use Tourze\BacktraceHelper\Backtrace;

// 在当前位置创建堆栈跟踪
$backtrace = Backtrace::create();

// 获取字符串形式
echo $backtrace->toString();

// 获取数组形式
$frames = $backtrace->frames();
```

### 异常打印

```php
<?php

use Tourze\BacktraceHelper\ExceptionPrinter;

try {
    // 可能抛出异常的代码
    throw new \RuntimeException("数据库连接失败");
} catch (\Throwable $e) {
    // 获取格式化的异常字符串
    echo ExceptionPrinter::exception($e);
    
    // 或直接打印到输出
    ExceptionPrinter::print($e);
}
```

### 上下文感知异常

```php
<?php

use Tourze\BacktraceHelper\ContextAwareInterface;
use Tourze\BacktraceHelper\ContextAwareTrait;

// 创建自己的上下文感知异常
class BusinessException extends \Exception implements ContextAwareInterface 
{
    use ContextAwareTrait;
}

// 抛出带上下文的异常
throw new BusinessException(
    "用户未找到", 
    404, 
    null, 
    [
        "user_id" => 123,
        "action" => "profile_view",
        "timestamp" => time()
    ]
);
```

### 清理代理类名

```php
<?php

use Tourze\BacktraceHelper\NameCleaner;

// 清理 AOP 代理类名
$clean = NameCleaner::formatClassName('AopProxy\__PM__\App\Service\UserService\Generated27a0b72656b351f2c469b189f98d296a');
// 返回: App\Service\UserService

// 清理 Doctrine 代理类名
$clean = NameCleaner::formatClassName('Proxies\__CG__\AppBundle\Entity\User');
// 返回: AppBundle\Entity\User
```

## 高级用法

### 自定义日志数据

实现 `LogDataInterface` 为你的对象提供自定义日志数据：

```php
<?php

use Tourze\BacktraceHelper\LogDataInterface;

class User implements LogDataInterface
{
    private int $id;
    private string $email;
    
    public function toLogData(): ?array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'type' => 'user'
        ];
    }
}
```

### 生产环境优化

该库自动检测并过滤生产环境中不必要的帧：

```php
// 自动过滤这些帧：
// - Composer 自动加载内部调用
// - Symfony EventDispatcher 内部调用
// - Symfony HttpKernel 内部调用
// - Spatie Backtrace 内部调用
```

### 与日志系统集成

```php
<?php

use Tourze\BacktraceHelper\ExceptionPrinter;
use Monolog\Logger;

$logger = new Logger('app');

try {
    // 你的应用程序代码
} catch (\Throwable $e) {
    $logger->error('应用程序错误', [
        'exception' => ExceptionPrinter::exception($e),
        'trace' => Backtrace::create()->toString()
    ]);
}
```

## 测试

运行测试套件：

```bash
./vendor/bin/phpunit packages/backtrace-helper/tests
```

运行静态分析：

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/backtrace-helper
```

## 贡献

欢迎贡献！请随时提交 Pull Request。

1. Fork 仓库
2. 创建你的功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交你的更改 (`git commit -am 'Add some amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 创建一个 Pull Request

### 开发规范

- 遵循 PSR-12 编码标准
- 为新功能编写单元测试
- 确保所有测试在提交 PR 前通过
- 保持文档更新

## 许可证

MIT 许可证（MIT）。请查看[许可证文件](LICENSE)获取更多信息。
