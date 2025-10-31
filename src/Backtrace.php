<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper;

use Spatie\Backtrace\Backtrace as BaseBacktrace;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * 调用栈跟踪工具，提供过滤和格式化功能
 *
 * 该类继承自 Spatie\Backtrace\Backtrace，增加了文件过滤、环境适配和格式化功能。
 * 在生产环境中会自动清理代理类名并隐藏敏感路径，在开发环境中保留完整信息。
 *
 * 主要特性：
 * - 自动过滤框架和自动加载相关的调用栈
 * - 支持生产环境的路径隐藏
 * - 清理 AOP 和 Doctrine 代理类名
 * - 可配置的忽略文件列表
 */
class Backtrace extends BaseBacktrace implements \Stringable
{
    private static bool $init = false;

    public static function create(): self
    {
        if (!self::$init) {
            self::initIgnoreFiles();
            self::$init = true;
        }

        return new Backtrace();
    }

    private static function initIgnoreFiles(): void
    {
        $classes = [
            self::class,
            EventDispatcher::class,
            HttpKernel::class,
            BaseBacktrace::class,
        ];

        foreach ($classes as $class) {
            self::addClassFileToIgnoreList($class);
        }
    }

    private static function addClassFileToIgnoreList(string $class): void
    {
        if (!class_exists($class)) {
            return;
        }

        $fileName = (new \ReflectionClass($class))->getFileName();
        if (false !== $fileName) {
            self::addProdIgnoreFiles($fileName);
        }
    }

    /** @var array<string> */
    protected static array $ignoreFiles = [
        // 当前工具类的调用我们不关注
        __FILE__,

        // call_user_func 之类的内部调用
        'unknown(0)',

        // 自动加载这个一般不关心
        'vendor/autoload_runtime.php',
        'vendor/autoload.php',
    ];

    /**
     * 生产环境，忽略更多无用数据
     */
    /** @var array<string> */
    protected static array $prodIgnoreFiles = [
        // 容器内部的调用，不处理
        'var/cache/prod/Container',
        'vendor/symfony/dependency-injection/',

        // AOP部分不关心
        'var/cache/prod/AopProxy__PM__',

        // Runtime中的一般也不会关心
        'vendor/symfony/runtime/Runner/Symfony',

        // 队列相关
        'bin/console',
        'vendor/symfony/messenger',
        'vendor/symfony/doctrine-bridge/Messenger',
    ];

    private static string $rootDirectory = 'main';

    public static function addProdIgnoreFiles(string $file): void
    {
        static::$prodIgnoreFiles[] = $file;
        static::$prodIgnoreFiles = array_unique(static::$prodIgnoreFiles);
    }

    public static function setRootDirectory(string $rootDirectory): void
    {
        self::$rootDirectory = $rootDirectory;
    }

    public static function getRootDirectory(): string
    {
        return self::$rootDirectory;
    }

    public static function cutFileName(string $fileName): string
    {
        if (($_ENV['APP_ENV'] ?? 'dev') === 'prod') {
            // 正式环境，我们把路径隐藏起来，也可以减少空间
            if (str_starts_with($fileName, static::getRootDirectory())) {
                $fileName = substr($fileName, strlen(static::getRootDirectory()));
                $fileName = ltrim($fileName, '/');
            }
        }

        return $fileName;
    }

    /**
     * 是否要忽略这个文件
     */
    public static function shouldIgnoreFile(string $file): bool
    {
        if (self::shouldShowAllFiles()) {
            return false;
        }

        if (self::isIgnoredFile($file, static::$ignoreFiles)) {
            return true;
        }

        if (self::isProductionEnvironment()) {
            return self::isIgnoredFile($file, static::$prodIgnoreFiles);
        }

        return false;
    }

    private static function shouldShowAllFiles(): bool
    {
        $showAll = $_ENV['BACKTRACE_SHOW_ALL'] ?? false;

        return true === $showAll || '1' === $showAll;
    }

    /**
     * @param array<string> $ignoreFiles
     */
    private static function isIgnoredFile(string $file, array $ignoreFiles): bool
    {
        foreach ($ignoreFiles as $ignoreFile) {
            if (str_contains($file, $ignoreFile)) {
                return true;
            }
        }

        return false;
    }

    private static function isProductionEnvironment(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'dev') === 'prod';
    }

    public function toString(): string
    {
        $lines = [];
        foreach ($this->frames() as $frame) {
            if ('' === $frame->file) {
                continue;
            }

            if (static::shouldIgnoreFile($frame->file)) {
                continue;
            }

            $method = (isset($frame->class) && '' !== $frame->class)
                ? static::formatClassName($frame->class) . "->{$frame->method}"
                : $frame->method;
            $fileName = static::cutFileName($frame->file);
            $lines[] = "{$fileName}({$frame->lineNumber}): {$method}";
        }

        return implode("\n", $lines);
    }

    public static function formatClassName(string $name): string
    {
        if (($_ENV['APP_ENV'] ?? 'dev') === 'prod') {
            // 去除类名中的AOP前缀，减少日志混淆
            $name = NameCleaner::formatClassName($name);
        }

        return $name;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
