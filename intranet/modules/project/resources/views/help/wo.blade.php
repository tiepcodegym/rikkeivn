@extends('layouts.default')

@section('title')
{{ trans('project::view.Project Workorder help') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css" />
@endsection

@section('content')
<style>
    .help-wo-table-item {
        margin-bottom: 50px;
    }
    .help-wo-table-item table td .label-control {

    }
    .help-wo-table-item .help-title-table {
        background: #ffe8e1;
        padding: 5px;
        color: #003400;
        font-weight: 700;
    }
</style>
<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12">
                <h2>&#60;Project Name&#62;</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Basic info</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td>
                                    <strong class="label-control">Project code</strong>
                                </td>
                                <td colspan="3">&#60;Code of project&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Project Name</strong>
                                </td>
                                <td>&#60;Name’s project&#62;</td>
                                <td>
                                    <strong class="label-control">Project Alias</strong>
                                </td>
                                <td>&#60;Alias of project&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Group</strong>
                                </td>
                                <td>&#60;Name of group or The company&#62;</td>
                                <td>
                                    <strong class="label-control">Group Leader</strong>
                                </td>
                                <td>&#60;Project Director’s name&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Project Manager</strong>
                                </td>
                                <td>&#60;Project Leader’s name&#62;</td>
                                <td>
                                    <strong class="label-control">Project Level</strong>
                                </td>
                                <td>&#60;Group or company&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Project type</strong>
                                </td>
                                <td>&#60;Type of the project &#62;</td>
                                <td>
                                    <strong class="label-control">Project status</strong>
                                </td>
                                <td>&#60;Status of project&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Customer</strong>
                                </td>
                                <td>&#60;Customer’s name&#62;</td>
                                <td>
                                    <strong class="label-control">Salesperson</strong>
                                </td>
                                <td>&#60;Saler’s name&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Billable Effort</strong>
                                </td>
                                <td>&#60;Effort chart from customer&#62;</td>
                                <td>
                                    <strong class="label-control">Plan Effort</strong>
                                </td>
                                <td>&#60;Effort to finish project that estimate base on requirement&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Start date</strong>
                                </td>
                                <td>&#60;Time start project&#62;</td>
                                <td>
                                    <strong class="label-control">End date&#62;</strong>
                                </td>
                                <td>&#60;Time end project&#62;</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong class="label-control">Programming Language</strong>
                                </td>
                                <td colspan="3">&#60;Programming Language’s project&#62;</td>                        
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Scope and objectives</h4>
                    <div>
                        <p>Description: What is the business area of the project? </p>
                        <p>Customers provided: Who are the end-users of the software-products of the project?</p>
                        <p>What is the scope of the project? (What did we do in the project?)</p>
                        <p>What is the product, result of the project? Have customer’s acceptance criteria been stated? If no when is it planned to get?</p>
                        <p>What are an assumption and/or constraints of the project? (Include technological constraints as HW, OS, DBMS, Language...</p>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Stages</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>Stage</th>
                                    <th>Description</th>
                                    <th>Milestone Output</th>
                                    <th>Quality gate plan date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <p><strong>Initiation</strong></p>
                                        <p>
                                            &#60;Initial activities of the project as: doing Work Order, Schedule, kick off,…&#62;
                                        </p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p><strong>Definition</strong></p>
                                        <p>
                                            &#60;Do Requirement analysis&#62;
                                        </p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p><strong>Solution</strong></p>
                                        <p>
                                            &#60;Do Detail design&#62;
                                        </p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p><strong>Construction</strong></p>
                                        <p>
                                            &#60;Project development activities as: coding, testing. Assure the quality of the scope before transferring to the client&#62;
                                        </p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p><strong>Transition</strong></p>
                                        <p>
                                            &#60;Deployment to the client, client test and feedback. Project team fix leakage and test confirm leakage.&#62;
                                        </p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p><strong>Termination</strong></p>
                                        <p>
                                            &#60;Project completion activities as&#62;
                                        </p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p><strong>&#60;Add here as required&#62;</strong></p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Deliverable</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>Deliverable</th>
                                    <th>Committed date of delivery</th>
                                    <th>Stage</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td>
                                        <p>
                                            &#60;dd-mmm-yy&#62;
                                        </p>
                                    </td>                                    
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <p>
                                            &#60;dd-mmm-yy&#62;
                                        </p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>
                                        <p><strong>&#60;Add here as required&#62;</strong></p>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Team Allocation</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Position</th>
                                    <th>Account</th>
                                    <th>Programming Language</th>
                                    <th>Start date</th>
                                    <th>End date</th>
                                    <th>Effort(%)</th>
                                    <th>Resource(MM)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>PM</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Dev</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>SQA</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>BrSE</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Comtor</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>PQA</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>&#60position of member in project&#62</td>
                                    <td>&#60Account of the Project Member&#62</td>
                                    <td>&#60programming language of member&#62</td>
                                    <td>&#60start date&#62</td>
                                    <td>&#60end date&#62</td>
                                    <td>&#60% effort in project&#62</td>
                                    <td>&#60effort in project (MM)&#62</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Performance</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>Metrics</th>
                                    <th>Targeted</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Duration (Day)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Maximum Team Size (Person)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Effort Usage (MM)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-right">In which: Development (%)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-right">Management (%)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-right">Quality (%)</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Quality</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>Metrics</th>
                                    <th>Unit</th>
                                    <th>Targeted</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Effort Effectiveness</td>
                                    <td>%</td>
                                    <td>100</td>
                                    <td>Follow "Effort Effectiveness": null: 1, <=80: 3, 80-<=100: 2, 100-<=110: 1, 110-<=120: -1, 120-<=130: -2, >130: -3</td>
                                </tr>
                                <tr>
                                    <td>Customer Satisfaction</td>
                                    <td>Point</td>
                                    <td>80</td>
                                    <td>Follow Customer satisfactions value: null: 0, 90-<=100: 3, 80-<=90: 2, 70-<=80: 1, 60-<=70: 0.5, 50-<=60: -1, <=50: -2</td>
                                </tr>
                                <tr>
                                    <td>Timeliness</td>
                                    <td>70</td>
                                    <td>%</td>
                                    <td>Follow deliver value: <=40: -3, 40-<=55: -2, 55-<70: -1, =70: 0, 70-<=85: 1, 85-<100: 2, 100: 3</td>
                                </tr>
                                <tr>
                                    <td>Leakage</td>
                                    <td>5</td>
                                    <td>%</td>
                                    <td>Follow Leakage value: null: 3, <=3: 3, 3-<=5: 2, 5-<=7: 1, 7-<=9: 0.5, 9-<=11: -1, 11-<=13: -2, >13: -3</td>
                                </tr>
                                <tr>
                                    <td>Process Compliance</td>
                                    <td>NC</td>
                                    <td>1</td>
                                    <td>Follow process none compliance: 0: 3, =1: 2, =2: 1, =3: 0, =4: -1, =5: -2, >5: -3</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Quality plan</h4>

                    <h4>Strategy</h4>
                    <p>&#60;Describe strategies to achieve the quality objectives including DP goals and review/inspection&#62;</p>

                    <h4>Activities</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Activity</th>
                                    <th>Date/Frequency</th>
                                    <th>Responsibility</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Metric collecting and monitoring</td>
                                    <td></td>
                                    <td>PL</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Internal audit</td>
                                    <td></td>
                                    <td>PQA</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Quality gate inspections</td>
                                    <td>At milestones</td>
                                    <td>SQA</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Defect prevention</td>
                                    <td></td>
                                    <td>PL</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>&#60;Add here if needed&#62;</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">CM plan</h4>

                    <h4>Source Code Repository & Issue Tracker</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td>LoC: Baseline</td>
                                <td></td>
                                <td>LoC: Current</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Source Code Repository</td>
                                <td>Gitlab Rikkei:</td>
                                <td></td>
                                <td>External Repository</td>
                            </tr>
                            <tr>
                                <td>Issue Tracker</td>
                                <td>Redmine Rikkei:</td>
                                <td></td>
                                <td>External Issue Tracker</td>
                            </tr>
                        </table>
                    </div>

                    <h4>Environments</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <tr>
                                <td>Schedule link:</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Development:</td>
                                <td></td>
                                <td>Test:</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Staging:</td>
                                <td></td>
                                <td>Production:</td>
                                <td></td>
                            </tr>
                        </table>
                    </div>
                    <h4>Other</h4>
                    <p>Note...</p>
                </div>

                <div class="help-wo-table-item">
                    <h4 class="help-title-table">Others</h4>

                    <h4>Critical dependencies</h4>
                    <p>&#60;Describe any dependency on other projects. In case of dependencies, describe in detail the reasons, tasks and milestones. Also include milestone information in the project plan&#62;</p>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Critical dependency</th>
                                    <th>Expected Delivery Date</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>Refer to Risk list if needed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                    <h4>Assumption and constrains</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Assumption/Constrain</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td>&#60;Fill assumptions/constrains that this plan is based on. Also list all key success factors for the projects. These elements have to be delivered before the project can deliver successfully. NA if it’s not available&#62</td>
                                    <td>Refer to Risk list if needed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                    <h4>Project stages and milestones</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>Stage</th>
                                    <th>Description</th>
                                    <th>Milestone</th>
                                    <th>Quality gate plan date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Initiation</td>
                                    <td>Describe the objectives of the stage, start date and end date</td>
                                    <td>Kick-of meeting</td>
                                    <td>When do quality gate?</td>
                                </tr>
                                <tr>
                                    <td>Definition</td>
                                    <td>Describe the objectives of the stage, start date and end date</td>
                                    <td>Requirement review</td>
                                    <td>When do quality gate?</td>
                                </tr>
                                <tr>
                                    <td>Solution </td>
                                    <td>Describe the objectives of the stage, start date and end date</td>
                                    <td>Design review</td>
                                    <td>When do quality gate?</td>
                                </tr>
                                <tr>
                                    <td>Construction</td>
                                    <td>Describe the objectives of the stage, start date and end date</td>
                                    <td>Final inspection</td>
                                    <td>When do quality gate?</td>
                                </tr>
                                <tr>
                                    <td>Transition</td>
                                    <td>Describe the objectives of the stage, start date and end date</td>
                                    <td>Customer acceptance</td>
                                    <td>When do quality gate?</td>
                                </tr>
                                <tr>
                                    <td>Termination</td>
                                    <td>Describe the objectives of the stage, start date and end date</td>
                                    <td>Post mortem meeting</td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4>Training plan</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Topic</th>
                                    <th>Description</th>
                                    <th>Participants</th>
                                    <th>Start date</th>
                                    <th>End date</th>
                                    <th>Waiver criteria</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4>External interface</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Responsibilities</th>
                                    <th>Tel, Fax, Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4>Customer’s interface</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Responsibilities</th>
                                    <th>Tel, Fax, Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4>Project communication</h4>
                    <p>&#60Describe project planning, monitoring, control and communication mechanism including task assign, reports, reviews, meetings for internal project team, with management and with customer. Make strongly attention to changes management, customer feedback and complaints, issue handling&#62;</p>
                    
                    <h4>Tools and infrastructure</h4>
                    <div class="table-responsive">
                        <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Software/Hardware</th>
                                    <th>Purpose</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>
@endsection