<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;

class ManageTimeAttachment extends CoreModel
{
    protected $table = 'manage_time_attachments';

    /**
     * [get all attachment of register]
     * @param  [int|null] $registerId 
     * @param  [int|null] $type 
     * @return array  
     */
    public static function getAttachments($registerId = null, $type = null)
    {
        $attachmentTable = self::getTableName();

    	$attachments = self::select("{$attachmentTable}.id as attachment_id", "{$attachmentTable}.register_id as register_id", "{$attachmentTable}.file_name as file_name", "{$attachmentTable}.path as path", "{$attachmentTable}.size as size", "{$attachmentTable}.mime_type as mime_type", "{$attachmentTable}.type as type");

        if ($registerId) {
            $attachments = $attachments->where("{$attachmentTable}.register_id", $registerId);
        }
        if ($type) {
            $attachments = $attachments->where("{$attachmentTable}.type", $type);
        }            
    	$attachments = $attachments->get();

    	return $attachments;
    }
}