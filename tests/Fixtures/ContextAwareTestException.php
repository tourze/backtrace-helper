<?php

namespace Tourze\BacktraceHelper\Tests\Fixtures;

use Tourze\BacktraceHelper\ContextAwareInterface;
use Tourze\BacktraceHelper\ContextAwareTrait;

/**
 * 用于测试的上下文感知异常类
 */
class ContextAwareTestException extends \Exception implements ContextAwareInterface
{
    use ContextAwareTrait;
}
