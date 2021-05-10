<?php

namespace Apex\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ContainerClassNotExistsException extends \RuntimeException implements NotFoundExceptionInterface
{

}

