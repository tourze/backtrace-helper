<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper;

/**
 * 类名清理工具，用于移除代理类的前缀
 *
 * 在生产环境中，AOP和Doctrine等框架会生成代理类，这些类名包含前缀，
 * 该工具用于清理这些前缀，使日志更易读。
 */
class NameCleaner
{
    /**
     * AOP代理类前缀
     */
    public const AOP_PREFIX = 'AopProxy\__PM__\\';

    /**
     * Doctrine代理类前缀
     */
    public const DOCTRINE_PREFIX = 'Proxies\__CG__\\';

    public static function formatClassName(string $className): string
    {
        // 处理 AOP 代理类名
        if (str_starts_with($className, self::AOP_PREFIX)) {
            // 示例: AopProxy\__PM__\XXX\Service\YYY\Generated27a0b72656b351f2c469b189f98d296a
            $className = substr($className, strlen(self::AOP_PREFIX));

            // 检查是否有 Generated 后缀
            if (1 === preg_match('/(.+)\\\Generated[a-f0-9]+$/', $className, $matches)) {
                $className = $matches[1];
            }
        }

        // 处理 Doctrine 代理类名
        elseif (str_starts_with($className, self::DOCTRINE_PREFIX)) {
            // 示例: Proxies\__CG__\AppBundle\Entity\User
            $className = substr($className, strlen(self::DOCTRINE_PREFIX));
        }

        return $className;
    }
}
