<?php
return [
    'document' => [
        'path' => 'document',
        'label' => 'Document',
        'active' => '1',
        'child' => [
            'document.view.list' => [
                'path' => 'document',
                'label' => 'Document list',
                'active' => '1'
            ],
            'document.manage' => [
                'path' => 'document/manage',
                'label' => 'Document manage',
                'active' => '1',
                'action_code' => 'doc.manage'
            ],
            'document.create' => [
                'path' => 'document/manage/edit',
                'label' => 'Create document',
                'active' => '1',
                'action_code' => 'doc.manage'
            ],
            'document.type.list' => [
                'path' => 'document/manage/types',
                'label' => 'Document types',
                'active' => '1',
                'action_code' => 'doc.type.manage'
            ],
        ]
    ],
];
