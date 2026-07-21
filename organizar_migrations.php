<?php

$path = __DIR__.'/database/migrations';

$files = glob($path.'/*.php');

sort($files);

$contador = 1;

foreach ($files as $file) {

    $nomeOriginal = basename($file);

    // remove timestamps antigos
    $nomeLimpo = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $nomeOriginal);

    // cria novo timestamp sequencial
    $novoTimestamp = date('Y_m_d_His', strtotime("2026-01-01 00:00:00 +$contador seconds"));

    $novoNome = $novoTimestamp.'_'.$nomeLimpo;

    $novoCaminho = $path.'/'.$novoNome;

    rename($file, $novoCaminho);

    echo "✔ $nomeOriginal → $novoNome\n";

    $contador++;
}
