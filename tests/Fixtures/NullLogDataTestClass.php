<?php

namespace Tourze\BacktraceHelper\Tests\Fixtures;

use Tourze\BacktraceHelper\LogDataInterface;

/**
 * 用于测试返回 null 日志数据的测试类
 */
class NullLogDataTestClass implements LogDataInterface
{
    public function generateLogData(): ?array
    {
        return null;
    }
}