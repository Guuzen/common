<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) Common component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Common\Exceptions\Reflection;

/**
 *
 */
final class ReflectionClassNotFound extends \InvalidArgumentException implements ReflectionExceptionMarker
{

}
