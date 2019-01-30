<?php

/**
 * PHP Service Bus common component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Common\MessageHandler;

/**
 *
 * @property-read string      $argumentName
 * @property-read bool        $hasType
 * @property-read string|null $argumentTypeClass
 * @property-read bool        $isObject
 */
final class MessageHandlerArgument
{
    /**
     * Argument name
     *
     * @var string
     */
    public $argumentName;

    /**
     * Does the argument have a type?
     *
     * @var bool
     */
    public $hasType;

    /**
     * If the argument type is an object, then the name of the class. Otherwise null
     *
     * @var string|null
     */
    public $typeClass;

    /**
     * Is the argument an object?
     *
     * @var bool
     */
    public $isObject;

    /**
     * @var \ReflectionParameter
     */
    private $reflectionParameter;

    /**
     * @param \ReflectionParameter $reflectionParameter
     *
     * @return self
     */
    public static function create(\ReflectionParameter $reflectionParameter): self
    {
        return new self($reflectionParameter);
    }

    /**
     * Checks if the class is of this class or has this class as one of its parents
     *
     * @param string $expectedClass
     *
     * @return bool
     */
    public function isA(string $expectedClass): bool
    {
        if(true === $this->isObject)
        {
            /** @var \ReflectionClass $reflectionClass */
            $reflectionClass = $this->reflectionParameter->getClass();

            return \is_a($reflectionClass->getName(), $expectedClass, true);
        }

        return false;
    }

    /**
     * @param \ReflectionParameter $reflectionParameter
     */
    private function __construct(\ReflectionParameter $reflectionParameter)
    {
        $this->reflectionParameter = $reflectionParameter;
        $this->argumentName        = $this->reflectionParameter->getName();
        $this->hasType             = true === \is_object($this->reflectionParameter->getType());
        $this->isObject            = $this->assertType('object');
        $this->typeClass           = $this->getTypeClassName();
    }

    /**
     * @return string|null
     */
    private function getTypeClassName(): ?string
    {
        if(true === $this->isObject)
        {
            /** @var \ReflectionClass $reflectionClass */
            $reflectionClass = $this->reflectionParameter->getClass();

            return $reflectionClass->getName();
        }

        return null;
    }

    /**
     * Compare argument types
     *
     * @param string $expectedType
     *
     * @return bool
     */
    private function assertType(string $expectedType): bool
    {
        if(true === $this->hasType)
        {
            /** @var \ReflectionType $type */
            $type = $this->reflectionParameter->getType();

            if(true === \class_exists($type->getName()) || true === \interface_exists($type->getName()))
            {
                return 'object' === $expectedType;
            }

            return $expectedType === $type->getName();
        }

        return false;
    }
}
