<div class="table-responsive freeze-wrapper">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th tbl-kpi"
        d-dom-tbl="project" data-xml-ws-name="Project">
        <colgroup>
            <col data-width="34.5"/>
            <col data-width="42"/>
            <col style="width: 170px;" width="170" data-width="83"/>
            <col data-width="45"/>
            <col data-width="50"/>
            <col data-width="49"/>
            <col data-width="54"/>
            <col data-width="57"/>
            <col data-width="60"/>
            <col data-width="52"/>
            <col data-width="88"/>
            <col data-width="84"/>
            <col data-width="69"/>
            <col data-width="66"/>
            <col data-width="45"/>
            <col data-width="58"/>
            <col data-width="64"/>
            <col data-width="71"/>
            <col data-width="72"/>
            <col data-width="49"/>
            <col data-width="54"/>
            <col data-width="64"/>
            <col data-width="58"/>
            <col data-width="73"/>
            <col data-width="72"/>
            <col data-width="83"/>
            <col data-width="81"/>
            <col data-width="63"/>
            <col data-width="72"/>
            <col data-width="71"/>
            <col data-width="76"/>
            <col data-width="65"/>
            <col data-width="55"/>
            <col data-width="70"/>
            <col data-width="70"/>
            <col data-width="70"/>
            <col data-width="69"/>
            <col data-width="72"/>
            <col data-width="72"/>
            <col data-width="87"/>
            <col data-width="88"/>
            <col data-width="64"/>
            <col data-width="80"/>
            <col data-width="80"/>
            <col data-width="64"/>
            <col data-width="64"/>
        </colgroup>
        <thead>
            <tr>
                <th class="freeze-col" data-free-width="40">No.</th>
                <th class="freeze-col" data-free-width="50">Id</th>
                <th class="freeze-col" data-free-width="120">Name</th>
                <th d-proj-index>Cost billable<br/>(MM)</th>
                <th d-proj-index>Cost plan effort total (MM)</th>
                <th d-proj-index>Cost plan effort point</th>
                <th d-proj-index>Cost plan effort current (MM)</th>
                <th d-proj-index>Cost resource allocation total (MM)</th>
                <th d-proj-index>Cost calendar effort current (MM)</th>
                <th d-proj-index>Cost actual effort (MM)</th>
                <th d-proj-index>Cost effectiveness</th>
                <th d-proj-index>Cost effectiveness point</th>
                <th d-proj-index>Cost efficiency</th>
                <th d-proj-index>Cost efficiency point</th>
                <th d-proj-index>Cost busy rate</th>
                <th d-proj-index>Cost busy rate point</th>
                <th d-proj-index>Quality error number leakage</th>
                <th d-proj-index>No leakage / mm</th>
                <th d-proj-index>Quality leakage value</th>
                <th d-proj-index>Quality leakage point</th>
                <th d-proj-index>Quality error number defect</th>
                <th d-proj-index>No defect/mm</th>
                <th d-proj-index>Quality defect value</th>
                <th d-proj-index>Quality defect point</th>
                <th d-proj-index>Effort dev team (MM)</th>
                <th d-proj-index>Timeliness schedule value</th>
                <th d-proj-index>Timeliness schedule point</th>
                <th d-proj-index>Timeliness deliverable value</th>
                <th d-proj-index>Timeliness deliverable point</th>
                <th d-proj-index>Process NC value</th>
                <th d-proj-index>Process NC point</th>
                <th d-proj-index>Process report value</th>
                <th d-proj-index>Process report point</th>
                <th d-proj-index>Css value</th>
                <th d-proj-index>Css point</th>
                <th d-proj-index>Sumary point</th>
                <th>Baseline at</th>
                <th>PM</th>
                <th>Type</th>
                <th>Start date</th>
                <th>End date</th>
                <th>Duaration (day)</th>
                <th>Programming language</th>
                <th>Division</th>
                <th>CSS value * billable (mm)</th>
                <th>Leakage number * billable (mm)</th>
                <th d-proj-index="remove">css / mm</th>
                <th d-proj-index="remove">leakage / mm</th>
            </tr>
        </thead>
        <tbody data-dom-list="projs">
            <tr data-dom-item="projs">
                <td class="number" data-xml-style-i-d="{projbodystyle}">{no}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{id}</td>
                <td class="freeze-cola" data-xml-style-i-d="{projbodystyle}">{name}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_bill}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_plan_total}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_plan_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_plan_current}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_resource_total}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_resource_current}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_actual}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_effectiveness}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_effec_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_effi}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_effi_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_busy_rate}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_busy_rate_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_number_leakage}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_leakage_mm}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_leakage_value}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_leakage_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_number_defect}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_defect_mm}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_defect_value}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{qua_defect_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{effort_dev}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{tl_sche_value}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{tl_sche_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{tl_deli_value}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{tl_deli_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{pro_nc_value}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{pro_nc_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{pro_report_value}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{pro_report_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{css_value}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{css_point}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{sumary_point}</td>
                <td data-xml-style-i-d="{projbodystyle}">{baseline_at}</td>
                <td data-xml-style-i-d="{projbodystyle}">{pm}</td>
                <td data-xml-style-i-d="{projbodystyle}">{type_label}</td>
                <td data-xml-style-i-d="{projbodystyle}">{start_date}</td>
                <td data-xml-style-i-d="{projbodystyle}">{end_date}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{duaration_day}</td>
                <td data-xml-style-i-d="{projbodystyle}">{pl}</td>
                <td data-xml-style-i-d="{projbodystyle}">{division}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{css_billable_mm}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{leakage_billable_mm}</td>
                <td data-xml-style-i-d="{projbodystyle}">{css_mm}</td>
                <td data-xml-style-i-d="{projbodystyle}">{leakage_mm}</td>
            </tr>
        </tbody>
    </table>
</div>
