<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper;

/**
 * 有时候，我们需要在异常抛出时设置一些上下文信息
 *
 * 此 trait 提供了一个便捷的构造函数，使用此 trait 的异常类可以：
 * 1. 直接使用 trait 提供的构造函数（如果异常类没有自定义构造函数）
 * 2. 在自己的构造函数中调用 parent::__construct() 然后调用 $this->setContext($context)
 *
 * @phpstan-ignore-next-line trait.unused (公共API trait，供外部使用)
 */
trait ContextAwareTrait
{
    /** @var array<string, mixed> */
    private array $context = [];

    /**
     * 构造函数，支持传入上下文信息
     *
     * 使用此 trait 的类如果没有自定义构造函数，可以直接使用这个构造函数。
     * 如果有自定义构造函数，应该调用 parent::__construct() 然后调用 $this->setContext($context)。
     *
     * @param array<string, mixed> $context 上下文信息
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->setContext($context);
    }

    /**
     * 设置上下文信息
     *
     * @param array<string, mixed> $context 上下文信息
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * 获取上下文信息
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
