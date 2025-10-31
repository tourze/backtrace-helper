<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\ContextAwareInterface;

/**
 * @internal
 */
#[CoversClass(ContextAwareInterface::class)]
final class ContextAwareInterfaceTest extends TestCase
{
    /**
     * 测试接口方法定义
     */
    public function testInterfaceMethods(): void
    {
        $reflection = new \ReflectionClass(ContextAwareInterface::class);

        // 验证接口包含必要的方法
        $this->assertTrue($reflection->hasMethod('getContext'));
        $this->assertTrue($reflection->hasMethod('setContext'));

        // 验证方法的公开性
        $getContextMethod = $reflection->getMethod('getContext');
        $this->assertTrue($getContextMethod->isPublic());

        $setContextMethod = $reflection->getMethod('setContext');
        $this->assertTrue($setContextMethod->isPublic());
    }
}
