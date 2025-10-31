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

A PHP package for enhanced backtrace handling and exception printing with useful context information, 
built on top of Spatie's backtrace library.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
  - [Requirements](#requirements)
- [Quick Start](#quick-start)
  - [Basic Backtrace](#basic-backtrace)
  - [Exception Printing](#exception-printing)
  - [Context-Aware Exceptions](#context-aware-exceptions)
  - [Clean Proxy Class Names](#clean-proxy-class-names)
- [Advanced Usage](#advanced-usage)
  - [Custom Log Data](#custom-log-data)
  - [Production Environment Optimization](#production-environment-optimization)
  - [Integration with Logging](#integration-with-logging)
- [Testing](#testing)
- [Contributing](#contributing)
  - [Development Standards](#development-standards)
- [License](#license)

## Features

- **Enhanced Stack Traces**: Clean and readable stack trace formatting with intelligent frame 
  filtering
- **Production-Ready**: Automatic filtering of irrelevant stack frames in production environments 
  (autoload, vendor internals)
- **Exception Printing**: Rich exception formatting with method arguments, file locations, and 
  context
- **Context-Aware Exceptions**: Support for exceptions with contextual data through interfaces 
  and traits
- **Proxy Class Handling**: Clean formatting for AOP and Doctrine proxy class names
- **Zero Configuration**: Works out of the box with sensible defaults
- **PHP 8.1+ Support**: Modern PHP features and type safety

## Installation

```bash
composer require tourze/backtrace-helper
```

### Requirements

- PHP 8.1 or higher
- ext-mbstring
- spatie/backtrace ^1.7.1
- symfony/event-dispatcher ^6.4
- symfony/http-kernel ^6.4

## Quick Start

### Basic Backtrace

```php
<?php

use Tourze\BacktraceHelper\Backtrace;

// Create a backtrace at the current point
$backtrace = Backtrace::create();

// Get as string
echo $backtrace->toString();

// Get as array
$frames = $backtrace->frames();
```

### Exception Printing

```php
<?php

use Tourze\BacktraceHelper\ExceptionPrinter;

try {
    // Some code that might throw an exception
    throw new \RuntimeException("Database connection failed");
} catch (\Throwable $e) {
    // Get a formatted exception string
    echo ExceptionPrinter::exception($e);
    
    // Or print directly to output
    ExceptionPrinter::print($e);
}
```

### Context-Aware Exceptions

```php
<?php

use Tourze\BacktraceHelper\ContextAwareInterface;
use Tourze\BacktraceHelper\ContextAwareTrait;

// Create your own context-aware exception
class BusinessException extends \Exception implements ContextAwareInterface 
{
    use ContextAwareTrait;
}

// Throw with context
throw new BusinessException(
    "User not found", 
    404, 
    null, 
    [
        "user_id" => 123,
        "action" => "profile_view",
        "timestamp" => time()
    ]
);
```

### Clean Proxy Class Names

```php
<?php

use Tourze\BacktraceHelper\NameCleaner;

// Clean AOP proxy class names
$clean = NameCleaner::formatClassName('AopProxy\__PM__\App\Service\UserService\Generated27a0b72656b351f2c469b189f98d296a');
// Returns: App\Service\UserService

// Clean Doctrine proxy class names
$clean = NameCleaner::formatClassName('Proxies\__CG__\AppBundle\Entity\User');
// Returns: AppBundle\Entity\User
```

## Advanced Usage

### Custom Log Data

Implement the `LogDataInterface` to provide custom log data for your objects:

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

### Production Environment Optimization

The library automatically detects and filters unnecessary frames in production:

```php
// These frames are automatically filtered:
// - Composer autoload internals
// - Symfony EventDispatcher internals
// - Symfony HttpKernel internals
// - Spatie Backtrace internals
```

### Integration with Logging

```php
<?php

use Tourze\BacktraceHelper\ExceptionPrinter;
use Monolog\Logger;

$logger = new Logger('app');

try {
    // Your application code
} catch (\Throwable $e) {
    $logger->error('Application error', [
        'exception' => ExceptionPrinter::exception($e),
        'trace' => Backtrace::create()->toString()
    ]);
}
```

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit packages/backtrace-helper/tests
```

Run static analysis:

```bash
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/backtrace-helper
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -am 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Standards

- Follow PSR-12 coding standards
- Write unit tests for new features
- Ensure all tests pass before submitting PR
- Keep documentation up to date

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
