<?php

/**
 * PHP Service Bus common component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\Common\Messages;

/**
 * Used to request that an action should be taken. A Command is intended to be sent to a receiver (all commands should
 * have one logical owner and should be sent to the endpoint responsible for processing)
 *
 * @noinspection PhpDeprecationInspection
 * @deprecated Now interface is not required. Will be removed in 3.1 version
 */
interface Command extends Message
{

}