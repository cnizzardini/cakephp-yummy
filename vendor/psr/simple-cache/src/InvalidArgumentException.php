<?php

namespace Psr\SimpleCache;

/**
 * ConfigurationException interface for invalid cache arguments.
 *
 * When an invalid argument is passed it must throw an exception which implements
 * this interface
 */
interface InvalidArgumentException extends CacheException
{
}
