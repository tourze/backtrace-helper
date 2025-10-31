<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\LogDataInterface;
use Tourze\BacktraceHelper\Tests\Fixtures\LogDataTestClass;
use Tourze\BacktraceHelper\Tests\Fixtures\NullLogDataTestClass;

/**
 * @internal
 */
#[CoversClass(LogDataInterface::class)]
final class LogDataInterfaceTest extends TestCase
{
    /**
     * 测试实现了 LogDataInterface 的类可以正确生成日志数据
     */
    public function testGenerateLogData(): void
    {
        // 创建测试对象并生成日志数据
        $logData = new LogDataTestClass();
        $data = $logData->generateLogData();

        // 验证数据格式
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('timestamp', $data);

        $this->assertEquals(123, $data['id']);
        $this->assertEquals('Test', $data['name']);
        $this->assertIsInt($data['timestamp']);
    }

    /**
     * 测试返回 null 的情况
     */
    public function testNullLogData(): void
    {
        $logData = new NullLogDataTestClass();
        $this->assertNull($logData->generateLogData());
    }
}
