<?php

declare(strict_types=1);

namespace Integrations\View\Directives;

class ErrorDirective
{
    public function __invoke(string $expression): string
    {
        return "<?php if (has_error({$expression})): \$message = error({$expression}); ?>";
    }
}
