<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) common component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Common\Exceptions\Reflection;

/**
 *
 */
final class ReflectionClassNotFound extends \InvalidArgumentException implements ReflectionExceptionMarker
{

}
