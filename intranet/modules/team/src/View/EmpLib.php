<?php

namespace Rikkei\Team\View;

use Rikkei\Core\View\BaseHelper;

class EmpLib
{
    use BaseHelper;

    /**
     * folk
     */
    public function folk()
    {
        return [
            1 => 'Chơ Ro',
            2 => 'Chu Ru',
            3 => 'Co',
            4 => 'Cơ Ho',
            5 => 'Cờ Lao',
            6 => 'Cống',
            7 => 'Dao',
            8 => 'Gia Rai',
            9 => 'Giáy',
            10 => 'Gié Triêng',
            11 => 'Hà Nhỉ',
            12 => 'Hoa',
            13 => 'Hrê',
            14 => 'Kháng',
            15 => 'Khơ Mú',
            16 => 'La Chí',
            17 => 'La Ha',
            18 => 'Mạ',
            19 => 'Mảng',
            20 => "M'Nông",
            21 => 'Mông ',
            22 => 'Mường',
            23 => 'Ngái',
            24 => 'Nùng',
            25 => 'Ơ Đu',
            26 => 'Pà Thẻn',
            27 => 'Phù Lá',
            28 => 'Pu Péo',
            29 => 'Ra Glai',
            30 => 'Rơ Măm',
            31 => 'Sán Chay',
            32 => 'Sán Dỉu',
            33 => 'Si La',
            34 => 'Tà Ôi',
            35 => 'Thái',
            36 => 'Thổ',
            37 => 'Xinh Mun',
            38 => 'Xơ Đăng',
            38 => 'Xtiêng',
            40 => 'Bru - Vân Kiều',
            41 => 'Chăm',
            42 => 'Chứt',
            43 => 'Ê Đê',
            44 => 'Khmer',
            45 => 'Ba Na',
            46 => 'Brâu',
            47 => 'Cơ Tu',
            49 => 'Lô Lô',
            49 => 'Tày',
            50 => 'Bố Y',
            51 => 'La Hú',
            52 => 'Lào',
            53 => 'Lự',
            54 => 'Kinh',
        ];
    }

    /**
     * relig
     */
    public function relig()
    {
        return [
            0 => 'Không',
            1 => 'Cao Đài',
            2 => 'Hồi giáo',
            3 => 'Tin lành',
            4 => 'Phật giáo',
            5 => 'Phật giáo Hòa Hảo',
            6 => 'Thiên Chúa giáo',
        ];
    }
}