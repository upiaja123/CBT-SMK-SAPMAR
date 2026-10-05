<?php

$publicPath = base_path('public');
if (!is_dir($publicPath) && is_dir(base_path('../public_html'))) {
    $publicPath = base_path('../public_html');
}

return [
    'public_path' => $publicPath,
    'options' => [
        'isRemoteEnabled' => true,
        'chroot' => [$publicPath, base_path('storage/app/public')],
    ],
];
