<div class="table-responsive" style="max-width: 800px;">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th tbl-kpi"
        d-dom-tbl="average-detail" data-xml-ws-name="{division}">
        <colgroup>
            <col style="width: 250px;" data-width="186"/>
            <col data-width="55" />
            <col data-width="55" />
            <col data-width="55" />
            <col data-width="55" />
            <col data-width="55" />
            <col data-width="55" />
            <!--<col data-width="400" />-->
        </colgroup>
        <thead>
            <tr>
                <th>KPI</th>
                <th colspan="3">{division}</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <!--<th>Guideline</th>-->
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="td-thead text-center" data-xml-style-i-d="Thead">Project Information</td>
                <td data-xml-style-i-d="text_center">Number</td>
                <td data-xml-style-i-d="text_center">Value</td>
                <td data-xml-style-i-d="text_center">Point</td>
                <td data-xml-style-i-d="text_center">LCL</td>
                <td data-xml-style-i-d="text_center">Target</td>
                <td data-xml-style-i-d="text_center">UCL</td>
                <!--<td>&nbsp;</td>-->
            </tr>
            <tr>
                <td colspan="7" data-xml-style-i-d="text_left_bold"><strong>Cost</strong></td>
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Project Size Billable (MM)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{cost_bill}</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_even">&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">Project Size Plan (MM)</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{cost_plan_total}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{cost_plan_point}</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_odd">MM Plan (approved), 1MM = 20 MD</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Project Size Plan current (MM)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{cost_plan_current}</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_even">&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">Resource allocation - total (MM)</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{cost_resource_total}</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_odd">&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Calendar Effort - current (MM)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{cost_resource_current}</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_even">&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">Project Size Actual (MM)</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{cost_actual}</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_odd">MM Actual</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Effort Effectiveness (%)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{cost_effectiveness}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{cost_effec_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">80</td>
                <td class="number" data-xml-style-i-d="proj_body_even">100</td>
                <td class="number" data-xml-style-i-d="proj_body_even">120</td>
                <!--<td data-xml-style-i-d="proj_body_even">null: 1, <=80: 3, 80-<=100: 2, 100-<=110: 1, 110-<=120: -1, 120-<=130: -2, >130: -3</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">Effort Efficiency (%)</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{cost_effi}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{cost_effi_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">50</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">75</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">100</td>
                <!--<td data-xml-style-i-d="proj_body_odd">&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Busy rate (%)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{cost_busy_rate}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{cost_busy_rate_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">80</td>
                <td class="number" data-xml-style-i-d="proj_body_even">100</td>
                <td class="number" data-xml-style-i-d="proj_body_even">120</td>
                <!--<td data-xml-style-i-d="proj_body_even">&nbsp;</td>-->
            </tr>
            <tr>
                <td colspan="7" data-xml-style-i-d="text_left_bold"><strong>Quality</strong></td>
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Leakage(%)</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{qua_number_leakage}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{qua_leakage_value}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{qua_leakage_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">3</td>
                <td class="number" data-xml-style-i-d="proj_body_even">5</td>
                <td class="number" data-xml-style-i-d="proj_body_even">7</td>
<!--                <td data-xml-style-i-d="proj_body_even">0: 3đ, 0-5%: 2đ, 5%-10%: 1đ, 10%-15%: -1đ, 15%-20%: -2đ, > 20%: -3đ (cận trên là bằng, cận dưới là lớn hơn)
Số lỗi sau Final release/tổng số lỗi của dự án</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">Defect rate</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{qua_number_defect}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{qua_defect_value}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{qua_defect_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">0</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">0.5</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">1</td>
                <!--<td>&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Dev team effort (MM)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{effort_dev}</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_even">&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" class="number" data-xml-style-i-d="proj_detail_label_odd">Leakage/MM</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" class="number" data-xml-style-i-d="proj_body_odd">{leakage_mm}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <!--<td class="number" data-xml-style-i-d="proj_body_odd">&nbsp;</td>-->
            </tr>
            <tr>
                <td colspan="7" data-xml-style-i-d="text_left_bold"><strong>Timeliness</strong></td>
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Late Schedule (days)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{tl_sche_value}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{tl_sche_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">0</td>
                <td class="number" data-xml-style-i-d="proj_body_even">1</td>
                <td class="number" data-xml-style-i-d="proj_body_even">2</td>
                <!--<td>&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">Deliverable (%)</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{tl_deli_value}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{tl_deli_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">40</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">70</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">100</td>
                <!--<td data-xml-style-i-d="proj_body_odd">100: 3đ, 90-100: 2 đ, 80-90: 1 đ, 70-80: -1 đ, 60-70: -2đ, <=60: -3 đ (cận trên là bằng, cận dưới là lớn hơn) (mỗi 1 sản phẩm như TC, Software,...mỗi delivery đúng hạn cộng 1 điểm, mỗi chậm trừ 1 điểm, cộng tối đa là 3 điểm, trừ tối đa -3 điểm)</td>-->
            </tr>
            <tr>
                <td colspan="7" data-xml-style-i-d="text_left_bold"><strong>Process</strong></td>
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Process Compliance (#)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{pro_nc_value}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{pro_nc_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">0</td>
                <td class="number" data-xml-style-i-d="proj_body_even">1</td>
                <td class="number" data-xml-style-i-d="proj_body_even">2</td>
                <!--<td data-xml-style-i-d="proj_body_even"><=3: 2đ, 3-5: 1đ, 5-7: -1đ, >7: -2đ (cận trên là bằng, cận dưới là lớn hơn)</td>-->
            </tr>
            <tr>
                <td colspan="7" data-xml-style-i-d="text_left_bold"><strong>CSS</strong></td>
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Customer Satisfaction (Point)</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{css_value}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{css_point}</td>
                <td class="number" data-xml-style-i-d="proj_body_even">60</td>
                <td class="number" data-xml-style-i-d="proj_body_even">80</td>
                <td class="number" data-xml-style-i-d="proj_body_even">100</td>
                <!--<td data-xml-style-i-d="proj_body_even">100: 3đ, 90-99: 2đ, 80-90: 1đ, 70-80: -1 đ, 60-70:-2 đ, <=60:-3đ (cận trên là bằng, cận dưới là lớn hơn).Không lấy được CSS: -3 điểm.</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">CSS/MM</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_odd">{css_mm}</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_odd">&nbsp;</td>-->
            </tr>
            <tr>
                <td colspan="7" data-xml-style-i-d="text_left_bold"><strong>Conclusion</strong></td>
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_even">Project Point</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td class="number" data-xml-style-i-d="proj_body_even">{sumary_point}</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_even">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_even">&nbsp;</td>-->
            </tr>
            <tr>
                <td class="text-right" data-xml-style-i-d="proj_detail_label_odd">Project Evaluation</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="text_left_bold"><strong>{evaluation}</strong></td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <td data-xml-style-i-d="proj_body_odd">&nbsp;</td>
                <!--<td data-xml-style-i-d="proj_body_odd">&nbsp;</td>-->
            </tr>
<!--            <tr>
                <td colspan="7" data-xml-style-i-d="proj_body_even">Value of Reward for a Success project is based on Success Level of the Project that is depended of project points</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2" data-xml-style-i-d="text_left_bold"><strong>Level</strong></td>
                <td colspan="4" data-xml-style-i-d="text_left_bold"><strong>Criteria</strong></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2" data-xml-style-i-d="proj_body_even">Excellent</td>
                <td colspan="4" data-xml-style-i-d="proj_body_even">>=24 point</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2" data-xml-style-i-d="proj_body_odd">Good</td>
                <td colspan="4" data-xml-style-i-d="proj_body_odd">>=20 point</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2" data-xml-style-i-d="proj_body_even">Fair</td>
                <td colspan="4" data-xml-style-i-d="proj_body_even">>=15 point</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2" data-xml-style-i-d="proj_body_odd">Poor</td>
                <td colspan="4" data-xml-style-i-d="proj_body_odd">>=5 point</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2" data-xml-style-i-d="proj_body_even">Unacceptable</td>
                <td colspan="4" data-xml-style-i-d="proj_body_even"><5</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">A project is Unacceptable if:</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">- Timeliness < 50%, or</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">- Project Point <= 0</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">Otherwise a project is Success</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">A project has no Reward if:</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">- Project is Unacceptable, or</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">- Work Order or Acceptance Note is not approved, or</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="7">- The post-mortem meeting is not conducted within 15 days from the project targeted end date- The post-mortem meeting is not conducted within 15 days from the project targeted end date</td>
            </tr>-->
        </tbody>
    </table>
</div>
