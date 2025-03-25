<?php

namespace Tourze\BacktraceHelper;

class NameCleaner
{
    const PREFIX = 'AopProxy\__PM__\\';

    public static function formatClassName(string $className): string
    {
        if (str_starts_with($className, self::PREFIX)) {
            // demo: AopProxy\__PM__\XXX\Service\YYY\Generated27a0b72656b351f2c469b189f98d296a
            $className = substr($className, strlen(self::PREFIX));

            $pos = strripos($className, "\\");
            if ($pos !== false) {
                $className = substr($className, 0, $pos);
            }
        }
        return $className;
    }
}
