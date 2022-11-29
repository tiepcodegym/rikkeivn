<?php

namespace Rikkei\News\Seeds;

use Carbon\Carbon;
use Exception;
use Log;
use Rikkei\Core\Seeds\CoreSeeder;
use DB;

class BlogCategoriesSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return bool
     * @throws Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        DB::beginTransaction();
        try {
            $data = [
                [
                    'title' => 'Cuộc thi',
                    'title_en' => 'Contest'
                ],
                [
                    'title' => 'Đào tạo',
                    'title_en' => 'Training'
                ],
                [
                    'title' => 'Tin đối ngoại',
                    'title_en' => 'Diplomacy'
                ],
                [
                    'title' => 'Báo chí',
                    'title_en' => 'Press'
                ],
                [
                    'title' => 'Chuyên môn',
                    'title_en' => 'Major'
                ],
                [
                    'title' => 'Tin điều hành',
                    'title_en' => 'Operation'
                ],
                [
                    'title' => 'Sự kiện',
                    'title_en' => 'Event'
                ],
                [
                    'title' => 'YUME',
                    'title_en' => 'YUME'
                ],
                [
                    'title' => 'Người Rikkei',
                    'title_en' => 'Rikkeisofter'
                ],
                [
                    'title' => 'Sách cho người Rikkei',
                    'title_en' => 'Rikkei bookstore'
                ],
                [
                    'title' => 'Đời sống',
                    'title_en' => 'Lifestyle'
                ],
                [
                    'title' => 'Rikkei toàn cầu',
                    'title_en' => 'Rikkei global'
                ],
                [
                    'title' => 'Rikkei Hồ Chí Minh',
                    'title_en' => 'Rikkei HCM'
                ],
                [
                    'title' => 'Rikkei Japan',
                    'title_en' => 'Rikkei Japan'
                ],
                [
                    'title' => 'Multimedia',
                    'title_en' => 'Multimedia'
                ],
            ];
            foreach ($data as $datum) {
                $blogCate = DB::table('blog_categories')->where('title', $datum['title']);
                if ($blogCate->get()) {
                    $blogCate->update($datum);
                }
            }
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }
}
