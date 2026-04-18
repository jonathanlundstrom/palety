<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Node Modules Path
    |--------------------------------------------------------------------------
    |
    | The path to the node_modules directory containing Puppeteer. Defaults to
    | the project root's node_modules, which is correct for both local development
    | and the Docker build (where Puppeteer is installed in the project directory).
    |
    */
    'node_modules_path' => env('BROWSERSHOT_NODE_MODULES_PATH', base_path('node_modules')),

    /*
    |--------------------------------------------------------------------------
    | Chrome Executable Path
    |--------------------------------------------------------------------------
    |
    | Path to the Chrome/Chromium executable. When null, Puppeteer uses its
    | bundled Chrome (downloaded via `puppeteer browsers install`), if installed.
    | In Dockerfile, PUPPETEER_EXECUTABLE_PATH is set to the Alpine Chromium binary.
    |
    */
    'chrome_path' => env('PUPPETEER_EXECUTABLE_PATH'),
];
