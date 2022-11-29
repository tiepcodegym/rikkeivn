<?php

namespace Rikkei\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\User;

class InitCacheRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Khởi tạo giá trị cho column roles ở bảng users cho check quyền ở api';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            User::changeRoles();
            Log::info('success init cache role');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}

