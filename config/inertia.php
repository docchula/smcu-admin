<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pages
    |--------------------------------------------------------------------------
    |
    | This application keeps its page components in `resources/js/Pages`, which
    | differs from the package default of `resources/js/pages`. The paths are
    | case-sensitive on Linux, so the override has to be explicit or component
    | lookups (including `assertInertia`) fail on CI.
    |
    */

    'pages' => [

        'ensure_pages_exist' => false,

        'paths' => [

            resource_path('js/Pages'),  // override the default 'js/pages' (case-sensitivity mismatch that only bites on Linux)

        ],

        'extensions' => [

            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',

        ],

    ],

];
