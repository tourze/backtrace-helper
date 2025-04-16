<?php

namespace Tourze\BacktraceHelper;

use Spatie\Backtrace\Backtrace as BaseBacktrace;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * 记录指定时间点的调用日志栈
 */
class Backtrace extends BaseBacktrace implements \Stringable
{
    private static bool $init = false;

    public static function create(): self
    {
        if (!self::$init) {
            if (class_exists(EventDispatcher::class)) {
                self::addProdIgnoreFiles((new \ReflectionClass(EventDispatcher::class))->getFileName());
            }
            if (class_exists(HttpKernel::class)) {
                self::addProdIgnoreFiles((new \ReflectionClass(HttpKernel::class))->getFileName());
            }
            if (class_exists(BaseBacktrace::class)) {
                self::addProdIgnoreFiles((new \ReflectionClass(BaseBacktrace::class))->getFileName());
            }
            self::$init = true;
        }

        return new Backtrace();
    }

    private static array $ignoreFiles = [
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
    private static array $prodIgnoreFiles = [
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
        // 有些环境，我们不需要忽略
        if ($_ENV['BACKTRACE_SHOW_ALL'] ?? false) {
            return false;
        }
        foreach (static::$ignoreFiles as $ignoreFile) {
            if (str_contains($file, $ignoreFile)) {
                return true;
            }
        }
        if ($_ENV['APP_ENV'] ?? 'dev' === 'prod') {
            foreach (static::$prodIgnoreFiles as $ignoreFile) {
                if (str_contains($file, $ignoreFile)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function toString(): string
    {
        $lines = [];
        foreach ($this->frames() as $frame) {
            if (empty($frame->file)) {
                continue;
            }

            if (static::shouldIgnoreFile($frame->file)) {
                continue;
            }

            $method = $frame->class
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
