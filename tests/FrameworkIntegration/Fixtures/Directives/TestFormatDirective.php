<?php

declare(strict_types=1);

namespace Tests\FrameworkIntegration\Fixtures\Directives;

class TestFormatDirective
{
    public function __invoke($expression): string
    {
        return "<?php echo strtoupper($expression); ?>";
    }
}
