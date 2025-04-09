<?php

namespace Tourze\BacktraceHelper\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\Backtrace;

class BacktraceTest extends TestCase
{
    /**
     * 测试创建 Backtrace 实例
     */
    public function testCreate(): void
    {
        $backtrace = Backtrace::create();
        $this->assertInstanceOf(Backtrace::class, $backtrace);
    }

    /**
     * 测试格式化类名
     */
    public function testFormatClassName(): void
    {
        // 测试普通类名
        $className = 'App\Service\UserService';
        $this->assertSame($className, Backtrace::formatClassName($className));

        // 在非生产环境中测试
        $_ENV['APP_ENV'] = 'dev';
        $aopClassName = 'AopProxy\__PM__\App\Service\UserService\Generated27a0b72656b351f2c469b189f98d296a';
        $this->assertSame($aopClassName, Backtrace::formatClassName($aopClassName));

        // 在生产环境中测试
        $_ENV['APP_ENV'] = 'prod';
        $this->assertSame('App\Service\UserService', Backtrace::formatClassName($aopClassName));
    }

    /**
     * 测试设置和获取根目录
     */
    public function testRootDirectory(): void
    {
        $originalRoot = Backtrace::getRootDirectory();
        $this->assertSame('main', $originalRoot); // 默认值

        Backtrace::setRootDirectory('/var/www');
        $this->assertSame('/var/www', Backtrace::getRootDirectory());

        // 恢复原始值
        Backtrace::setRootDirectory('main');
    }

    /**
     * 测试文件名切割
     */
    public function testCutFileName(): void
    {
        // 在非生产环境中测试
        $_ENV['APP_ENV'] = 'dev';
        $fileName = '/var/www/app/Controller/UserController.php';
        $this->assertSame($fileName, Backtrace::cutFileName($fileName));

        // 在生产环境中测试
        $_ENV['APP_ENV'] = 'prod';
        Backtrace::setRootDirectory('/var/www');
        $this->assertSame('app/Controller/UserController.php', Backtrace::cutFileName($fileName));

        // 恢复原始值
        Backtrace::setRootDirectory('main');
    }

    /**
     * 测试是否应该忽略文件
     */
    public function testShouldIgnoreFile(): void
    {
        // 测试应该忽略的文件
        $this->assertTrue(Backtrace::shouldIgnoreFile('vendor/autoload.php'));

        // 测试不应该忽略的文件
        $this->assertFalse(Backtrace::shouldIgnoreFile('app/Controller/UserController.php'));

        // 测试显示所有文件的情况
        $_ENV['BACKTRACE_SHOW_ALL'] = true;
        $this->assertFalse(Backtrace::shouldIgnoreFile('vendor/autoload.php'));
        unset($_ENV['BACKTRACE_SHOW_ALL']);
    }

    /**
     * 测试 toString 方法
     */
    public function testToString(): void
    {
        $backtrace = Backtrace::create();
        $output = $backtrace->toString();

        // 至少应该包含当前测试文件
        $this->assertStringContainsString('BacktraceTest.php', $output);
    }
}
