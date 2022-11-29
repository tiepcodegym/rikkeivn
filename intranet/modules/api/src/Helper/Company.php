<?php

namespace Rikkei\Api\Helper;

use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\Sales\Model\Company as CompanyModel;
use Rikkei\Team\Model\Employee;

/**
 * Description of Contact
 *
 * @author lamnv
 */
class Company extends BaseHelper
{
    public function __construct() {
        $this->model = CompanyModel::class;
    }


    public function getCompanyByCrmId($crmIds)
    {
        $tbl = CompanyModel::getTableName();
        $tblEmp = Employee::getTableName();


        return CompanyModel::select(
            "{$tbl}.id",
            "{$tbl}.company",
            "{$tbl}.name_ja",
            "{$tbl}.homepage",
            "{$tbl}.address",
            "{$tbl}.phone",
            "{$tbl}.fax",
            "{$tbl}.note",
            "{$tbl}.created_by",
            "{$tbl}.contract_security",
            "{$tbl}.contract_quality",
            "{$tbl}.contract_other",
            "{$tbl}.type",
            "{$tbl}.crm_id",
            'manager_employee.email as manager_email',
            'sale_support.email as sale_support_email',
            'employee_create.email as employee_create_email'
        )
        ->leftJoin("{$tblEmp} as manager_employee", 'manager_employee.id', '=', "{$tbl}.manager_id")
        ->leftJoin("{$tblEmp} as sale_support", 'sale_support.id', '=', "{$tbl}.sale_support_id")
        ->leftJoin("{$tblEmp} as employee_create", 'employee_create.id', '=', "{$tbl}.created_by")
        ->whereIn("{$tbl}.crm_id", $crmIds)
        ->get();
    }

    public function getCompany($params)
    {   
        $collection = CompanyModel::select(
            'cust_companies.id', 
            'cust_companies.company AS company_name', 
            'employ_one.email AS manager_email',
            'employ_two.email AS support_email',
            'employ_one.name AS manager_name',
            'employ_two.name AS support_name'
        )
        ->join('employees AS employ_one', 'cust_companies.manager_id', '=' ,'employ_one.id')
        ->leftJoin( 'employees AS employ_two', 'cust_companies.sale_support_id', '=',  'employ_two.id');
        if(!empty($params['companies_id']) ){
            $collection = $collection->whereIn("cust_companies.id", $params['companies_id']);
        }  
        $collection = $collection->get();

        return $collection;
    }
}