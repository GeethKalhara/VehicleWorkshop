<?php

declare(strict_types=1);

use Random\Engine\Secure;
use Random\Randomizer;
use Vwork\Shared\Collections\IRegistry;
use Vwork\Web\Utils\AssetParser;
use Vwork\Web\Utils\CsrfTokenGenerator;
use Vwork\Web\Utils\IUtility;
use Vwork\Web\Utils\View;

/**
 * @var array<
 *  class-string<IUtility>,
 *  Closure(IRegistry): object
 * >
 */
return [
    View::class => static fn (IRegistry $r) => new View(
        dir: 'web/resources/views'
    ),
    CsrfTokenGenerator::class => static fn (IRegistry $r) => new CsrfTokenGenerator(
        secret: 'secret',
        rand: new Randomizer(new Secure())
    ),
    AssetParser::class => static fn (IRegistry $r) => new AssetParser(
        manifestPath: 'web/resources/build/assets.json'
    )
];
