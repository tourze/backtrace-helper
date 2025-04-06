<?php

namespace Tourze\BacktraceHelper;

interface ContextAwareInterface
{
    public function getContext(): array;

    public function setContext(array $context): void;
}
