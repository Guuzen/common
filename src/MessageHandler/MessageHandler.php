<?php

/**
 * PHP Service Bus common component.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Common\MessageHandler;

use ServiceBus\Common\MessageExecutor\MessageHandlerOptions;

/**
 * Message handler details.
 *
 * @psalm-immutable
 */
final class MessageHandler
{
    /** @var string */
    public $methodName;

    /** @var bool */
    public $hasArguments;

    /**
     * Collection of arguments to the message handler.
     *
     * @see MessageHandlerArgument
     *
     * @var \SplObjectStorage
     */
    public $arguments;

    /**
     * Message class for which the handler was created.
     *
     * @var string|null
     */
    public $messageClass = null;

    /** @var MessageHandlerReturnDeclaration */
    public $returnDeclaration;

    /** @var MessageHandlerOptions */
    public $options;

    /**
     * Execution closure.
     *
     * @psalm-var \Closure(object, \ServiceBus\Common\Context\ServiceBusContext):\Amp\Promise
     *
     * @var \Closure
     */
    public $closure;

    /**
     * Message handler description
     *
     * @var string|null
     */
    public $description;

    /**
     * @psalm-param class-string $messageClass
     * @psalm-param \Closure(object, \ServiceBus\Common\Context\ServiceBusContext):\Amp\Promise $closure
     */
    public function __construct(
        string $messageClass,
        \Closure $closure,
        \ReflectionMethod $reflectionMethod,
        MessageHandlerOptions $options,
        ?string $description = null
    ) {
        $this->closure           = $closure;
        $this->options           = $options;
        $this->methodName        = $reflectionMethod->getName();
        $this->messageClass      = $messageClass;
        $this->arguments         = $this->extractArguments($reflectionMethod);
        $this->hasArguments      = 0 !== \count($this->arguments);
        $this->returnDeclaration = $this->extractReturnDeclaration($reflectionMethod);
        $this->description       = $description;
    }

    /**
     * Retrieves a collection of method arguments.
     *
     * @psalm-suppress ImpureMethodCall
     */
    private function extractArguments(\ReflectionMethod $reflectionMethod): \SplObjectStorage
    {
        $argumentCollection = new \SplObjectStorage();

        $position = 1;

        foreach ($reflectionMethod->getParameters() as $parameter)
        {
            $argumentCollection->attach(new MessageHandlerArgument($position, $parameter));

            ++$position;
        }

        return $argumentCollection;
    }

    /**
     * Retrieves a method return declaration.
     *
     * @psalm-suppress ImpureMethodCall
     */
    private function extractReturnDeclaration(\ReflectionMethod $reflectionMethod): MessageHandlerReturnDeclaration
    {
        $returnDeclaration = $reflectionMethod->getReturnType();

        if ($returnDeclaration instanceof \ReflectionNamedType)
        {
            return MessageHandlerReturnDeclaration::create($returnDeclaration);
        }

        return MessageHandlerReturnDeclaration::createVoid();
    }
}
