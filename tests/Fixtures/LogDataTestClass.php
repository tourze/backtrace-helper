<?php

namespace Tourze\BacktraceHelper\Tests\Fixtures;

use Tourze\BacktraceHelper\LogDataInterface;

/**
 * 用于测试的实现 LogDataInterface 的测试类
 */
class LogDataTestClass implements LogDataInterface
{
    public function generateLogData(): ?array
    {
        return [
            'id' => 123,
            'name' => 'Test',
            'timestamp' => time(),
        ];
    }
}
