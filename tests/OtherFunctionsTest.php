<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) Common component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\Common\Tests;

use function Desperado\ServiceBus\Common\removeDirectory;
use function Desperado\ServiceBus\Common\uuid;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 *
 */
final class OtherFunctionsTest extends TestCase
{
    /**
     * @test
     *
     * @return void
     */
    public function uuid(): void
    {
        $uuid = uuid();

        static::assertNotEmpty($uuid);
        static::assertTrue(Uuid::isValid($uuid));
    }

    /**
     * @test
     *
     * @return void
     */
    public function removeDirectory(): void
    {
        $tmpDirectoryPath = \sys_get_temp_dir() . '/' . sha1(__METHOD__);

        @unlink($tmpDirectoryPath);

        \mkdir($tmpDirectoryPath);
        file_put_contents($tmpDirectoryPath . '/q.txt', 'qwerty');

        static::assertDirectoryExists($tmpDirectoryPath);

        removeDirectory($tmpDirectoryPath);

        static::assertDirectoryNotExists($tmpDirectoryPath);
    }
}