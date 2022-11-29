<?php

namespace Rikkei\Welfare\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Welfare\Model\Event;
use Rikkei\Welfare\Model\WelEmployee;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Rikkei\Welfare\Model\RelationName;
use Rikkei\Welfare\Model\WelfareFee;
use Rikkei\Welfare\Model\WelFeeMore;

class ExportController extends Controller
{

    const FORMAT_TYPE_DATA = '#,##0';

    /**
     * Export all employee of welfare
     *
     * @param int $welId
     */
    public function exportEmpoyees($welId)
    {
        $welfare = Event::find($welId);
        if (!$welfare) {
            return redirect()->route('welfare::welfare.event.index')
                    ->withErrors(trans('welfare::view.Not Found Welfare'));
        }
        $headerArray = [
            trans('welfare::view.Employee code'),
            trans('welfare::view.Employee name'),
            trans('team::view.Identity card number'),
            trans('team::view.Birthday'),
            trans('welfare::view.Job Position'),
            trans('welfare::view.Department job'),
            trans('welfare::view.Fee of employee'),
            trans('welfare::view.Fee of company support'),
        ];
        $employeeArray = WelEmployee::listExportEmployee($welId);
        array_unshift($employeeArray, $headerArray);


        Excel::create('DanhSachNhanVienThamGia_'. date('Y.m.d'), function ($excel) use ($employeeArray) {
            $excel->sheet('Nhân Viên Tham Gia', function ($sheet) use ($employeeArray) {
                $sheet->fromArray($employeeArray, null, 'A1', true, false);
                $sheet->setFontFamily('Arial');
                $sheet->setFontSize(10);

                $sheet->row(1, function($row) {
                    $row->setAlignment('center');
                    $row->setBackground('#ebebe0');
                    $row->setBorder('thin', 'thin', 'thin', 'thin');
                    $row->setFont([
                        'size' => '11',
                        'bold' => true
                    ]);
                });
                $sheet->setBorder('A1:F1', 'thin');
                $sheet->setAutoSize(true);
                $sheet->setColumnFormat([
                    'E2:E' . (count($employeeArray) + 10) => self::FORMAT_TYPE_DATA,
                    'F2:F' . (count($employeeArray) + 10) => self::FORMAT_TYPE_DATA,
                ]);
            });
        })->export('xlsx');
    }

    /**
     * Export all employee have participated of welfare
     *
     * @param int $welId
     * @return $this
     */
    public function exportEmployeesParticipate($welId)
    {
        $welfare = Event::find($welId);
        if (!$welfare) {
            return redirect()->route('welfare::welfare.event.index')
                    ->withErrors(trans('welfare::view.Not Found Welfare'));
        }

        $arg['is_confirm'] = true;
        $headerArray = [
            trans('welfare::view.Employee code'),
            trans('welfare::view.Employee name'),
            trans('team::view.Identity card number'),
            trans('team::view.Birthday'),
            trans('welfare::view.Job Position'),
            trans('welfare::view.Department job'),
            trans('welfare::view.Fee of employee'),
            trans('welfare::view.Fee of company support'),
            trans('welfare::view.Confirm participation'),
        ];

        $employeeArray = WelEmployee::listExportEmployee($welId, $arg);
        array_unshift($employeeArray, $headerArray);
        Excel::create('DanhSachNhanVienXacNhanThamGia_'. date('Y.m.d'), function ($excel) use ($employeeArray) {
            $excel->sheet('Nhân viên xác nhận tham gia', function ($sheet) use ($employeeArray) {
                $sheet->fromArray($employeeArray, null, 'A1', true, false);
                $sheet->setFontFamily('Arial');
                $sheet->setFontSize(10);

                $sheet->row(1, function($row) {
                    $row->setAlignment('center');
                    $row->setBackground('#ebebe0');
                    $row->setBorder('thin', 'thin', 'thin', 'thin');
                    $row->setFont([
                        'size' => '11',
                        'bold' => true
                    ]);
                });
                $sheet->setBorder('A1:G1', 'thin');
                $sheet->setAutoSize(true);
                $sheet->setColumnFormat([
                    'E2:E' . (count($employeeArray) + 10) => self::FORMAT_TYPE_DATA,
                    'F2:F' . (count($employeeArray) + 10) => self::FORMAT_TYPE_DATA,
                ]);
                $configs = trans('welfare::view.Yes') . ',' . trans('welfare::view.Not');
                $i = 2;
                while ($i <= (count($employeeArray)+ 20)) {
                    $objValidation = $sheet->getCell('G' . $i)->getDataValidation();
                    $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list.');
                    $objValidation->setPromptTitle('Pick from list');
                    $objValidation->setFormula1('"'.$configs.'"');
                    $i++;
                }

            });
        })->export('xlsx');
    }

