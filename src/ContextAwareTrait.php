<?php

namespace Tourze\BacktraceHelper;

/**
 * 有时候，我们需要在异常抛出时设置一些上下文信息
 */
trait ContextAwareTrait
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->setContext($context);
    }

    private array $context = [];

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
