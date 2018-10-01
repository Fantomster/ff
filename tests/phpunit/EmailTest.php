<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 29.09.2018
 * Time: 21:13
 */

declare(strict_types=1);
namespace tests\phpunit;


use PHPUnit\Framework\TestCase;

/**
 * Class EmailTest
 *
 * @package tests\phpunit
 */
final class EmailTest extends TestCase
{
    /**
     * @throws \InvalidArgumentException
     */
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(
            \Email::class,
            \Email::fromString('user@example.com')
        );
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testCannotBeCreatedFromInvalidEmailAddress(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        \Email::fromString('invalid');
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function testCanBeUsedAsString(): void
    {
        $this->assertEquals(
            'user@example.com',
            \Email::fromString('user@example.com')
        );
    }
}
