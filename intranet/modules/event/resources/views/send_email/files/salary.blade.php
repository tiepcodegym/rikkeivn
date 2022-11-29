<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            .salary_table {
                border-collapse: collapse;
                border: 2px solid #767676;
                width: 100%;
            }
            .salary_table tr th, .salary_table tr td {
                padding: 8px 10px;
                border: 1px solid #767676;
                text-align: left;
                font-size: 0.9em;
            }
        </style>
    </head>
    <body>

        <?php
        use Rikkei\Event\View\ViewEvent;
        ?>

        <?php
        $breakInfo = 5;
        $headingRow1 = $collectCols['2'];
        $totalCol = 0;
        if ($collectCols) {
            foreach ($collectCols as $columns) {
                $maxIdx = max(array_keys($columns));
                if ($maxIdx > $totalCol) {
                    $totalCol = $maxIdx;
                }
            }
        }
        ?>
        
        <h4 style="font-size: 1.07em; text-align: center; text-transform: uppercase; font-weight: 400;">{{ $subject }}</h4>

        <h4 style="font-size: 1em;">I. Thông tin chung</h4>
        <table class="salary_table">
            <tbody>
                @for ($i = 0; $i <= $breakInfo; $i++)
                    @if (isset($emailData[$i]) && $emailData[$i])
                    <tr>
                        <td>{{ ucfirst(ViewEvent::getValueSalary($collectCols, $i)) }}</td>
                        <td style="text-align: right">{{ ViewEvent::formatMoney($emailData[$i]) }}</td>
                    </tr>
                    @endif
                @endfor
            </tbody>
        </table>

        <?php
        $offsetTimeStart = 6;
        $offsetTimeEnd = 8;
        $arrFloatIdxs = [$itemIndexs['ot'], $itemIndexs['cong_thu_viec'], $itemIndexs['cong_chinh_thuc']];
        ?>
 
        <h4 style="font-size: 1em;">
            II. Thông tin ngày chính thức xem trên rikkei.vn
            <?php /*
            II. Thông tin  công hưởng lương 
            <a style="float: right; font-size: 0.93em; font-weight: 400;"
            href="{{ route('manage_time::profile.timekeeping') }}">chi tiết công xem trên rikkei.vn</a>
            */?>
        </h4>
        <div style="clear: both;"></div>
        <table class="salary_table">
            <tbody>
                @for ($i = $offsetTimeStart; $i <= $offsetTimeEnd; $i++)
                    @if (isset($emailData[$i]) && $emailData[$i])
                    <?php
                    $colTitle = ViewEvent::getValueSalary($collectCols, $i);
                    if ($colTitle == 'Chính thức') {
                        $colTitle = 'Tổng công chính thức';
                    }
                    if ($colTitle == 'Thử việc') {
                        $colTitle = 'Tổng công thử việc';
                    }
                    if ($colTitle == 'OT') {
                        $colTitle = 'Tổng số giờ OT hưởng lương (h)';
                    }
                    ?>
                    <tr>
                        <td>{{ ucfirst($colTitle) }}</td>
                        <td style="text-align: right">{{ in_array($i, $arrFloatIdxs) ? $emailData[$i] : ViewEvent::formatMoney($emailData[$i]) }}</td>
                    </tr>
                    @endif
                @endfor
            </tbody>
        </table>

        <?php
        $offsetRow = 9;
        $offsetRow1 = $offsetRow + $headingRow1[$offsetRow]['cols'] - 1;
        $offsetRow2 = $offsetRow1 + 1 + $headingRow1[$offsetRow1 + 2]['cols'];
        $rowsDataSalary = [
            [
                'title' => '1. Các khoản thu nhập tiền lương tiền công (vnđ)',
                'range_index' => [$offsetRow, $offsetRow1],
                'idx_sum' => $offsetRow1 + 1
            ],
            [
                'title' => '2. Các khoản giảm trừ (vnđ)',
                'range_index' => [$offsetRow1 + 2, $offsetRow2],
                'idx_sum' => $offsetRow2 + 1
            ],
            [
                'title' => '3. Thực lĩnh (1-2) (vnđ)',
                'range_index' => [],
                'idx_sum' => ($offsetRow2 + 2) >= $totalCol ? $totalCol : ($offsetRow2 + 2)
            ]
        ];
        //if has more colums
        $titleNo = 3;
        if ($totalCol > $offsetRow2 + 2) {
            for ($col = $offsetRow2 + 3; $col <= $totalCol; $col++) {
                $rowsDataSalary[] = [
                    'title' => (++$titleNo) . '. ' . ucfirst($headingRow1[$col]['title']),
                    'range_index' => [],
                    'idx_sum' => $col,
                ];
            }
        }
        ?>

        <h4 style="font-size: 1em;">III. Nội dung</h4>
        <table class="salary_table">
            <tbody>
                @foreach ($rowsDataSalary as $rows)
                    <tr>
                        <th>{{ $rows['title'] }}</th>
                        <th style="text-align: right;">{{ isset($emailData[$rows['idx_sum']]) ? ViewEvent::formatMoney($emailData[$rows['idx_sum']]) : null }}</th>
                    </tr>
                    @if ($rows['range_index'])
                        @for ($i = $rows['range_index'][0]; $i <= $rows['range_index'][1]; $i++)
                            @if (isset($emailData[$i]) && $emailData[$i])
                            <tr>
                                <td>{{ ucfirst(ViewEvent::getValueSalary($collectCols, $i)) }}</td>
                                <td style="text-align: right;">{{ in_array($i, $arrFloatIdxs) ? $emailData[$i] : ViewEvent::formatMoney($emailData[$i]) }}</td>
                            </tr>
                            @endif
                        @endfor
                    @endif
                @endforeach
            </tbody>
        </table>

    </body>
</html>


 



