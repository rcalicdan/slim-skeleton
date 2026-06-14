<?php

declare(strict_types=1);

namespace Integrations\View\Directives;

class SessionDirective
{
    public function __invoke(string $expression): string
    {
        return "<?php 
            if (session()->has({$expression}) || session()->getFlash()->has({$expression})): 
                \$value = session({$expression}) ?? session()->getFlash()->get({$expression}); 
                
                // If it's a flash message, Odan/Session returns an array of messages for that key.
                // For convenience, it unwrap the first message if it's a single-item array.
                if (is_array(\$value) && count(\$value) === 1) {
                    \$value = \$value[0];
                }
        ?>";
    }
}