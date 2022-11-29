<?php
namespace Rikkei\Sales\Seeds;

use DB;
use Carbon\Carbon;
use Rikkei\Sales\Model\Css;
use Rikkei\Sales\Model\CssResult;
use Rikkei\Sales\Model\CssCategory;
use Rikkei\Sales\Model\CssQuestion;

class CssTemplateDefaultAddQsExplainSeeder extends \Rikkei\Core\Seeds\CoreSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkExistsSeed(1)) {
            return true;
        }
        
        DB::beginTransaction();
        try {
            $data = [
                '42' => [
                    '121' => [
                        'explain' => '5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied'
                    ],
                ],
                '43' => [],
                '44' => [
                    '122' => [
                        'explain' => "5=>Good capability of understanding and analyzing the user requirement, no problems occur
                        4=>Quite good capability of understanding and analyzing, Q&A applied for clarification, no impact on project's progress
                        3=>Not good capability of understanding and analyzing, but Q&A applied for clarification, and acceptable
                        2=>A few problems on understanding and analyzing, Q&A applied many times for confirmation, delay and re-work arise
                        1=>Many problems occur during understanding and analyzing, big impact on project's progress and quality
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '123' => [
                        'explain' => '5=>The content is so clear that the project can run smoothly without problems
                        4=>There is a little bit of ambiguous content, but the project team has clarified it through Q&A
                        3=>There is a little bit of ambiguous content, but the re-work does not arise
                        2=>The delay and re-work arise from the ambiguous content 
                        1=>Big impact on the progress and quality mainly due to the inadequate specification
                        0=>No interest because the item is not important or not applicable in the project'
                    ],
                    '124' => [
                        'explain' => "5=>Specification is completed on time
                        4=>Specification is completed little late but no impact on the project's deadline
                        3=>Specification is completed late, some tasks get in trouble but no big impact
                        2=>The delay and re-work arise from the late completion of Specification
                        1=>Big impact on the progress and quality mainly due to the late completion of Specification
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '45' => [
                    '125' => [
                        'explain' => '5=>The content is so clear that the project can run smoothly without problems
                        4=>There is a little bit of ambiguous content, but project team has clarified it through Q&A
                        3=>There is a little bit of ambiguous content, but the re-work does not arise
                        2=>The delay and re-work arise from the ambiguous content 
                        1=>Big impact on the progress and quality mainly due to the inadequate specification
                        0=>No interest because the item is not important or not applicable in the project'
                    ],
                    '126' => [
                        'explain' => "5=>The solutions are so good that the project can run smoothly without problems
                        4=>A few troubles in the solution, but the project team has managed them timely and no impact 
                        3=>Troubles in the solution, but the correction cost is not high and totally acceptable																											
                        2=>The not good solution make the team's correction cost high, but no impact on the customer																											
                        1=>Big impact on the customer mainly due to the design solution																											
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '127' => [
                        'explain' => "5=>Design is completed on time
                        4=>Design is completed little late but no impact on project's deadline
                        3=>Design is completed late, some tasks get in trouble but no big impact
                        2=>The delay and re-work arise from the late completion of Design
                        1=>Big impact on the progress and quality mainly due to the late completion of Design
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '46' => [
                    '128' => [
                        'explain' => "5=>Source code follow the Coding convention, the code comments are fully available if requested
                        4=>Some of source codes do not follow the coding convention, but the code comments are fully available
                        3=>Some of source codes do not follow the coding convention, and the code comments are not fully availableSome of source codes do not follow the coding convention, and the code comments are not fully available
                        2=>Most of source codes do not follow the coding convention, and lack of the code comments
                        1=>All the source codes do not follow the coding convention, and have no the code comments
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '129' => [
                        'explain' => "5=>Source code fully represents the functions mentioned in Specification & Design, the program can run smoothly
                        4=>Source code represents the functions in Specification & Design, but some are not represented adequately
                        3=>Source code doest not fully represent the requirements, but it does not waste much effort to fix
                        2=>Source code doest not fully represent the requirements, it takes much effort to fix, but no impact on customer
                        1=>Source code doest not fully represent the requirements, and there is an impact on customer
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '130' => [
                        'explain' => "5=>Most of the source code has no basic bugs
                        4=>Some basic bugs, but it does not waste much effort to fix, and totally acceptable
                        3=>Many basic bugs, but it does not waste much effort to fix, and totally acceptable
                        2=>Most of the source code has basic bugs, it waste much effort to fix, but no impact on the quality & deadline
                        1=>Big impact on the progress and quality mainly due to the basic bugs
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '47' => [
                    '131' => [
                        'explain' => "5=>Good test documents, no impact on the project's progress and quality
                        4=>Test documents have errors, but no impact on the project's progress and quality
                        3=>Test documents have errors, impact on the quality, but acceptable
                        2=>The test documents are not so good that they must be updated and corrected
                        1=>Not good test documents,  correction is requested, and impact on the process and quality
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '132' => [
                        'explain' => "5=>No leakage after the acceptance test by the customer, the project finishes smoothly
                        4=>The number of leakage defects after the acceptance test is in an accepted control limit, no impact on the manipulation
                        3=>The number of leakage defects after the acceptance test is in an accepted control limit, impact on the manipulation
                        2=>Delay and re-work arise from many leakages after the acceptance test, but no impact on the delivery deadline
                        1=>Many leakages arise after the acceptance test, big impact on the customer's work
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '48' => [
                    '133' => [
                        'explain' => "5=>No problems on the response time
                        4=>A few problems of the response time but no impact on the project
                        3=>Late response time, but no big impact on the project
                        2=>Late response time makes the project miss some tasks
                        1=>No feedback is available for the given requests/questions
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '134' => [
                        'explain' => "5=>The suitable solution can deal with almost the requests
                        4=>The good solution is given, but some requests have not been properly dealt with
                        3=>Customer has received the solution after confirming several times, but no impact on the project's progress
                        2=>Customer has received the solution after confirming several times, and impact on the project's progress
                        1=>Customer has not received the solution, big impact on their job
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '49' => [
                    '135' => [
                        'explain' => "5=>The content of CR is fully represented in the relevant products, and no impact on the project's progress
                        4=>Some errors are in the description, but no impact on the project's progress
                        3=>The content of CR is not described fully, the implementation is late, but the re-work does not occur
                        2=>The content of CR is not described fully, the implementation is late, and the re-work occurs
                        1=>The content of CR is not described fully, impact on the progress and quality
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '136' => [
                        'explain' => "5=>CR is completed on time
                        4=>CR is completed little late but no impact on project's deadline
                        3=>CR is completed late, some tasks get in trouble but no big impact
                        2=>The delay and re-work arise from the late completion of CR
                        1=>Big impact on the progress and quality mainly due to the late completion of CR
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '50' => [
                    '137' => [
                        'explain' => "5=>The project is strictly managed and completes on schedule
                        4=>Some mistakes in controlling the progress, but no impact on the committed plan
                        3=>Some mistakes in controlling the progress; the plan is delayed; but no impact on the customer
                        2=>The project's progress is not controlled tightly; impact on the customer's plan
                        1=>The progress is unable to control, big impact on the quality and plan of the customer
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '138' => [
                        'explain' => "5=>The report is sent on schedule and reflects correctly the project's actual status
                        4=>The report is sometimes sent late but reflects the project's actual status
                        3=>The report is sometimes sent late with few errors, but no need to update
                        2=>The report is not sent on schedule, or includes errors and must be re-made
                        1=>The report is not sent as requested
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '139' => [
                        'explain' => "5=>Good at managing risks & issues, and good preventive & corrective actions
                        4=>Some risks & issues are not managed well, but the good preventive & corrective actions are given timely
                        3=>Not good at managing risks & issues, and the preventive & corrective actions are given, no impact on the project
                        2=>Not good at managing risks & issues, no preventive & corrective actions, impact on the project
                        1=>Poor risks & issues management, and big impact on the project's process and quality
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '140' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '132' => [
                    '315' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '316' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '317' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '318' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
                '133' => [
                    '319' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '320' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                    '321' => [
                        'explain' => "5=>Satisfied
                        4=>Quite Satisfied
                        3=>Normal
                        2=>Rather unsatisfied
                        1=>Unsatisfied
                        0=>No interest because the item is not important or not applicable in the project"
                    ],
                ],
            ];
            foreach ($data as $items) {
                if (count($items) > 0) {
                    foreach ($items as $key => $qs) {
                        $question = CssQuestion::find($key);
                        if ($question && $qs['explain']) {
                            $qsExplains = preg_split('/\r\n|[\r\n]/', $qs['explain']);
                            if ($qsExplains) {
                                $ovvQsExplain = [];
                                foreach ($qsExplains as $qsExplain) {
                                    $qsExplainItem = explode('=>', $qsExplain);
                                    if (count($qsExplainItem) > 1) {
                                        $ovvQsExplain[trim($qsExplainItem[0])] = trim($qsExplainItem[1]);
                                    }
                                }
                                $overviewQuestionExplain = json_encode($ovvQsExplain);
                            }
                            $question->update([
                                'explain' => $overviewQuestionExplain
                            ]);
                        }
                    }
                }
            }

            $this->insertSeedMigrate();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }        
    }
}
