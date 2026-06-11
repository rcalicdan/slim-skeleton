<?php

declare(strict_types=1);

namespace Integrations\View\Directives;

class MethodDirective
{
    public function __invoke(string $expression): string
    {
        return "<?php echo method_field({$expression}); ?>";
    }
}