<?php
/**
 * config general
 */
return [
    'language_level' => [
        'N1',
        'N2',
        'N3',
        'N4',
        'N5',

        'TOEIC 990',
        'TOEIC 950',
        'TOEIC 900',
        'TOEIC 850',
        'TOEIC 800',
        'TOEIC 750',
        'TOEIC 700',
        'TOEIC 650',
        'TOEIC 600',
        'TOEIC 500',
        'TOEIC 450',
        'TOEIC 400',
        'TOEIC 350',
        'TOEIC 300',
        'TOEIC 250',
        'TOEIC 200',
        'TOEIC 150',
        'TOEIC 100',
        
        'IELTS 9.0',
        'IELTS 8.5',
        'IELTS 8.0',
        'IELTS 7.5',
        'IELTS 7.0',
        'IELTS 6.5',
        'IELTS 6.0',
        'IELTS 5.5',
        'IELTS 5.0',
        'IELTS 4.5',
        'IELTS 4.0',
        'IELTS 3.5',
        'IELTS 3.0',
        'IELTS 2.5',
        'IELTS 2.0',
        'IELTS 1.5',
        'IELTS 1.0',
    ],
    
    'normal_level' => [
        1 => 'High',
        2 => 'Normal',
        3 => 'Low'
    ],
    
    'upload_folder' => 'storage', //folder public/storage/
    'upload_storage_public_folder' => 'public', //folder storage/app/public/
    
    /**
     * error path
     */
    'errors' => [
        'general' => 'maintain'
    ],
    'language_import' => [
        'TIENG NHAT' => 'Japanese',
        'TIENG ANH' => 'English',
        'TIENG TRUNG' => 'Chinese',
        'TIENG PHAP' => 'France',
        'ENGLISH' => 'English',
        'CHINESE' => 'Chinese',
        'FRANCE' => 'France',
        'JAPANESE' => 'Japanese'
    ],
    'format_excel' => [
        'format' => [
            'full_name',
            'birddayyear',
            'mobile',
        ],
        'nomal' => [
            'full_name' => 'fullname',
            'email' => 'email',
            'university' => 'university',
            'certificate' => 'certificate',
            'experience_year_number' => 'experience',
            'test_resultpassfail' => 'test_result',
            'note' => 'interview_note',
            'test_result' => 'test_mark',
            'interview_result' => 'interview_result',
        ],
        'date' => [
            'birddayyear' => 'birthday',
            'received_cv_date' => 'received_cv_date',
            'test_date' => 'test_date',
            'interview_date' => 'interview_date',
            'offer_date' => 'offer_date',
            'start_working_date' => 'start_working_date',
            'calling_date' => 'interview_calling_date',
            'email_date' => 'interview_email_date',
        ],
    ],
    'page' => [
        'limit' => 50,
        'order_dir' => 'desc',
        'order_order' => 'id',
    ]
];
