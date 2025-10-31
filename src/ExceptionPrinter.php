<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper;

/**
 * 异常打印工具，提供格式化异常信息和上下文数据的功能
 *
 * 该类能够将异常及其调用栈格式化为可读的字符串，支持显示上下文信息，
 * 适用于日志记录和调试输出。
 */
class ExceptionPrinter
{
    /** The maximum length of a method argument */
    private const MAX_LEN = 15;

    /** Represents an argument of a method as a string */
    public static function arg(mixed $value): string
    {
        return match (gettype($value)) {
            'NULL' => 'NULL',
            'boolean' => $value ? 'true' : 'false',
            'array' => 'Array',
            'object' => 'Object(' . Backtrace::formatClassName(get_class($value)) . ')',
            'string' => "'" . self::cutString($value) . "'",
            default => is_scalar($value) ? (string) $value : gettype($value),
        };
    }

    /**
     * 将方法调用表示为字符串
     * @param array<string, mixed> $item
     */
    public static function method(array $item): string
    {
        if (!isset($item['function']) || !is_string($item['function']) || '' === $item['function']) {
            return '';
        }
        $method = $item['function'];
        if (isset($item['class']) && is_string($item['class']) && '' !== $item['class']) {
            $type = (!isset($item['type']) || !is_string($item['type']) || '' === $item['type'] ? '->' : $item['type']);
            $class = Backtrace::formatClassName($item['class']) . $type;
        } else {
            $class = '';
        }
        $args = $item['args'] ?? [];
        if (!is_array($args)) {
            $args = [];
        }
        $args = array_map(static fn ($arg) => self::arg($arg), $args);

        return $class . $method . '(' . implode(', ', $args) . ')';
    }

    /**
     * 将调用点表示为字符串
     * @param array<string, mixed> $item
     */
    public static function point(array $item): string
    {
        if (!isset($item['file']) || !is_string($item['file']) || '' === $item['file']) {
            $result = '[internal function]';
        } else {
            $result = Backtrace::cutFileName($item['file']);
            if (isset($item['line']) && is_numeric($item['line']) && 0 !== $item['line']) {
                $result .= '(' . (string) $item['line'] . ')';
            }
        }

        return $result;
    }

    /**
     * 将追踪项表示为字符串
     *
     * @param array<string, mixed> $item 一个回溯项
     * @param ?int  $number 项在追踪中的编号
     */
    public static function item(array $item, ?int $number = null): string
    {
        if (null !== $number) {
            $number = "#{$number} ";
        }

        return $number . self::point($item) . ': ' . self::method($item);
    }

    /**
     * 将追踪表示为字符串
     *
     * @param array<int, array<string, mixed>> $items 追踪项列表
     * @param string $sep 行分隔符
     */
    public static function trace(array $items, string $sep = PHP_EOL): string
    {
        $lines = [];
        $i = 0;
        foreach ($items as $number => $item) {
            if (isset($item['file']) && is_string($item['file']) && Backtrace::shouldIgnoreFile($item['file'])) {
                continue;
            }
            $lines[] = self::item($item, $number);
            ++$i;
        }
        $lines[] = '#' . $i . ' {main}';

        return implode($sep, $lines) . $sep;
    }

    /**
     * 按最大长度截断字符串
     */
    private static function cutString(string $str): string
    {
        static $mb;
        if (null === $mb) {
            $mb = function_exists('mb_strlen');
        }
        if ($mb) {
            $len = mb_strlen($str, 'UTF-8');
        } else {
            $len = strlen($str);
        }
        if ($len > self::MAX_LEN) {
            if ($mb) {
                return mb_substr($str, 0, self::MAX_LEN, 'UTF-8') . '...';
            }

            return substr($str, 0, self::MAX_LEN) . '...';
        }

        return $str;
    }

    /**
     * @return array<\Throwable>
     */
    public static function getAllPrevious(\Throwable $exception): array
    {
        $exceptions = [];
        while (($previous = $exception->getPrevious()) !== null) {
            $exception = $previous;
            $exceptions[] = $exception;
        }

        return $exceptions;
    }

    public static function exception(\Throwable $throwable): string
    {
        $message = '';
        $exceptions = array_reverse(array_merge([$throwable], static::getAllPrevious($throwable)));

        foreach ($exceptions as $index => $exception) {
            $message .= self::formatSingleException($exception, $index > 0);
        }

        return rtrim($message);
    }

    private static function formatSingleException(\Throwable $exception, bool $isNext): string
    {
        $message = '';

        if ($isNext) {
            $message .= 'Next ';
        }

        $message .= self::formatExceptionBasicInfo($exception);
        $message .= self::formatExceptionContext($exception);
        $message .= "\nStack trace:\n" . static::trace($exception->getTrace()) . "\n\n";

        return $message;
    }

    private static function formatExceptionBasicInfo(\Throwable $exception): string
    {
        $message = get_class($exception);

        if ('' !== $exception->getMessage()) {
            $message .= ': ' . $exception->getMessage();
        }

        return $message . ' in ' . $exception->getFile() . ':' . $exception->getLine();
    }

    private static function formatExceptionContext(\Throwable $exception): string
    {
        if (!$exception instanceof ContextAwareInterface) {
            return '';
        }

        $context = $exception->getContext();
        if ([] === $context) {
            return '';
        }

        // 使用 JSON 替代 var_export，自动处理循环引用
        // JSON_PARTIAL_OUTPUT_ON_ERROR 会在遇到不可序列化的对象时输出 null
        $serialized = json_encode($context, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);

        if (false === $serialized) {
            return "\nContext:\n[Unable to serialize context]";
        }

        return "\nContext:\n" . $serialized;
    }

    public static function print(\Throwable $throwable): void
    {
        echo static::exception($throwable);
    }
}
