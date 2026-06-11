<?php

declare(strict_types=1);

namespace Integrations\View\Directives;

class UpperDirective
{
    public function __invoke(string $expression): string
    {
        return "<?php echo strtoupper({$expression}); ?>";
    }
}
