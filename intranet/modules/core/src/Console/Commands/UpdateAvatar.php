<?php

namespace Rikkei\Core\Console\Commands;

use Illuminate\Console\Command;
use File;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\Employee;
use Image;

class UpdateAvatar extends Command
{
    const TAG_RESIZE = 'resize';

    /**
     * The name and signature of the console command.
     * Command resize: php artisan update-avatar --tag=resize
     * Commang move file: php artisan update-avatar prefix=nv
     *
     * @var string
     */
    protected $signature = 'update-avatar {prefix=nv} {--tag=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Avatar employee from storage/avatar';

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
        if (!$this->confirm('Do you want update avatar user?')) {
            return;
        }

        $tag = $this->option('tag');
        if ($tag == self::TAG_RESIZE) {
            $this->resize();
        } else {
            $this->moveFolder();
        }

        $this->info("\n======= Update avatar success! =======\n");
    }

    /**
     * Move folder
     */
    private function moveFolder()
    {
        // get all files in storage/avatar
        $avatarDirectory = storage_path('avatar');
        if (!File::isDirectory($avatarDirectory)) {
            $this->error("This folder {$avatarDirectory} does not exist.");
            return;
        }

        // Get all file in folder
        $images = File::allFiles($avatarDirectory);
        if (empty($images)) {
            $this->warn("Folder {$avatarDirectory} is empty.");
            return;
        }

        try {
            $this->info("======= Start Update avatar =======\n");
            $bar = $this->output->createProgressBar(count($images));
            foreach ($images as $path) {
                $image = pathinfo($path);

                // check invalid File name
                $re = '/^(.*?(-\s+|-))(\d+)(((\s+-|-).*)|)(\.jpg|\.jpeg)$/i';
                preg_match($re, $image['basename'], $matches);

                if (!isset($matches[3]) || empty($matches[3])) {
                    \Log::error("Image name invalid. {$path}");
                    $this->error("Image name invalid. {$path}");
                    continue;
                }

                // Move file from storage/avatar -> storage/app/public/resource/employee/avatar/employee_id
                // Get Employee ID from employee_card_id & prefix
                $employeeCardId = $matches[3];
                $prefix = $this->argument('prefix');
                $employee = Employee::select('id')
                    ->where('employee_card_id', $employeeCardId)
                    ->where('employee_code', 'like', "{$prefix}%")
                    ->first();

                if (empty($employee)) {
                    \Log::error("Không tìm thấy nhân viên có employee_card_id= {$employeeCardId} & prefix={$prefix}");
                    $this->error("Không tìm thấy nhân viên có employee_card_id= {$employeeCardId} & prefix={$prefix}");
                    continue;
                }

                // Select Employee ID
                $avatarPath = storage_path("app/public/resource/employee/avatar/{$employee->id}/");
                if (!File::isDirectory($avatarPath)) {
                    File::makeDirectory($avatarPath, 0777, true);
                }

                $filename = str_random(16) . strtotime('now') . '.' . $image['extension'];
                File::move($path, $avatarPath . $filename);
                // End

                // Update users avatar_url
                User::where('employee_id', $employee->id)
                    ->update([
                        'avatar_url' => asset("storage/resource/employee/avatar/{$employee->id}/{$filename}")
                    ]);

                $bar->advance();
            }

            $bar->finish();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Resize avatar
     */
    private function resize()
    {
        $avatarDirectory = storage_path('app/public/resource/employee/avatar');
        if (!File::isDirectory($avatarDirectory)) {
            $this->error("This folder {$avatarDirectory} does not exist.");
            return;
        }

        try {
            $this->info("======= Start Update avatar =======\n");
            $images = File::allFiles($avatarDirectory);
            $bar = $this->output->createProgressBar(count($images));
            // Get all file in folder

            $widthResize = 200;
            foreach ($images as $file) {
                $path = $file->getPathname();

                $img = Image::make($path);
                $width = $img->getWidth();

                //Nếu kích thước ảnh nhỏ hơn kích thước resize thì bỏ qua
                if ($width <= $widthResize){
                    continue;
                }

                //resize image
                $img->widen($widthResize, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->heighten($widthResize, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

                //Save image with quality 75% (only JPG)
                $img->save($path, 75);

                $bar->advance();
            }
            $bar->finish();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
