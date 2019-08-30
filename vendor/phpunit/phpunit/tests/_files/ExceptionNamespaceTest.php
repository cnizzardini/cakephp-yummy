<?php

namespace My\Space;

class ExceptionNamespaceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * ConfigurationException message
     *
     * @var string
     */
    const ERROR_MESSAGE = 'ConfigurationException namespace message';

    /**
     * ConfigurationException code
     *
     * @var int
     */
    const ERROR_CODE = 200;

    /**
     * @expectedException Class
     * @expectedExceptionMessage My\Space\ExceptionNamespaceTest::ERROR_MESSAGE
     * @expectedExceptionCode My\Space\ExceptionNamespaceTest::ERROR_CODE
     */
    public function testConstants()
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode My\Space\ExceptionNamespaceTest::UNKNOWN_CODE_CONSTANT
     * @expectedExceptionMessage My\Space\ExceptionNamespaceTest::UNKNOWN_MESSAGE_CONSTANT
     */
    public function testUnknownConstants()
    {
    }
}
