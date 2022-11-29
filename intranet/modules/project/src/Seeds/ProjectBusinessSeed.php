<?php
namespace Rikkei\Project\Seeds;

use Rikkei\Core\Seeds\CoreSeeder;
use Rikkei\Project\Model\ProjectBusiness;


class ProjectBusinessSeed extends CoreSeeder
{
    const BUSINESS_DOMAIN_HEALTHCARE = 1;
    const BUSINESS_DOMAIN_BAKING = 2;
    const BUSINESS_DOMAIN_MANUFACTURING = 3;
    const BUSINESS_DOMAIN_AUTOMOVE = 4;
    const BUSINESS_DOMAIN_ECOMMERCE = 5;
    const BUSINESS_DOMAIN_MEDIA = 6;
    const BUSINESS_DOMAIN_UTILITY = 7;
    const BUSINESS_DOMAIN_LOGISTIC = 8;
    const BUSINESS_DOMAIN_OTHERS = 9;

    /**
     * Run the database seeds.
     *
     * @return bool
     * @throws \Exception
     */
    public function run()
    {
        if ($this->checkExistsSeed()) {
            return true;
        }
        $arrBusiness = self::lablelBusiness();
        $arrInsert = [];
        foreach ($arrBusiness as $key => $value) {
            if($key == self::BUSINESS_DOMAIN_OTHERS) {
                $arrInsert[] = [
                    'business_name' => $value,
                    'is_other_type' => 1,
                ];
            } else {
                $arrInsert[] = [
                    'business_name' => $value,
                    'is_other_type' => 0,
                ];
            }
        }
        ProjectBusiness::insert($arrInsert);

        $this->insertSeedMigrate();
    }

    /**
     * label of state
     *
     * @return array
     */
    public static function lablelBusiness() {
        return [
            self::BUSINESS_DOMAIN_HEALTHCARE => 'Healthcare',
            self::BUSINESS_DOMAIN_BAKING => 'Banking and Finance',
            self::BUSINESS_DOMAIN_MANUFACTURING => 'Manufacturing',
            self::BUSINESS_DOMAIN_AUTOMOVE => 'Automotive',
            self::BUSINESS_DOMAIN_ECOMMERCE => 'Ecommerce',
            self::BUSINESS_DOMAIN_MEDIA => 'Media-Entertainance',
            self::BUSINESS_DOMAIN_UTILITY => 'Utility',
            self::BUSINESS_DOMAIN_LOGISTIC => 'Logistics',
            self::BUSINESS_DOMAIN_OTHERS => 'Other'
        ];
    }
}
