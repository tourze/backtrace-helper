<?php

namespace Tourze\BacktraceHelper\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\ExceptionPrinter;
use Tourze\BacktraceHelper\Tests\Fixtures\ContextAwareException;

class ExceptionPrinterTest extends TestCase
{
    /**
     * 测试参数格式化
     */
    public function testArg(): void
    {
        // 测试 null 值
        $this->assertSame('NULL', ExceptionPrinter::arg(null));

        // 测试布尔值
        $this->assertSame('true', ExceptionPrinter::arg(true));
        $this->assertSame('false', ExceptionPrinter::arg(false));

        // 测试数组
        $this->assertSame('Array', ExceptionPrinter::arg([]));

        // 测试对象
        $obj = new \stdClass();
        $this->assertSame('Object(stdClass)', ExceptionPrinter::arg($obj));

        // 测试字符串
        $this->assertSame("'test'", ExceptionPrinter::arg('test'));

        // 测试长字符串
        $longString = str_repeat('a', 50);
        $this->assertSame("'" . substr($longString, 0, 15) . "...'", ExceptionPrinter::arg($longString));

        // 测试数字
        $this->assertSame('123', ExceptionPrinter::arg(123));
    }

    /**
     * 测试方法调用格式化
     */
    public function testMethod(): void
    {
        // 测试无类的函数调用
        $item = [
            'function' => 'test',
            'args' => ['value1', 123],
        ];
        $this->assertSame("test('value1', 123)", ExceptionPrinter::method($item));

        // 测试带类的方法调用
        $item = [
            'function' => 'test',
            'class' => 'TestClass',
            'type' => '::',
            'args' => [true, null],
        ];
        $this->assertSame("TestClass::test(true, NULL)", ExceptionPrinter::method($item));

        // 测试对象方法调用
        $item = [
            'function' => 'test',
            'class' => 'TestClass',
            'type' => '->',
            'args' => [new \stdClass(), []],
        ];
        $this->assertSame("TestClass->test(Object(stdClass), Array)", ExceptionPrinter::method($item));

        // 测试无参数方法调用
        $item = [
            'function' => 'test',
            'class' => 'TestClass',
        ];
        $this->assertSame("TestClass->test()", ExceptionPrinter::method($item));
    }

    /**
     * 测试调用点格式化
     */
    public function testPoint(): void
    {
        // 测试内部函数
        $item = [];
        $this->assertSame('[internal function]', ExceptionPrinter::point($item));

        // 测试带文件的调用点
        $item = ['file' => 'test.php'];
        $this->assertSame('test.php', ExceptionPrinter::point($item));

        // 测试带文件和行号的调用点
        $item = ['file' => 'test.php', 'line' => 123];
        $this->assertSame('test.php(123)', ExceptionPrinter::point($item));
    }

    /**
     * 测试异常格式化
     */
    public function testException(): void
    {
        // 创建简单异常
        $exception = new \Exception('Test exception');
        $output = ExceptionPrinter::exception($exception);

        $this->assertStringContainsString('Exception: Test exception', $output);
        $this->assertStringContainsString('Stack trace:', $output);

        // 创建带上下文的异常
        $contextException = new ContextAwareException('Test context exception', 0, null, ['key' => 'value']);
        $output = ExceptionPrinter::exception($contextException);

        $this->assertStringContainsString('ContextAwareException: Test context exception', $output);

        // 创建嵌套异常
        $nestedExceptionParent = new \Exception('Parent exception');
        $nestedException = new \Exception('Child exception', 0, $nestedExceptionParent);
        $output = ExceptionPrinter::exception($nestedException);

        $this->assertStringContainsString('Exception: Parent exception', $output);
        $this->assertStringContainsString('Next Exception: Child exception', $output);
    }

    /**
     * 测试获取所有前置异常
     */
    public function testGetAllPrevious(): void
    {
        // 创建嵌套异常
        $exception1 = new \Exception('First');
        $exception2 = new \Exception('Second', 0, $exception1);
        $exception3 = new \Exception('Third', 0, $exception2);

        $previous = ExceptionPrinter::getAllPrevious($exception3);

        $this->assertCount(2, $previous);
        $this->assertSame('Second', $previous[0]->getMessage());
        $this->assertSame('First', $previous[1]->getMessage());
    }
}
