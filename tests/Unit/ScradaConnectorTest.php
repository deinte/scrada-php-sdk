<?php

declare(strict_types=1);

use Deinte\ScradaSdk\ScradaConnector;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ScradaConnectorTest extends TestCase
{
    public function test_it_requires_non_empty_credentials(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore argument.type */
        new ScradaConnector('', 'secret', 'company');
    }

    public function test_company_id_accessor_returns_value(): void
    {
        $connector = new ScradaConnector('key', 'secret', 'company');

        self::assertSame('company', $connector->getCompanyId());
    }
}
