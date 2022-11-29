<?php


return array(
    
    'magazine_dir' => 'magazines/',
    
    'image_sizes' => [
        'thumbnail' => [
            'width' => 120,
            'height' => 120,
            'crop' => true
        ],
        'slide' => [
            'width' => 915,
            'height' => 1294,
            'crop' => false
        ],
        'full' => [
            'width' => 1920,
            'height' => null,
            'crop' => false
        ],
    ],

    //mime image
    'type' => [ 
        'image/jpeg',
        'image/png',
        'image/x-ms-bmp',
        'image/gif',
        'image/bmp',
    ],
    
    /**
     * mime to extension
     */
    'extensions' => [
        'image/gif' => 'gif',
        'image/jpeg' => ['jpeg','jpg'],
        'image/png' => 'png',
        'image/bmp' => ['bmp', 'bin'],
        'image/x-ms-bmp' => 'bmp',
        'text/xml' => 'xml',
    ],
    
    'news' => [
        'size_thumbnail_width' => 400,
        'size_thumbnail_height' => 1000,
        
        'size_detail_width' => 1000,
        'size_detail_height' => 4000,
    ]
);
