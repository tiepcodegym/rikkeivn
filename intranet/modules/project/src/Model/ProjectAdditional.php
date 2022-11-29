<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;
use DB;

class ProjectAdditional extends CoreModel
{
    use SoftDeletes;

    /*
     * type project
     */
    const TYPE_OSDC = 1;
    const TYPE_BASE = 2;
    const TYPE_TRAINING = 3;
    const TYPE_RD = 4;
    const TYPE_ONSITE = 5;
    const TYPE_OPPORTUNITY = 30;

    /*
     * state project
     */
    const STATE_NEW = 1;
    const STATE_PROCESSING = 2;
    const STATE_PENDING = 3;
    const STATE_CLOSED = 4;
    const STATE_REJECT = 5;
    const STATE_FUTURE = 40;
    const STATE_OPPORTUNITY = 30;


    protected $table = 'projs_additional';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'type', 'note', 'unit_price',
        'team_id', 'approved_production_cost', 'month', 'year', 'price'];

    /**
     * insert or update project
     * @param array
     */
    public static function insertProjectAdditional($input, $isFlagCreate = false)
    {
        DB::beginTransaction();
        if (isset($input['name_old']) && isset($input['type_old'])) {
            self::where([
                ['name', $input['name_old']],
                ['type', $input['type_old']],
            ])->delete();
        }
        if ($isFlagCreate) {
            $insertArray = [] ;
            foreach ($input['datadetai'] as $data) {
                $insertArray [] = [
                    'name' => $input['name'],
                    'type' => $input['type'],
                    'team_id' => $data['teamId'],
                    'approved_production_cost' => $data['approved_production_cost'],
                    'month' => $data['month'],
                    'year' => $data['year'],
                    'note' =>isset($data['approve_cost_note']) ? $data['approve_cost_note'] : null,
                    'price' => $data['price'],
                    'unit_price' => $data['unit_price'],
                    'kind_id' => $input['kind_id']
                ];

                if (isset($data['detail'])) {
                    foreach ($data['detail'] as $value) {
                        $insertArray [] = [
                            'name' => $input['name'],
                            'type' => $input['type'],
                            'team_id' => $value['teamId'],
                            'approved_production_cost' => $value['approved_production_cost'],
                            'month' => $data['month'],
                            'year' => $data['year'],
                            'note' =>isset($value['approve_cost_note']) ? $value['approve_cost_note'] : null,
                            'price' => $value['price'],
                            'unit_price' => $value['unit_price'],
                            'kind_id' => $input['kind_id']
                        ];
                    }
                }
            }

            ProjectAdditional::insert($insertArray);
        }

        DB::commit();
        return true;
    }

    /***
     * Delete
     */
    public static function deleteProjectAdditional($id)
    {
        $model = self::findOrFail($id);
        return self::where([
            ['name', '=', $model->name],
            ['type', '=', $model->type],
            ['team_id', '=', $model->team_id],
        ])->forceDelete();
    }

    /***
     * update
     */
    public static function updateProjectAdditional($attribute)
    {
        $item = self::find($attribute['id']);
        $item->approved_production_cost = $attribute['approved_production_cost'];
        $item->save();

        return [
            'status' => true,
            'data' => $item
        ];
    }

    public static function getProjectFutureDetail($request)
    {
        $data = self::where([
            ['name', $request->name],
            ['type', $request->type],
        ])
        ->whereNull('deleted_at')
        ->orderBy('name')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        return $data;
    }

    public static function renderDataToDisplayView($data)
    {
        $dataUsingForView = [];
        foreach ($data as $item) {
            $keyYearMonth = $item['year'] . '-' . str_pad($item['month'], 2, '0', STR_PAD_LEFT);

            if (!array_key_exists($keyYearMonth, $dataUsingForView)) {
                $dataUsingForView[$keyYearMonth] = [];
            }

            $dataUsingForView[$keyYearMonth][] = (object) [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->type,
                'kind_id' => $item->kind_id,
                'team_id' => $item->team_id,
                'approved_production_cost' => $item->approved_production_cost,
                'month' => $item->month,
                'year' => $item->year,
                'note' => $item->note,
                'price' => $item->price,
                'unit_price' => $item->unit_price,
            ];
        }

        return $dataUsingForView;
    }
}
