<?php

namespace Tourze\BacktraceHelper\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\NameCleaner;

class NameCleanerTest extends TestCase
{
    /**
     * 测试格式化 AOP 代理类名
     */
    public function testFormatClassName(): void
    {
        // 测试普通类名（不包含任何代理前缀）
        $className = 'App\Service\UserService';
        $this->assertSame($className, NameCleaner::formatClassName($className));

        // 测试包含 AOP 前缀的类名
        $aopClassName = 'AopProxy\__PM__\App\Service\UserService\Generated27a0b72656b351f2c469b189f98d296a';
        $this->assertSame('App\Service\UserService', NameCleaner::formatClassName($aopClassName));

        // 测试包含 AOP 前缀但没有 Generated 后缀的类名
        $aopClassNameWithoutHash = 'AopProxy\__PM__\App\Service\UserService';
        $this->assertSame('App\Service\UserService', NameCleaner::formatClassName($aopClassNameWithoutHash));

        // 测试 Doctrine 代理类名
        $doctrineProxyName = 'Proxies\__CG__\AppBundle\Entity\BizUser';
        $this->assertSame('AppBundle\Entity\BizUser', NameCleaner::formatClassName($doctrineProxyName));
    }
}
