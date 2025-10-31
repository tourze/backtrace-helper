<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper;

interface LogDataInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function generateLogData(): ?array;
}
