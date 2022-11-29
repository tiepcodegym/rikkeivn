<?php

namespace Rikkei\Team\View;

Class TeamConst
{
    /**
     * BOD
     */
    const CODE_BOD = 'bod';

    /**
     * Ban Kiem Soat
     */
    const CODE_PQA = 'pqa';

    /**
     * Rikkei - Hanoi
     */
    const CODE_HANOI = 'hanoi';
    const CODE_HN_BCN = 'hanoi_bcn';

    /* Rikkei - Hanoi -> Resource Assuarance */
    const CODE_HN_RESOURCE_ASSUARANCE = 'hanoi_ra';
    const CODE_HN_TRAINING = 'hanoi_traning';
    const CODE_HN_HR = 'hanoi_hr';

    /* Rikkei - Hanoi -> Khoi Kinh Doanh */
    const CODE_HN_KKD = 'hanoi_kkd';
    const CODE_HN_SALES = 'hanoi_sale';

    /* Rikkei - Hanoi -> Khoi Van Phong */
    const CODE_HN_HCTH = 'hanoi_hcth';
    const CODE_HN_HC = 'hanoi_hc';
    const CODE_HN_PR = 'hanoi_pr';

    /* Rikkei - Hanoi -> Khoi CNTT */
    const CODE_HN_CNTT = 'hanoi_cntt';
    const CODE_HN_PRODUCTION = 'hanoi_production';
    const CODE_HN_IT = 'hanoi_it';

    /* Rikkei - Hanoi -> Khối sản xuất */
    const CODE_HN_DEV = 'hanoi_dev';
    const CODE_HN_D0 = 'hanoi_d0';
    const CODE_HN_D1 = 'hanoi_d1';
    const CODE_HN_D2 = 'hanoi_d2';
    const CODE_HN_D3 = 'hanoi_d3';
    const CODE_HN_D5 = 'hanoi_d5';
    const CODE_HN_D6 = 'hanoi_d6';
    const CODE_HN_D8 = 'hanoi_d8';
    const CODE_HN_VD = 'hanoi_vd';
    const CODE_HN_GD = 'hanoi_gd';
    const CODE_HN_QA = 'hanoi_qa';

    /* Rikkei - Hanoi -> Systena */
    const CODE_HN_SYSTENA = 'hanoi_systena';

    /**
     * Rikkei - Danang
     */
    const CODE_DANANG = 'danang';
    const CODE_DN_IT = 'danang_it';
    const CODE_DN_HCTH = 'danang_hcth';
    const CODE_DN_DEV = 'danang_dev';
    const CODE_DN_D0 = 'danang_d0';
    const CODE_DN_D1 = 'danang_d1';
    const CODE_DN_D2 = 'danang_d2';
    const CODE_DN_D3 = 'danang_dn3';

    /**
     * Rikkei - Japan
     */
    const CODE_JAPAN = 'japan';
    const CODE_JAPAN_HCTH = 'japan_hcth';
    const CODE_JAPAN_SALE = 'japan_sale';
    const CODE_JAPAN_DEV = 'japan_dev';

    /**
     * Rikkei - HCM
     */
    const CODE_HCM = 'hcm';
    const CODE_HCM_PTPM = 'hcm_ptpm';
    const CODE_HCM_IT = 'hcm_it';
    const CODE_HCM_HCTH = 'hcm_hcth';
    const CODE_RS = 'rs';

    /**
     * Rikkei - AI
     */
    const CODE_AI = 'ai';

    /**
     * get code of dev teams and self children
     *     self: get it self
     *     child: get children
     * @return array
     */
    public static function getTeamTreeDev()
    {
        return [
            self::CODE_HN_DEV => [
                'self' => 0,
                'child' => 1
            ],
            self::CODE_HN_SYSTENA => [
                'self' => 1,
                'child' => 1
            ],
            self::CODE_DANANG => [
                'self' => 1,
                'child' => 0
            ],
            self::CODE_JAPAN => [
                'self' => 1,
                'child' => 0
            ]
        ];
    }
}
