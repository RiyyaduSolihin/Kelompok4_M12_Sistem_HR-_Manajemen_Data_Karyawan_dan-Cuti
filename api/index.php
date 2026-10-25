<?php

foreach (['/tmp/views', '/tmp/cache', '/tmp/sessions'] as $dir) {
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require __DIR__.'/../public/index.php';