    /**
     * Export all employee have joined of welfare
     *
     * @param int $welId
     */
    public function exportEmployeesHaveJoined($welId)
    {
        $welfare = Event::find($welId);
        if (!$welfare) {
            return redirect()->route('welfare::welfare.event.index')
                    ->withErrors(trans('welfare::view.Not Found Welfare'));
        }

        $arg['is_joined'] = true;
        $headerArray = [
            Lang::get('welfare::view.Employee code'),
            Lang::get('welfare::view.Employee name'),
            Lang::get('team::view.Identity card number'),
            Lang::get('team::view.Birthday'),
            Lang::get('welfare::view.Job Position'),
            Lang::get('welfare::view.Department job'),
            Lang::get('welfare::view.Fee of employee'),
            Lang::get('welfare::view.Fee of company support'),
            Lang::get('welfare::view.Confirm participation'),
            Lang::get('welfare::view.Joined'),
            Lang::get('welfare::view.Is beneficiary'),
        ];

        $empoyeesArray = WelEmployee::listExportEmployee($welId, $arg);
        array_unshift($empoyeesArray, $headerArray);
        Excel::create('DanhSachNhanVienDaThamGia_'. date('Y.m.d'), function ($excel) use ($empoyeesArray) {
            $excel->sheet('Nhân viên đã tham gia', function ($sheet) use ($empoyeesArray) {
                $sheet->fromArray($empoyeesArray, null, 'A1', true, false);
                $sheet->setFontFamily('Arial');
                $sheet->setFontSize(10);

                $sheet->row(1, function($row) {
                    $row->setAlignment('center');
                    $row->setBackground('#ebebe0');
                    $row->setBorder('thin', 'thin', 'thin', 'thin');
                    $row->setFont([
                        'size' => '11',
                        'bold' => true
                    ]);
                });
                $sheet->setBorder('A1:H1', 'thin');
                $sheet->setAutoSize(true);
                $sheet->setColumnFormat([
                    'E2:E' . (count($empoyeesArray) + 10) => self::FORMAT_TYPE_DATA,
                    'F2:F' . (count($empoyeesArray) + 10) => self::FORMAT_TYPE_DATA,
                ]);
                $configs = trans('welfare::view.Yes') . ',' . trans('welfare::view.Not');
                $i = 2;
                while ($i <= (count($empoyeesArray)+ 20)) {
                    $objValidation = $sheet->getCell('G' . $i)->getDataValidation();
                    $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list.');
                    $objValidation->setPromptTitle('Pick from list');
                    $objValidation->setFormula1('"'.$configs.'"');

                    $objValidationTwo = $sheet->getCell('H' . $i)->getDataValidation();
                    $objValidationTwo->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidationTwo->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidationTwo->setAllowBlank(false);
                    $objValidationTwo->setShowInputMessage(true);
                    $objValidationTwo->setShowErrorMessage(true);
                    $objValidationTwo->setShowDropDown(true);
                    $objValidationTwo->setErrorTitle('Input error');
                    $objValidationTwo->setError('Value is not in list.');
                    $objValidationTwo->setPromptTitle('Pick from list');
                    $objValidationTwo->setFormula1('"'.$configs.'"');

                    $objValidationThree = $sheet->getCell('I' . $i)->getDataValidation();
                    $objValidationThree->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidationThree->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidationThree->setAllowBlank(false);
                    $objValidationThree->setShowInputMessage(true);
                    $objValidationThree->setShowErrorMessage(true);
                    $objValidationThree->setShowDropDown(true);
                    $objValidationThree->setErrorTitle('Input error');
                    $objValidationThree->setError('Value is not in list.');
                    $objValidationThree->setPromptTitle('Pick from list');
                    $objValidationThree->setFormula1('"'.$configs.'"');

                    $i++;
                }
            });
        })->export('xlsx');
    }

