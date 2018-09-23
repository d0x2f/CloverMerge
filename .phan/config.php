

<?php
return [
    'target_php_version' => '7.2',

    'directory_list' => [
        'src/',
        'vendor/kahlan/kahlan/src',
        'vendor/vanilla/garden-cli/src',
        'vendor/jms/serializer/src',
        'vendor/php-ds/php-ds/src'
    ],

    "exclude_analysis_directory_list" => [
        'vendor/'
    ],

    'minimum_severity' => 0,
];
