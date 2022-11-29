<?php

namespace Rikkei\News\View;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\User;
use Rikkei\News\Model\Post;
use Rikkei\News\Model\LikeManage;
use Rikkei\News\Model\PostComment;
use Rikkei\News\Model\ViewManage;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;

class ViewNewPost
{
    /**
     * Get out the 10 most interactive members
     */
    public static function getTopMember()
    {
        $dayInLast30days = Carbon::now()->subDays(30)->format('Y-m-d');
        $limitPost = Post::where([
            ['public_at', '>=', $dayInLast30days],
            ['status', '=', Post::STATUS_ENABLE]
        ])->whereNull('deleted_at')->pluck('id')->toArray();
        $limitPost[] = -1;
        $limitPost = '(' . implode(',', $limitPost) . ')';

        $tableLikeManage = LikeManage::getTableName();
        $tablePostComment = PostComment::getTableName();
        $tableViewManager = ViewManage::getTableName();

        $likePost = LikeManage::TYPE_POST;
        $statusCommentActive = PostComment::STATUS_COMMENT_ACTIVE;
        $listTeamReject = array(54, 55, 24);
        $stringRejectId = '(00)';
        $listCode = array(
            'TTS_5593',
            'NV0001147',
            'JP2019058',
            'JP2019071',
            'DN0000064',
            'DN0000130',
            'DN0000339',
            'HCM0000053',
            'NV00324234',
            'NV29496729',
            'NV0001023',
            'NV0000920',
            'NV0000007',
            'NV0000042',
            'NV0000178',
            'NV0000008',
            'NV0000034',
            'NV0000254',
            'JP2018040',
            'NV0000022',
            'DN0000010',
        );
        $listEmpIdByCodeByTeam = TeamMember::getAllMemberOfTeam($listTeamReject);
        $listEmpIdByCode = Employee::getEmployeeByListCode($listCode);
        $listReject = array_unique(array_merge($listEmpIdByCodeByTeam, $listEmpIdByCode), SORT_REGULAR);
        if ($listReject) $stringRejectId = '(' . implode(', ', $listReject) . ')';

        return DB::select(
            "select employees.email, employees.name, users.avatar_url, table_d.* from
             (
                 select 
                     COALESCE(table_a.employee_id, COALESCE(table_b.employee_id, table_c.user_id)) as employee_id,
                     (COALESCE(table_b.total_like, 0) + COALESCE(table_a.total_view, 0) + COALESCE(table_c.total_comment, 0)) as points
                     from (
                         select employee_id, 
                         count(employee_id) as total_view from {$tableViewManager} where post_id in {$limitPost} AND employee_id NOT IN {$stringRejectId} group by employee_id
                     ) as table_a
                     LEFT JOIN
                     (
                         select employee_id,
                         count(employee_id) as total_like 
                         from {$tableLikeManage} where post_id in {$limitPost} AND type = {$likePost} AND employee_id NOT IN {$stringRejectId} group by employee_id
                     ) as table_b on table_b.employee_id = table_a.employee_id
                     LEFT JOIN
                     (
                         select user_id,
                         count(user_id) as total_comment
                         from {$tablePostComment} 
                         where post_id in {$limitPost} 
                            and deleted_at is null 
                            and status = {$statusCommentActive}
                            AND user_id NOT IN {$stringRejectId}
                         group by user_id
                     ) as table_c on table_b.employee_id = table_c.user_id
                 union
                 select 
                     COALESCE(table_a.employee_id, COALESCE(table_b.employee_id, table_c.user_id)) as employee_id,
                     (COALESCE(table_b.total_like, 0) + COALESCE(table_a.total_view, 0) + COALESCE(table_c.total_comment, 0)) as points
                      from (
                           select employee_id,
                           count(employee_id) as total_view from {$tableViewManager} where post_id in {$limitPost}  AND employee_id NOT IN {$stringRejectId} group by employee_id
                       ) as table_a
                     RIGHT JOIN
                     (
                         select employee_id,
                         count(employee_id) as total_like
                         from {$tableLikeManage} where post_id in {$limitPost} AND type = {$likePost} AND employee_id NOT IN {$stringRejectId} group by employee_id
                     ) as table_b on table_b.employee_id = table_a.employee_id
                     RIGHT JOIN
                     (
                         select user_id,
                         count(user_id) as total_comment
                         from {$tablePostComment} 
                         where post_id in {$limitPost} 
                            and deleted_at is null 
                            and status = {$statusCommentActive}
                            AND user_id NOT IN {$stringRejectId}
                         group by user_id
                     ) as table_c on table_b.employee_id = table_c.user_id
              ) as table_d
              JOIN employees ON employees.id = table_d.employee_id
              JOIN users ON employees.id =  users.employee_id 
                 where employees.deleted_at is null
                   and employees.leave_date is null
                 order by table_d.points desc limit 10"
        );
    }
    /**
     * Get top post Most comments
     */
    public static function getTopPostMostComment()
    {
        $arrPostId = PostComment::select(DB::raw('COUNT(post_id) AS count'), 'post_id')
            ->groupBy('post_id')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
        foreach ($arrPostId as $value) {
            $data[] = $value->post_id;
        }
        return Post::select('id', 'title', 'slug', 'image', 'short_desc', 'public_at', 'author', 'created_at')
            ->where('status', Post::STATUS_ENABLE)
            ->whereIn('id', $data)
            ->groupBy('id')
            ->get();
    }
    /**
     * Get post for slide
     */
    public static function getPostSlide()
    {
        return Post::select('id', 'title', 'slug', 'image', 'short_desc', 'public_at', 'author', 'created_at')
            ->join('blog_meta', 'blog_meta.post_id', '=', 'blog_posts.id')
            ->where('blog_posts.status', Post::STATUS_ENABLE)
            ->where('blog_meta.key', 'view')
            ->whereYear('blog_posts.public_at', '=', Carbon::now()->year)
            ->whereMonth('blog_posts.public_at', '=', Carbon::now()->month)
            ->groupBy(DB::raw('blog_meta.value DESC'))
            ->take(4)
            ->get();
    }
}
