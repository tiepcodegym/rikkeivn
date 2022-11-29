<?php
namespace Rikkei\Sales\Seeds;

use Illuminate\Database\Seeder;
use Rikkei\Sales\Model\CssCategory;
use DB;

class CssQuestionEnglishSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return true;  
        $dataDemo = [

         //PROJECT BASE
            [
                'content' => 'How would you rate us at understanding the project requirements?',
                'category_id' => CssCategory::getIdCateByName('About Project Requirement Understanding'),
                'sort_order' => 1
            ],
            [
                'content' => 'Have required changes been fully updated in related products? (Project requirements, source code, test documents, etc)',
                'category_id' => CssCategory::getIdCateByName('About Project Requirement Understanding'),
                'sort_order' => 2
            ],
            [
                'content' => 'Was the content of the design document created by the project team clear?',
                'category_id' => CssCategory::getIdCateByName('About Designing phase'),
                'sort_order' => 3
            ],
            [
                'content' => 'Was the design solution appropriate?',
                'category_id' => CssCategory::getIdCateByName('About Designing phase'),
                'sort_order' => 4
            ],
            [
                'content' => 'Was the source code described coherently (complied with coding conventions and with sufficient comments?)',
                'category_id' => CssCategory::getIdCateByName('About Coding phase'),
                'sort_order' => 5
            ],
            [
                'content' => 'Did the source code reflect all the required features ?',
                'category_id' => CssCategory::getIdCateByName('About Coding phase'),
                'sort_order' => 6
            ],
            [
                'content' => 'How would you rate our unit test quality?',
                'category_id' => CssCategory::getIdCateByName('About Coding phase'),
                'sort_order' => 7
            ],
            [
                'content' => 'How you would rate our general testing quality?',
                'category_id' => CssCategory::getIdCateByName('About the General Testing'),
                'sort_order' => 8
            ],
            [
                'content' => 'Did our testing execution quality meet your expectations?',
                'category_id' => CssCategory::getIdCateByName('About the General Testing'),
                'sort_order' => 9
            ],
            [
                'content' => 'How would you rate our support at bug fixing? i.e Did we manage to respond in a timely manner or have the bug fixed and carried out sufficient testing?',
                'category_id' => CssCategory::getIdCateByName('About the General Testing'),
                'sort_order' => 10
            ],
            [
                'content' => 'How would you rate our response time?',
                'category_id' => CssCategory::getIdCateByName('About our Customer Support throughout the project'),
                'sort_order' => 11
            ],
            [
                'content' => 'Were our team\'s suggested solutions appropriate?',
                'category_id' => CssCategory::getIdCateByName('About our Customer Support throughout the project'),
                'sort_order' => 12
            ],
            [
                'content' => 'How do you think about the project team\'s degree of working in compliance with the schedule ? (Whether the progress was as scheduled, whether the delivery was as expected, etc.)',
                'category_id' => CssCategory::getIdCateByName('Project management'),
                'sort_order' => 13
            ],
            [
                'content' => 'How would you rate our team\'s work report?.',
                'category_id' => CssCategory::getIdCateByName('Project management'),
                'sort_order' => 14
            ],
            [
                'content' => 'How was the task and risk management ability of the project team?',
                'category_id' => CssCategory::getIdCateByName('Project management'),
                'sort_order' => 15
            ],
            [
                'content' => 'How was the enthusiasm of the project team?',
                'category_id' => CssCategory::getIdCateByName('Project management'),
                'sort_order' => 16
            ],
            [
                'content' => 'Did you see our effort to better the relationship between the two companies?',
                'category_id' => CssCategory::getIdCateByName('Project management'),
                'sort_order' => 17
            ],
            [
                'content' => ' How do you think about our team technical skills? (the understanding of project requirements , the ability to design and code,...)',
                'category_id' => CssCategory::getIdCateByName('BA\'s Evaluation'),
                'sort_order' => 18
            ],
            [
                'content' => 'How do you think about our team\'s English level? (English in email, in conference, in translating document, etc ）',
                'category_id' => CssCategory::getIdCateByName('BA\'s Evaluation'),
                'sort_order' => 19
            ],
            [
                'content' => 'How do you think about  our problem solving skills and project support capabilities?',
                'category_id' => CssCategory::getIdCateByName('BA\'s Evaluation'),
                'sort_order' => 20
            ],
            [
                'content' => 'Overall, were you satisfied with our products and services?',
                'category_id' => CssCategory::getIdCateByName('Project base'),
                'sort_order' => 1,
                'is_overview_question' => 1
            ],
            
        //Project OSDC
            
            [
                'content' => 'Are our staffs\' abilities suitable for assigned tasks?',
                'category_id' => CssCategory::getIdCateByName('Abilities/Skills'),
                'sort_order' => 1
            ],
            [
                'content' => 'How would you rate our team technical skills? i.e understanding of project requirements, planning or actual coding skills...',
                'category_id' => CssCategory::getIdCateByName('Abilities/Skills'),
                'sort_order' => 2
            ],
            [
                'content' => 'How would you rate our problem solving skills and project support capabilities?',
                'category_id' => CssCategory::getIdCateByName('Abilities/Skills'),
                'sort_order' => 3
            ],
            [
                'content' => 'How would you rate our staffs\' team working?',
                'category_id' => CssCategory::getIdCateByName('Abilities/Skills'),
                'sort_order' => 4
            ],
            [
                'content' => 'How would you rate us in terms of working with deadlines? i.e did we manage to deliver on time and at the promised quality?',
                'category_id' => CssCategory::getIdCateByName('Abilities/Skills'),
                'sort_order' => 5
            ],
            [
                'content' => 'How would you rate our team\'s work report ?',
                'category_id' => CssCategory::getIdCateByName('Abilities/Skills'),
                'sort_order' => 6
            ],
            [
                'content' => 'Have required changes been fully reflected in related products?',
                'category_id' => CssCategory::getIdCateByName('Abilities/Skills'),
                'sort_order' => 7
            ],
            [
                'content' => 'Did we manage to fully complete the given job?',
                'category_id' => CssCategory::getIdCateByName('The result of work'),
                'sort_order' => 8
            ],
            [
                'content' => 'How satisfied are you with the work delivered?',
                'category_id' => CssCategory::getIdCateByName('The result of work'),
                'sort_order' => 9
            ],
            [
                'content' => 'Did we manage to keep up with or respond in a timely manner with the tasks given?',
                'category_id' => CssCategory::getIdCateByName('The result of work'),
                'sort_order' => 10
            ],
            [
                'content' => 'Please evaluate the sense of responsibility and commitment for the work of our staff.',
                'category_id' => CssCategory::getIdCateByName('Sense of responsibility'),
                'sort_order' => 11
            ],
            [
                'content' => 'How do you think about our staff \'s sense of responsibility toward the benefits of the two companies and the ongoing project ?',
                'category_id' => CssCategory::getIdCateByName('Sense of responsibility'),
                'sort_order' => 12
            ],
            [
                'content' => 'How do you think about our staff\' ability to overcome difficulties at work as well as in personal life ? (Leaving early from work even when their work is not completed, late arrivals to work , absenteeism, etc)',
                'category_id' => CssCategory::getIdCateByName('Sense of responsibility'),
                'sort_order' => 13
            ],
            [
                'content' => 'Did our company staff work effectively  within the working hours?',
                'category_id' => CssCategory::getIdCateByName('Labor discipline'),
                'sort_order' => 14
            ],
            [
                'content' => 'Did our staff strictly follow your company rules and regulations? (Security, report, etc.)',
                'category_id' => CssCategory::getIdCateByName('Labor discipline'),
                'sort_order' => 15
            ],
            [
                'content' => 'How was the English level at writing email?',
                'category_id' => CssCategory::getIdCateByName('About English communication ability'),
                'sort_order' => 16
            ],
            [
                'content' => 'How was the quality of documents translated from English?',
                'category_id' => CssCategory::getIdCateByName('About English communication ability'),
                'sort_order' => 17
            ],
            [
                'content' => 'How was the English level used in telephone conference, TV conference, chat, etc?',
                'category_id' => CssCategory::getIdCateByName('About English communication ability'),
                'sort_order' => 18
            ],
            [
                'content' => 'How do you think about the person in charge of the OSDC? (project management ability, resource adjustment ability, etc )',
                'category_id' => CssCategory::getIdCateByName('About the person in charge of OSDC'),
                'sort_order' => 19
            ],
            [
                'content' => 'Did you see our effort to improve the relationship between the two companies?',
                'category_id' => CssCategory::getIdCateByName('About the person in charge of OSDC'),
                'sort_order' => 20
            ],
            [
                'content' => 'Overall, are you satisfied with our products and services?',
                'category_id' => CssCategory::getIdCateByName('OSDC'),
                'sort_order' => 1,
                'is_overview_question' => 1
            ],
        ];
        DB::beginTransaction();
        try {
            $count = DB::table('css_question')->count(); 
            if($count >= 42){
                foreach ($dataDemo as $data) {
                    if (! DB::table('css_question')->select('id')->where('content', $data['content'])->get()) {
                        DB::table('css_question')->insert($data);
                    }
                }
            }
            DB::commit();
        } catch(Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
