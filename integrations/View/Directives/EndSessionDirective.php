<?php

declare(strict_types=1);

namespace Integrations\View\Directives;

class EndSessionDirective
{
    public function __invoke(?string $expression = null): string
    {
        return '<?php endif; ?>';
    }
}