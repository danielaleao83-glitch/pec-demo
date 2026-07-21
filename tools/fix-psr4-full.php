<?php

$basePath = __DIR__ . '/../app';

$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basePath)
);

foreach ($rii as $file) {

    if (!$file->isFile()) {
        continue;
    }

    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();

    echo "FIXANDO: {$path}\n";

    $content = file_get_contents($path);

    /*
    |--------------------------------------------------------------------------
    | Remove BOM UTF8
    |--------------------------------------------------------------------------
    */
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

    /*
    |--------------------------------------------------------------------------
    | Corrige <<?php
    |--------------------------------------------------------------------------
    */
    $content = preg_replace('/^<+\?php/', '<?php', $content);

    /*
    |--------------------------------------------------------------------------
    | Namespace baseado na pasta
    |--------------------------------------------------------------------------
    */
    $relative = str_replace($basePath . DIRECTORY_SEPARATOR, '', $path);

    $dir = dirname($relative);

    $namespace = 'App';

    if ($dir !== '.') {
        $namespace .= '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $dir);
    }

    /*
    |--------------------------------------------------------------------------
    | Nome correto da classe = nome do arquivo
    |--------------------------------------------------------------------------
    */
    $className = pathinfo($path, PATHINFO_FILENAME);

    /*
    |--------------------------------------------------------------------------
    | Corrige namespace
    |--------------------------------------------------------------------------
    */
    if (preg_match('/namespace\s+([^;]+);/', $content)) {

        $content = preg_replace(
            '/namespace\s+([^;]+);/',
            "namespace {$namespace};",
            $content,
            1
        );

    } else {

        $content = preg_replace(
            '/<\?php/',
            "<?php\n\nnamespace {$namespace};",
            $content,
            1
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Corrige nome da classe
    |--------------------------------------------------------------------------
    */
    $content = preg_replace(
        '/class\s+([a-zA-Z0-9_]+)/',
        "class {$className}",
        $content,
        1
    );

    file_put_contents($path, $content);
}

echo "\nPSR-4 FULL FIX concluído.\n";