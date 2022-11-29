<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th tbl-kpi"
        d-dom-tbl="average" data-xml-ws-name="Average">
        <colgroup>
            <col data-width="90"/>
            <col data-width="90"/>
            <col style="width: 170px;" width="170" data-width="83"/>
            <col data-width="80"/>
            <col data-width="71"/>
            <col data-width="61"/>
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
                <th>Division</th>
                <th>Sum of cost billable</th>
                <th>Average of cost efficiency</th>
                <th>Average of cost effectiveness</th>
                <th>Sum of cost resource allocation total</th>
                <th>Average of No defect/mm</th>
                <th>Average of No leakage/mm</th>
                <th>Average of css value</th>
            </tr>
        </thead>
        <tbody data-dom-list="short-ave-team">
            <tr data-dom-item="short-ave-team">
                <td data-xml-style-i-d="{projbodystyle}">{division}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_bill}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{aver_cost_effi}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{aver_cost_effectiveness}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{cost_resource_total}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{aver_qua_defect_mm}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{aver_qua_leakage_mm}</td>
                <td class="number" data-xml-style-i-d="{projbodystyle}">{aver_css_value}</td>
            </tr>
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>
            <tr class="tr-empty">
                <td>&nbsp;</td>
            </tr>
            <tr data-dom-title="ave-team-1" data-height="35">
                <td colspan="2" class="td-thead black" data-xml-style-i-d="proj_ave_ave">AVERAGE</td>
            </tr>
            <tr data-dom-title="ave-team" data-height="51">
                <td class="td-thead" data-xml-style-i-d="Thead">Count project</td>
                <td class="td-thead" data-xml-style-i-d="Thead">Division</td>
            </tr>
            <tr data-dom-item="ave-team">
                <td class="number" data-xml-style-i-d="{projbodystyle}">{proj_count}</td>
                <td data-xml-style-i-d="{projbodystyle}">{division}</td>
            </tr>
        </tbody>
    </table>
</div>
