<?php

namespace Rikkei\News\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\News\Model\Post;
use Rikkei\News\Model\PostSchedule;

class PostEnable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'post:enable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[News] Set lịch đăng bài viết';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    protected $domain;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->info("======= Start schedule posting =======\n");
            Log::info("======= Start schedule posting =======\n");
            $post = PostSchedule::where('publish_at', '<=', Carbon::now()->format('Y-m-d H:i'))->get();
            foreach ($post as $value) {
                DB::table('blog_posts')->where('id',$value->post_id)->update(['status' => Post::STATUS_ENABLE]);
                PostSchedule::where('post_id', $value->post_id)->update(['deleted_at' => Carbon::now()->format('Y-m-d H:i')]);
            }
            $this->info("======= End schedule posting =======\n");
            Log::info("======= End schedule posting =======\n");
        } catch (\Exception $ex) {
            Log::error($ex);
            $this->info("\n======= Error schedule posting =======\n");
        }
    }
}
