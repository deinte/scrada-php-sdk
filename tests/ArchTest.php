<?php

declare(strict_types=1);

use Deinte\ScradaSdk\Exceptions\ScradaException;
use Saloon\Http\BaseResource;
use Saloon\Http\Request;

use function Pest\Plugin\Arch\arch;

arch('all classes are final')
    ->expect('Deinte\ScradaSdk')
    ->classes()
    ->toBeFinal()
    ->ignoring([
        ScradaException::class,
    ]);

arch('no else statements')
    ->expect('Deinte\ScradaSdk')
    ->not->toUse('else');

arch('exceptions extend base exception')
    ->expect('Deinte\ScradaSdk\Exceptions')
    ->toExtend(ScradaException::class)
    ->ignoring([
        ScradaException::class,
    ]);

arch('Data classes are readonly')
    ->expect('Deinte\ScradaSdk\Data')
    ->classes()
    ->toBeReadonly();

arch('requests extend Saloon request')
    ->expect('Deinte\ScradaSdk\Requests')
    ->toExtend(Request::class);

arch('resources extend Saloon base resource')
    ->expect('Deinte\ScradaSdk\Resources')
    ->toExtend(BaseResource::class);

it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();
