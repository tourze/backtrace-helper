<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\ContextAwareTrait;
use Tourze\BacktraceHelper\Tests\Fixtures\ContextAwareTestException;

/**
 * @internal
 */
#[CoversClass(ContextAwareTrait::class)]
final class ContextAwareTraitTest extends TestCase
{
    /**
     * 测试上下文感知异常的构造函数和基本功能
     */
    public function testContextAwareExceptionConstruction(): void
    {
        // 不带上下文创建异常
        $exception = new ContextAwareTestException('Test exception');
        $this->assertSame('Test exception', $exception->getMessage());
        $this->assertSame([], $exception->getContext());

        // 带上下文创建异常
        $context = ['user_id' => 123, 'action' => 'login'];
        $exception = new ContextAwareTestException('Test with context', 0, null, $context);
        $this->assertSame('Test with context', $exception->getMessage());
        $this->assertSame($context, $exception->getContext());

        // 设置新的上下文
        $newContext = ['key' => 'value'];
        $exception->setContext($newContext);
        $this->assertSame($newContext, $exception->getContext());
    }

    /**
     * 测试嵌套异常
     */
    public function testNestedExceptionWithContext(): void
    {
        // 创建带上下文的嵌套异常
        $parentException = new \Exception('Parent exception');
        $context = ['trace_id' => 'abc123'];
        $exception = new ContextAwareTestException('Child exception', 0, $parentException, $context);

        // 检查上下文
        $this->assertSame($context, $exception->getContext());

        // 检查前置异常
        $this->assertSame($parentException, $exception->getPrevious());
        $this->assertSame('Parent exception', $exception->getPrevious()->getMessage());
    }
}
