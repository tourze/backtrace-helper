# Backtrace Helper

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/backtrace-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/backtrace-helper)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/backtrace-helper.svg?style=flat-square)](https://packagist.org/packages/tourze/backtrace-helper)

A PHP package for enhanced backtrace handling and exception printing with useful context information.

## Features

- Enhanced backtrace information with cleaner output
- Intelligent filtering of irrelevant stack frames in production environments
- Exception printer with rich context information
- Support for context-aware exceptions
- Clean class name formatting (especially for AOP proxies)
- Easy integration with existing PHP applications

## Installation

```bash
composer require tourze/backtrace-helper
```

## Quick Start

```php
<?php

use Tourze\BacktraceHelper\Backtrace;
use Tourze\BacktraceHelper\ExceptionPrinter;

// Get a formatted backtrace
$backtrace = Backtrace::create();
echo $backtrace->toString();

// Print an exception with enhanced information
try {
    // Some code that might throw an exception
    throw new \Exception("Something went wrong");
} catch (\Throwable $e) {
    echo ExceptionPrinter::exception($e);
}

// Create context-aware exceptions
class MyException extends \Exception implements \Tourze\BacktraceHelper\ContextAwareInterface 
{
    use \Tourze\BacktraceHelper\ContextAwareTrait;
}

throw new MyException("Error with context", 0, null, ["user_id" => 123]);
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
