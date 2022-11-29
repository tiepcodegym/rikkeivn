<?php

namespace Rikkei\ManageTime\Http\Services;

use Maatwebsite\Excel\Collections\SheetCollection;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\ManageTime\Model\WorkPlace;
use Rikkei\ManageTime\Model\EmployeeWorkPlace;
use DB, Lang;
use Carbon\Carbon;

class WorkPlaceServices
{
    protected $modelPlace;

    protected $modelEmployeePlace;

    public function __construct(WorkPlace $modelPlace, EmployeeWorkPlace $modelEmployeePlace)
    {
        $this->modelPlace = $modelPlace;
        $this->modelEmployeePlace = $modelEmployeePlace;
    }

    public function insertEmployeePlace($file)
    {
        DB::beginTransaction();
        try {
            $data = Excel::selectSheetsByIndex(0)->noHeading()->load($file->getRealPath(), 'UTF-8', function ($reader) {
                $reader->ignoreEmpty();
            })->get()->toArray();
            $rowErr = 0;
            $data = array_slice($data, 2);
            if (!count($data)) {
                $messages = [
                    'errors' => [
                        Lang::get('manage_time::view.Invalid file')
                    ]
                ];
                return $messages;
            }
            unset($data[0]);
            $errors = [];
            $datasInsertEmployee = [];
            foreach ($data as $key => $itemRow) {
                $rowErr++;
                $dataInsert = [];
                $dataInsert['employee_code'] = $itemRow[$rowErr];
                $rowErr++;
                $dataInsert['code_place'] = $itemRow[$rowErr];
                $rowErr++;
                $dataInsert['start_date'] = Carbon::parse($itemRow[$rowErr])->format('Y-m-d');
                $rowErr++;
                $dataInsert['end_date'] = Carbon::parse($itemRow[$rowErr])->format('Y-m-d');
                $datasInsertEmployee[] = $dataInsert;
                $rowErr = 0;
            }
            if (count($errors)) {
                DB::rollback();
                $messages = [
                    'errors' => $errors
                ];
            } else {
                $this->modelEmployeePlace->insert($datasInsertEmployee);
                DB::commit();
                $messages = [
                    'success' => [
                        Lang::get('manage_time::view.Import success')
                    ]
                ];
            }
            return $messages;

        }  catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [$ex->getMessage()]]);
        }
    }

    public function export()
    {
        $collection = $this->modelEmployeePlace->all();
        $fileName = Carbon::now()->format('Y-m-d') . '_export_employee_work_place';

        Excel::create($fileName, function ($excel) use ($collection) {
            $excel->setTitle(trans('manage_time::view.Workplace management'));
            $excel->sheet(trans('manage_time::view.Workplace management'), function ($sheet) use ($collection) {
                $sheet->mergeCells('A1:E1');
                // show title
                $sheet->cells('A1', function ($cells) {
                    $cells->setValue(trans('manage_time::view.List of employees by location'));
                    $cells->setFontWeight('bold');
                    $cells->setFontSize('18');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                // No
                $sheet->cells('A3', function ($cells) {
                    $cells->setValue(trans('manage_time::view.No.'));
                });
                // employee code
                $sheet->cells('B3', function ($cells) {
                    $cells->setValue(trans('manage_time::view.Employee code'));
                });
                // Location code
                $sheet->cells('C3', function ($cells) {
                    $cells->setValue(trans('manage_time::view.Location code'));
                });
                // Start date
                $sheet->cells('D3', function ($cells) {
                    $cells->setValue(trans('manage_time::view.Start date'));
                });
                // End date
                $sheet->cells('E3', function ($cells) {
                    $cells->setValue(trans('manage_time::view.End date'));
                });
                // import data
                $count = 4;
                foreach ($collection as $key => $value) {
                    $rowData = [
                        $key + 1,
                        $value && $value->employee_code ? $value->employee_code : '',
                        $value && $value->code_place ? $value->code_place : '',
                        $value && $value->start_date ? Carbon::parse($value->start_date)->format('Y-m-d') : '',
                        $value && $value->end_date ? Carbon::parse($value->end_date)->format('Y-m-d') : '',
                    ];
                    $sheet->row($count++, $rowData);
                }

                //set customize style
                $sheet->getStyle('A3:E3')->applyFromArray([
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => '004e00']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'ffffff'],
                            'bold' => true
                        ]
                    ]
                );
                //set wrap text
                $sheet->getStyle('A4:E' . ($collection->count() + 1))->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');
    }
}
