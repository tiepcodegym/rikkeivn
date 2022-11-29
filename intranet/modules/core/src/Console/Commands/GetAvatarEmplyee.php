<?php

namespace Rikkei\Core\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\Model\Employee;

class GetAvatarEmplyee extends Command
{
    const IMG_AVATAR = 'img_avatar';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employee:get_avatar';

    /**
     * The console command description.
     * tên ảnh
     *     có số CMTND: hoten_account_soCMTND
     *     không có số CMTND: hoten_account
     * @var string
     */
    protected $description = 'Lấy avatar của nhân viên';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            Log::info('=== Start get avatar employees ===');
            $this->info('=== Start get avatar employees ===');
            $pathStorage = storage_path('app/' . static::IMG_AVATAR);
            if (file_exists($pathStorage)) {
                File::deleteDirectory($pathStorage);
            }
            @mkdir($pathStorage, 0777, true);
            $employees = Employee::select(
                'employees.id as emp_id',
                'employees.name',
                'employees.email',
                'employees.id_card_number',
                'users.avatar_url'
            )
            ->join("users", 'users.employee_id', '=', 'employees.id')
            ->whereNotNull('users.avatar_url')
            ->get();

            $i = 0;
            foreach ($employees as $item) {
                $i++;
                if ($i % 100 === 0) {
                    $number = $i / 100;
                    $this->info("====== Sleep 60': {$number} ======");
                    sleep(60);
                }
                $avatarUrl = $item->avatar_url;
                $avatar = @get_headers($avatarUrl);
                if($avatar && $avatar[0] == 'HTTP/1.0 200 OK') {
                    $card = $item->id_card_number;
                    $extension = image_type_to_extension(exif_imagetype($avatarUrl));
                    $name = str_slug($item->name, '') . '_' . preg_replace('/@.*/', '', $item->email);
                    if ($card) {
                        $name .= '_' . $card;
                    }
                    $pathFile = $pathStorage . '/' . $name . $extension;

                    $pattern = '/=s[0-9]{1,3}/';
                    $avatarUrl = preg_replace($pattern, '=', $avatarUrl);
                    $pattern = '/\?sz=[0-9]{1,3}/';
                    $avatarUrl = preg_replace($pattern, '?', $avatarUrl);

                    copy($avatarUrl, $pathFile);
                }
            }
            $this->info('=== End get avatar employees ===');
            Log::info('=== End get avatar employees === ');
            return true;
        } catch (Exception $e) {
            $this->info($e->getMessage());
            Log::error($e);
        }
    }
}
