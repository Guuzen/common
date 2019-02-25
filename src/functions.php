<?php

/**
 * PHP Service Bus common component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Common;

use Ramsey\Uuid\Uuid;
use ServiceBus\Common\Exceptions\DateTimeException;
use ServiceBus\Common\Exceptions\FileSystemException;
use ServiceBus\Common\Exceptions\ReflectionApiException;

/**
 * @noinspection PhpDocMissingThrowsInspection
 *
 * Generate a version 4 (random) UUID.
 *
 * @return string
 */
function uuid(): string
{
    /** @noinspection PhpUnhandledExceptionInspection */
    return Uuid::uuid4()->toString();
}

/**
 * Create datetime object from valid string
 *
 * @param string|null               $datetimeString
 * @param \DateTimeZone|string|null $timezone
 *
 * @return \DateTimeImmutable|null
 *
 * @throws \ServiceBus\Common\Exceptions\DateTimeException
 */
function datetimeInstantiator(?string $datetimeString, $timezone = null): ?\DateTimeImmutable
{
    if(null !== $datetimeString && '' !== $datetimeString)
    {
        try
        {
            if(true === \is_string($timezone) && '' !== $timezone)
            {
                $timezone = new \DateTimeZone($timezone);
            }

            /** @var \DateTimeZone|null $timezone */
            return new \DateTimeImmutable($datetimeString, $timezone);
        }
        catch(\Throwable $throwable)
        {
            throw DateTimeException::fromThrowable($throwable);
        }
    }

    return null;
}

/**
 * Receive datetime as string representation (or null if not specified)
 *
 * @param \DateTimeInterface|null $dateTime
 * @param string                  $format
 *
 * @return string|null
 *
 * @throws \ServiceBus\Common\Exceptions\DateTimeException
 */
function datetimeToString(?\DateTimeInterface $dateTime, string $format = 'Y-m-d H:i:s'): ?string
{
    if(null !== $dateTime)
    {
        /** @var string|false $result */
        $result = $dateTime->format($format);

        if(false !== $result && false !== \strtotime($result))
        {
            return $result;
        }

        throw DateTimeException::wrongFormat($format);
    }

    return null;
}

/**
 * @param object $object
 * @param string $methodName
 * @param mixed  ...$parameters
 *
 * @return mixed
 *
 * @throws \ServiceBus\Common\Exceptions\ReflectionApiException
 */
function invokeReflectionMethod(object $object, string $methodName, ...$parameters)
{
    try
    {
        $reflectionMethod = new \ReflectionMethod($object, $methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($object, ...$parameters);
    }
    catch(\ReflectionException $exception)
    {
        throw ReflectionApiException::fromThrowable($exception);
    }
}

/**
 * Write value to property
 *
 * @param object $object
 * @param string $propertyName
 * @param mixed  $value
 *
 * @return void
 *
 * @throws \ServiceBus\Common\Exceptions\ReflectionApiException
 */
function writeReflectionPropertyValue(object $object, string $propertyName, $value): void
{
    $attribute = extractReflectionProperty($object, $propertyName);

    $attribute->setAccessible(true);
    $attribute->setValue($object, $value);
}

/**
 * Read property value
 *
 * @psalm-suppress MixedAssignment Mixed return data type
 *
 * @param object $object
 * @param string $propertyName
 *
 * @return mixed
 *
 * @throws \ServiceBus\Common\Exceptions\ReflectionApiException
 */
function readReflectionPropertyValue(object $object, string $propertyName)
{
    $attribute = extractReflectionProperty($object, $propertyName);

    $attribute->setAccessible(true);

    return $attribute->getValue($object);
}

/**
 * Extract property
 *
 * @internal
 *
 * @param object $object
 * @param string $propertyName
 *
 * @return \ReflectionProperty
 *
 * @throws \ServiceBus\Common\Exceptions\ReflectionApiException
 */
function extractReflectionProperty(object $object, string $propertyName): \ReflectionProperty
{
    try
    {
        return new \ReflectionProperty($object, $propertyName);
    }
    catch(\ReflectionException $e)
    {
        $reflector = new \ReflectionObject($object);

        /** @noinspection LoopWhichDoesNotLoopInspection */
        while($reflector = $reflector->getParentClass())
        {
            try
            {
                return $reflector->getProperty($propertyName);
            }
            catch(\Throwable $throwable)
            {
                /** Not interested */
            }
        }

        throw ReflectionApiException::propertyNotExists($propertyName, $object);
    }
}

/**
 * @psalm-param class-string $class
 *
 * @param string $class
 *
 * @return object
 *
 * @throws \ServiceBus\Common\Exceptions\ReflectionApiException
 */
function createWithoutConstructor(string $class): object
{
    try
    {
        return (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    }
    catch(\Throwable $throwable)
    {
        throw ReflectionApiException::classNotExists($class);
    }
}

/**
 * Reads entire file into a string
 *
 * @param string $filePath
 *
 * @return string
 *
 * @throws FileSystemException
 */
function fileGetContents(string $filePath): string
{
    if(false === \file_exists($filePath) || false === \is_readable($filePath))
    {
        throw FileSystemException::nonExistentFile($filePath);
    }

    $fileContents = \file_get_contents($filePath);

    // @codeCoverageIgnoreStart
    if(false === $fileContents)
    {
        throw FileSystemException::getContentFailed($filePath);
    }

    // @codeCoverageIgnoreEnd

    return $fileContents;
}

/**
 * Extract namespace from file content
 *
 * @param string $filePath
 *
 * @return string|null
 *
 * @throws \ServiceBus\Common\Exceptions\FileSystemException
 */
function extractNamespaceFromFile(string $filePath): ?string
{
    $fileContents = fileGetContents($filePath);

    $matches = [];

    if(
        false !== \preg_match('#^namespace\s+(.+?);$#sm', $fileContents, $matches) &&
        true === isset($matches[1])
    )
    {
        return \sprintf('%s\\%s',
            $matches[1],
            \pathinfo($filePath)['filename']
        );
    }

    return null;
}

/**
 * Recursive search of all files in the directory
 * Search for files matching the specified regular expression
 *
 * @psalm-suppress MixedTypeCoercion
 *
 * @param array<mixed, string> $directories
 * @param string               $regExp
 *
 * @return \Generator<\SplFileInfo>
 */
function searchFiles(array $directories, string $regExp): \Generator
{
    foreach($directories as $directory)
    {
        $regexIterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory)
            ),
            $regExp
        );

        yield from $regexIterator;
    }
}

/**
 * Casting paths to canonical form
 *
 * @param array<mixed, string> $paths
 *
 * @return array<int, string>
 */
function canonicalizeFilesPath(array $paths): array
{
    $result = [];

    foreach($paths as $path)
    {
        $result[] = (new \SplFileInfo($path))->getRealPath();
    }

    return $result;
}

/**
 * Formats bytes into a human readable string
 *
 * @param int $bytes
 *
 * @return string
 */
function formatBytes(int $bytes): string
{
    if(1024 * 1024 < $bytes)
    {
        return \round($bytes / 1024 / 1024, 2) . ' mb';
    }

    if(1024 < $bytes)
    {
        return \round($bytes / 1024, 2) . ' kb';
    }

    return $bytes . ' b';
}