    /**
     * Export attached of employee
     *
     * @param int $welId
     */
    public function exportAttached($welId)
    {
        $welfare = Event::find($welId);
        if (!$welfare) {
            return redirect()->route('welfare::welfare.event.index')
                    ->withErrors(trans('welfare::view.Not Found Welfare'));
        }

        $relations = RelationName::listStringRelation();
        $headerArray = [
            Lang::get('welfare::view.Employee code'),
            Lang::get('welfare::view.Employee name'),
            Lang::get('welfare::view.Full name attached'),
            Lang::get('welfare::view.Birthday'),
            Lang::get('welfare::view.Ages'),
            Lang::get('welfare::view.Gender'),
            Lang::get('welfare::view.Relation'),
            Lang::get('welfare::view.Rep Card ID'),
            Lang::get('welfare::view.Amount to pay'),
            Lang::get('welfare::view.Company fee'),
            Lang::get('welfare::view.Note'),
            Lang::get('welfare::view.Is beneficiary'),
        ];
        $attachedArray = WelEmployeeAttachs::exportListAttachByWelId($welId);//dd($attachedArray);
        array_unshift($attachedArray, $headerArray);
        Excel::create('DanhSachNguoiDiKem_'. date('Y.m.d'), function($excel) use($attachedArray, $relations) {
            $excel->sheet('Người đi kèm', function($sheet) use($attachedArray, $relations) {
                $sheet->fromArray($attachedArray, null, 'A1', true, false);
                $sheet->setFontFamily('Arial');
                $sheet->setFontSize(10);

                $sheet->row(1, function($row) {
                    $row->setAlignment('center');
                    $row->setBackground('#ebebe0');
                    $row->setBorder('thin', 'thin', 'thin', 'thin');
                    $row->setFont([
                        'size' => '11',
                        'bold' => true
                    ]);
                });
                $sheet->setBorder('A1:L1', 'thin');
                $sheet->setAutoSize(true);
                 $sheet->setColumnFormat([
                    'I2:I' . (count($attachedArray) + 10) => self::FORMAT_TYPE_DATA,
                    'J2:J' . (count($attachedArray) + 10) => self::FORMAT_TYPE_DATA,
                ]);

                $gender = Lang::get('team::view.Male') .','. Lang::get('team::view.Female');
                $configs = Lang::get('welfare::view.Yes') . ',' . Lang::get('welfare::view.Not');
                $ages = Lang::get('welfare::view.Is less than 12 years old') . ',' . Lang::get('welfare::view.Is over 12 years old');
                $i = 2;
                while ($i <= (count($attachedArray)+ 10)) {
                    $objValidationFour = $sheet->getCell('E' . $i)->getDataValidation();
                    $objValidationFour->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidationFour->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidationFour->setAllowBlank(false);
                    $objValidationFour->setShowInputMessage(true);
                    $objValidationFour->setShowErrorMessage(true);
                    $objValidationFour->setShowDropDown(true);
                    $objValidationFour->setErrorTitle('Input error');
                    $objValidationFour->setError('Value is not in list.');
                    $objValidationFour->setPromptTitle('Pick from list');
                    $objValidationFour->setFormula1('"'.$ages.'"');

                    $objValidation = $sheet->getCell('F' . $i)->getDataValidation();
                    $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list.');
                    $objValidation->setPromptTitle('Pick from list');
                    $objValidation->setFormula1('"'.$gender.'"');

                    $objValidationTwo = $sheet->getCell('G' . $i)->getDataValidation();
                    $objValidationTwo->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidationTwo->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidationTwo->setAllowBlank(false);
                    $objValidationTwo->setShowInputMessage(true);
                    $objValidationTwo->setShowErrorMessage(true);
                    $objValidationTwo->setShowDropDown(true);
                    $objValidationTwo->setErrorTitle('Input error');
                    $objValidationTwo->setError('Value is not in list.');
                    $objValidationTwo->setPromptTitle('Pick from list');
                    $objValidationTwo->setFormula1('"'.$relations.'"');

                    $objValidationThree = $sheet->getCell('L' . $i)->getDataValidation();
                    $objValidationThree->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidationThree->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidationThree->setAllowBlank(false);
                    $objValidationThree->setShowInputMessage(true);
                    $objValidationThree->setShowErrorMessage(true);
                    $objValidationThree->setShowDropDown(true);
                    $objValidationThree->setErrorTitle('Input error');
                    $objValidationThree->setError('Value is not in list.');
                    $objValidationThree->setPromptTitle('Pick from list');
                    $objValidationThree->setFormula1('"'.$configs.'"');

                    $i++;
                }
            });
        })->export('xlsx');
    }

