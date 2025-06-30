<?php

namespace Tourze\BacktraceHelper\Tests\Fixtures;

use Tourze\BacktraceHelper\ContextAwareInterface;
use Tourze\BacktraceHelper\ContextAwareTrait;

/**
 * 测试用的上下文感知异常类
 */
class ContextAwareException extends \Exception implements ContextAwareInterface
{
    use ContextAwareTrait;
}