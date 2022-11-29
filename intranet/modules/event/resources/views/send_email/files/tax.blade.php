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
        $breakInfo = 2;
        $totalCol = 0;
        if ($collectCols) {
            foreach ($collectCols as $columns) {
                $maxIdx = max(array_keys($columns));
                if ($maxIdx > $totalCol) {
                    $totalCol = $maxIdx;
                }
            }
        }
        $totalCol++;
        ?>
        
        <h4 style="font-size: 1.07em; text-align: center; text-transform: uppercase; font-weight: 400;">{{ $subject }}</h4>

        <h4 style="font-size: 1em;">I. Thông tin chung</h4>
        <table class="salary_table">
            <tbody>
                @for ($i = 0; $i <= $breakInfo; $i++)
                    @if (isset($emailData[$i]) && $emailData[$i])
                    <tr>
                        <td>{{ ucfirst(ViewEvent::getValueSalary($collectCols, $i)) }}</td>
                        <td style="text-align: right">{{ ViewEvent::formatMoney($emailData[$i], $isFormatNumber) }}</td>
                    </tr>
                    @endif
                @endfor
            </tbody>
        </table>

        <?php
        $offsetTimeStart = 3;
        $offsetTimeEnd = $totalCol;
        ?>
 
        <h4 style="font-size: 1em;">II. Nội dung</h4>
        <table class="salary_table">
            <tbody>
                @for ($i = $offsetTimeStart; $i <= $offsetTimeEnd; $i++)
                    @if (isset($emailData[$i]) && $emailData[$i])
                    <tr>
                        <td>{{ ucfirst(ViewEvent::getValueSalary($collectCols, $i)) }}</td>
                        <td style="text-align: right">{{ ViewEvent::formatMoney($emailData[$i], $isFormatNumber) }}</td>
                    </tr>
                    @endif
                @endfor
            </tbody>
        </table>

    </body>
</html>


 



