<?php

namespace Tourze\BacktraceHelper;

class NameCleaner
{
    const AOP_PREFIX = 'AopProxy\__PM__\\';
    const DOCTRINE_PREFIX = 'Proxies\__CG__\\';

    public static function formatClassName(string $className): string
    {
        // 处理 AOP 代理类名
        if (str_starts_with($className, self::AOP_PREFIX)) {
            // 示例: AopProxy\__PM__\XXX\Service\YYY\Generated27a0b72656b351f2c469b189f98d296a
            $className = substr($className, strlen(self::AOP_PREFIX));

            // 检查是否有 Generated 后缀
            if (preg_match('/(.+)\\\\Generated[a-f0-9]+$/', $className, $matches)) {
                $className = $matches[1];
            }
        }

        // 处理 Doctrine 代理类名
        elseif (str_starts_with($className, self::DOCTRINE_PREFIX)) {
            // 示例: Proxies\__CG__\AppBundle\Entity\BizUser
            $className = substr($className, strlen(self::DOCTRINE_PREFIX));
        }

        return $className;
    }
}
