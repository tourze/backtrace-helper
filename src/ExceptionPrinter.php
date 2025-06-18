<?php

namespace Tourze\BacktraceHelper;

/**
 * 打印异常，增加更加多上下文信息
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
            'string' => "'" . self::cutString((string) $value) . "'",
            default => (string) $value,
        };
    }

    /** Represents a method call as a string */
    public static function method(array $item): string
    {
        if (empty($item['function'])) {
            return '';
        }
        $method = $item['function'];
        if (!empty($item['class'])) {
            $type = (empty($item['type']) ? '->' : $item['type']);
            $class = Backtrace::formatClassName($item['class']) . $type;
        } else {
            $class = '';
        }
        $args = $item['args'] ?? [];
        foreach ($args as &$arg) {
            $arg = self::arg($arg);
        }
        unset($arg);

        return $class . $method . '(' . implode(', ', $args) . ')';
    }

    /** Represents a call point as a string */
    public static function point(array $item): string
    {
        if (empty($item['file'])) {
            $result = '[internal function]';
        } else {
            $result = Backtrace::cutFileName($item['file']);
            if (!empty($item['line'])) {
                $result .= "({$item['line']})";
            }
        }

        return $result;
    }

    /**
     * Represents a trace item as a string
     *
     * @param array $item
     *                    a backtrace item
     * @param ?int $number [optional]
     *                     a number of the item in the trace
     */
    public static function item(array $item, ?int $number = null): string
    {
        if (null !== $number) {
            $number = "#$number ";
        }

        return $number . self::point($item) . ': ' . self::method($item);
    }

    /**
     * Represents a trace as a string
     *
     * @param array $items a trace items list
     * @param string $sep a line separator
     */
    public static function trace(array $items, string $sep = PHP_EOL): string
    {
        $lines = [];
        $i = 0;
        foreach ($items as $number => $item) {
            if (isset($item['file']) && Backtrace::shouldIgnoreFile($item['file'])) {
                continue;
            }
            $lines[] = self::item($item, $number);
            $i++;
        }
        $lines[] = '#' . $i . ' {main}';

        return implode($sep, $lines) . $sep;
    }

    /**
     * Cuts a string by the max length
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
            $len = mb_strlen($str);
        }
        if ($len > self::MAX_LEN) {
            if ($mb) {
                return mb_substr($str, 0, self::MAX_LEN, 'UTF-8') . '...';
            }

            return mb_substr($str, 0, self::MAX_LEN) . '...';
        }

        return $str;
    }

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
        $next = false;

        foreach (array_reverse(array_merge([$throwable], static::getAllPrevious($throwable))) as $exception) {
            if ($next) {
                $message .= 'Next ';
            } else {
                $next = true;
            }
            $message .= get_class($exception);

            if ('' != $exception->getMessage()) {
                $message .= ': ' . $exception->getMessage();
            }

            $message .= ' in ' . $exception->getFile() . ':' . $exception->getLine() .
                "\nStack trace:\n" . static::trace($exception->getTrace()) . "\n\n";
        }

        return rtrim($message);
    }
}
