<?php

namespace Rikkei\Resource\Seeds;

use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Core\Seeds\CoreSeeder;
use Illuminate\Support\Facades\DB;

class CandidateAddressSeeder extends CoreSeeder
{

    public function run()
    {
        if ($this->checkExistsSeed()) {
            return;
        }

        $collectContacts = EmployeeContact::where(function ($query) {
            $query->whereNull('native_province')
                    ->orWhere('native_province', '');
        })
        ->whereNotNull('tempo_province')
        ->get();
        if ($collectContacts->isEmpty()) {
            return;
        }
        $fieldUpdate = [
            'tempo_country' => 'native_country',
            'tempo_province' => 'native_province',
            'tempo_district' => 'native_district',
            'tempo_ward' => 'native_ward',
            'tempo_district' => 'native_district',
            'tempo_addr' => 'native_addr'
        ];

        DB::beginTransaction();
        try {
            foreach ($collectContacts as $item) {
                foreach ($fieldUpdate as $fromField => $toField) {
                    if (!$item->{$toField}) {
                        $item->{$toField} = $item->{$fromField};
                    }
                }
                $item->save();
            }

            DB::commit();
            $this->insertSeedMigrate();
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            throw $ex;
        }
    }

}

