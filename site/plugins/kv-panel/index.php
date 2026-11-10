<?php

use Kirby\Cms\App;

// Helpers for the Panel's page lists (site/blueprints/pages/*.yml)
App::plugin('kv/panel', [
    'pageMethods' => [
        // Name of a trip's category, as the option of its select field
        'categoryLabel' => function (): string {
            return match ($this->category()->value()) {
                'atelierbesuch' => 'Atelierbesuch',
                'kunstreise'    => 'Kunstreise',
                default         => '',
            };
        },
    ],
]);
