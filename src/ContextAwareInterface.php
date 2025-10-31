<?php

declare(strict_types=1);

namespace Tourze\BacktraceHelper;

interface ContextAwareInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getContext(): array;

    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void;
}
