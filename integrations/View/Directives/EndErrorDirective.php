<?php

declare(strict_types=1);

namespace Integrations\View\Directives;

class EndErrorDirective
{
    public function __invoke(?string $expression = null): string
    {
        return '<?php endif; ?>';
    }
}
