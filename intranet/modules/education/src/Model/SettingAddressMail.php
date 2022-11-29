<?php
namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class SettingAddressMail extends CoreModel
{
    protected $table = 'setting_address_mails';
    protected $fillable = [
        'id', 'team_id', 'email'
    ];
}