    /**
     * Export fee of Welfare
     *
     * @param int $welId
     * @param string $filter
     */
    public function exportFee($welId, $filter)
    {
        $welfare = Event::find($welId);
        if (!$welfare) {
            return redirect()->route('welfare::welfare.event.index')
                    ->withErrors(trans('welfare::view.Not Found Welfare'));
        }
        $headerArray = [
            trans('welfare::view.Join_number_plan'),
            trans('welfare::view.Amount paid by staff'),
            trans('welfare::view.The amount the company assists'),
            trans('welfare::view.Total amount'),
        ];
        if ($filter == 'expected') {
            $feeExpected = WelfareFee::exportFee($welId, false);
            $nameSheet = 'Chi phí dự kiến';
            $namefile = 'ChiPhiDuKien';
            $actual = false;
            $feeMore = WelfareFee::getFeeEstimate($welId);
        } elseif ($filter == 'actual') {
            $feeExpected = WelfareFee::exportFee($welId, true);
            $nameSheet = 'Chi phí thực tế';
            $namefile = 'ChiPhiThucTe';
            $actual = true;
            $feeMore = WelFeeMore::totalFeeWelfare($welId);
        }

        array_unshift($feeExpected, $headerArray);
        Excel::create($namefile.'_'. date('Y.m.d'), function($excel) use($feeExpected, $nameSheet, $actual, $feeMore) {
            $excel->sheet($nameSheet, function($sheet) use($feeExpected, $actual, $feeMore) {
                $countArray =  count($feeExpected);
                $sheet->fromArray($feeExpected, null, 'B1', true, false);
                $sheet->setFontFamily('Arial');
                $sheet->setFontSize(10);

                $sheet->row(1, function($row) {
                    $row->setAlignment('center');
                    $row->setBackground('#ebebe0');
                    $row->setBorder('thin', 'thin', 'thin', 'thin');
                    $row->setFont([
                        'size' => '11',
                        'bold' => true
                    ]);
                });
                $sheet->setAutoSize(true);

                $titleArray = [
                    Lang::get('welfare::view.Official employee'),
                    Lang::get('welfare::view.Trial employee'),
                    Lang::get('welfare::view.Interns'),
                    Lang::get('welfare::view.Welfare Employee Attach'),
                ];
                $i = 2;
                $k = 0;
                while ($i <= $countArray) {
                    $sheet->cell('A'. $i, function($cell) use($titleArray, $k) {
                        $cell->setValue($titleArray[$k]);
                        $cell->setFont([
                            'size' => '11',
                            'bold' => true
                        ]);
                    });
                    $sheet->cell('E'. $i, function ($cell) use($i) {
                        $cell->setValue('=B'. $i .'*SUM(C'. $i .':D' . $i . ')');
                    });
                    $i++;
                    $k++;
                }
                $sheet->cell('A'.($countArray + 1), function ($cell)  use($actual) {
                    if(!$actual) {
                        $cell->setValue(Lang::get('welfare::view.Cost estimates'));
                    } else {
                        $cell->setValue(Lang::get('welfare::view.Extra cost'));
                    }
                    $cell->setBorder('none', 'solid', 'solid', 'solid');
                    $cell->setFont([
                            'size' => '11',
                            'bold' => true
                        ]);
                });
                $sheet->cell('E' . ($countArray + 1), function ($cell) use($feeMore) {
                    $cell->setValue($feeMore);
                });
                $sheet->cell('A'.($countArray + 2), function ($cell)  {
                    $cell->setValue(Lang::get('welfare::view.Total'));
                    $cell->setBorder('none', 'solid', 'solid', 'solid');
                    $cell->setFont([
                            'size' => '11',
                            'bold' => true
                        ]);
                });
                $sheet->cell('B'.($countArray + 2), function ($cell) use($countArray) {
                    $cell->setValue('=SUM(B2:B' . ($countArray + 1) . ')');
                });
                $sheet->cell('E'.($countArray + 2), function ($cell) use($countArray) {
                    $cell->setValue('=SUM(E2:E' . ($countArray + 1) . ')');
                });
                $sheet->cell('C'.($countArray + 2), function ($cell) use($countArray) {
                    $cell->setValue('=SUM(C2:C' . ($countArray + 1) . ')');
                });
                $sheet->cell('D'.($countArray + 2), function ($cell) use($countArray) {
                    $cell->setValue('=SUM(D2:D' . ($countArray + 1) . ')');
                });

                $sheet->setColumnFormat([
                    'C2:C'. ($countArray + 2) => self::FORMAT_TYPE_DATA,
                    'D2:D'. ($countArray + 2) => self::FORMAT_TYPE_DATA,
                    'E2:E'. ($countArray + 2) => self::FORMAT_TYPE_DATA,
                ]);

                $numberCell = max(array_map('count', $feeExpected));
                $endCell    = str_repeat(chr(($numberCell + 1) % 26 + 64), ceil(($numberCell + 1)  / 26)) . (count($feeExpected)+ 2);
                $sheet->setBorder('A1:' . $endCell, 'thin');

            });
        })->download('xlsx');
    }
}
