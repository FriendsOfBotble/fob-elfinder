<?php

return [
    'menu_name' => 'elFinder Media',
    'elfinder' => 'elFinder',
    'settings' => [
        'title' => 'elFinder',
        'description' => 'View and update the elFinder settings',
        'form' => [
            'enable_editor' => 'Enable elFinder for Editor',
            'replace_default_media' => 'Replace default media manager',
            'replace_default_media_helper' => 'You should set base path for elFinder is `storage` to view uploaded files in default media manager.',
            'base_path' => 'Base Path',
            'base_path_helper' => 'The base path for elFinder, relative to the public folder. Example: `files` your upload files will store in `public/files` folder.',
            'base_path_invalid' => 'Field :attribute must not starts with dot (.) or slash (/) character and ends with slash (/) character.',
            'base_path_does_not_starts_with_vendor_or_themes' => 'Field :attribute must not starts with `themes` or `vendor`.',
        ],
        'editor_not_support' => 'elFinder does not support :editor. Only CKEditor is supported.',
    ],
];
