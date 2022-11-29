<?php

namespace Rikkei\FinesMoney\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\FinesMoney\View\ImportFinesMoney;
use Illuminate\Support\Facades\File;

class JobFinesMoney extends CoreModel
{
    protected $table = 'job_fines_money';

    protected $fillable = [
        'created_by', 'num', 'total', 'file_path', 'created_at', 'updated_at'
    ];
    public $timestamps = true;


    public function checkJobSuccess($employeeId)
    {
        $job = self::where('created_by', $employeeId)->orderBy('created_at', 'desc')->first();
        if (!$job) {
            return;
        }

        $job->increment('num', ImportFinesMoney::CHUNK_ROW);
        if ($job->num >= $job->total) {

            $path = storage_path('app/'.$job->file);
            if ($job->file && File::exists($path)) {
                @unlink($path);
                $job->delete();
            }
        }
    }
}
