<?php
namespace Rikkei\Core\Seeds;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * convert protocol of col link follow protocol current
 */
class HttpsSeeder extends CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        $appParseUrl = parse_url(Config::get('app.url'));
        $this->appHost = $appParseUrl['host'];
        $this->appScheme = $appParseUrl['scheme'];
        DB::beginTransaction();
        try {
            $this->convertProtocol('users', 'avatar_url', 'employee_id');
            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    protected function convertProtocol($tbl, $col, $colPrimary = 'id')
    {
        $collection = DB::table($tbl)
            ->select([$colPrimary, $col])
            ->whereNotNull($col)
            ->get();
        if (!count($collection)) {
            return true;
        }
        foreach ($collection as $item) {
            $urlParse = parse_url($item->{$col});
            // convert if link same domain and diffirent protocal
            if ($urlParse['host'] === $this->appHost && $urlParse['scheme'] !== $this->appScheme) {
                $urlNew = preg_replace('/^'.$urlParse['scheme'].'/', $this->appScheme, $item->{$col});
                DB::table($tbl)
                    ->where($colPrimary, $item->{$colPrimary})
                    ->update([
                        $col => $urlNew,
                    ]);
            }
        }
    }
}
