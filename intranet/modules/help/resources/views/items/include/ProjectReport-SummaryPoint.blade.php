<body>
    <div class="help-table-item">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                <tbody><tr>
                        <td><font>Project Information</font></td>
                        <td><font>Value</font></td>
                        <td><font>Point</font></td>
                        <td><font>LCL</font></td>
                        <td><font>Target</font></td>
                        <td><font>UCL</font></td>
                        <td><font>Note</font></td>
                        <td><font>Maining</font></td>
                        <td><font>Formula</font></td>
                    </tr>
                    <tr>
                        <td><font>Plan Effort - total (MM)</font></td>
                        <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Summary Point Plan Effort comment') !!}</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Plan Effort maining') !!}</font></td>
                <td><font>Point = Follow Plan Effort - total: &lt;10: 0.5, &lt;=10-&lt;20: 1, &lt;=20-&lt;30: 2, &gt;=30: 3</font></td>
                </tr>
                <tr>
                    <td><font>Effort Effectiveness (%)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Actual Effort / Plan Effort - current</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="80" sdnum="1033;"><font>80</font></td>
                <td align="right" valign="bottom" sdval="100" sdnum="1033;"><font>100</font></td>
                <td align="right" valign="bottom" sdval="120" sdnum="1033;"><font>120</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Effort Effectiveness maining') !!}</font></td>
                <td><font>= Actual Effort/Plan Effort current *100<br>Point: Follow "Effort Effectiveness": null: 1, &lt;=80: 3, 80-&lt;=100: 2, 100-&lt;=110: 1, 110-&lt;=120: -1, 120-&lt;=130: -2, &gt;130: -3</font></td>
                </tr>
                <tr>
                    <td><font>Effort Efficiency (%)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Plan Effort - total / Resource allocation - total</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="50" sdnum="1033;"><font>50</font></td>
                <td align="right" valign="bottom" sdval="75" sdnum="1033;"><font>75</font></td>
                <td align="right" valign="bottom" sdval="100" sdnum="1033;"><font>100</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Effort Efficiency maining') !!}</font></td>
                <td><font>= Plan Effort total/Resource allocation total*100<br>Follow Effort Efficiency: &lt;50: -2, =50-&lt;70: -1, =70-&lt;80: 0.5, =80-&lt;90: 1, &gt;=90: 2</font></td>
                </tr>
                <tr>
                    <td><font>Busy rate (%)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Actual Effort / Calendar Effort - current</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="80" sdnum="1033;"><font>80</font></td>
                <td align="right" valign="bottom" sdval="100" sdnum="1033;"><font>100</font></td>
                <td align="right" valign="bottom" sdval="120" sdnum="1033;"><font>120</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Busy rate maining') !!}</font></td>
                <td><font>= Actual Effort/Calendar Effort current *100<br>Point: Follow Busy rate: &lt;70: -2, =70-&lt;80: -1, =80-&lt;90: 1, =90-&lt;110: 2, =110-&lt;120: 1, =120-&lt;140: -1, &gt;140: -2</font></td>
                </tr>
                <tr>
                    <td><font>Leakage (%)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Leakage error / Defect error</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="3" sdnum="1033;"><font>3</font></td>
                <td align="right" valign="bottom" sdval="5" sdnum="1033;"><font>5</font></td>
                <td align="right" valign="bottom" sdval="7" sdnum="1033;"><font>7</font></td>
                <td><font>
                        </font></td><td align="left" valign="top"><font>{!! trans('help::seed-view.ProjectReport Summary Point Leakage maining') !!}</font></td>
                <td><font>Value = Leakage error/Defect error*100<br>Point: Follow Leakage value: null: 3, &lt;=3: 3, 3-&lt;=5: 2, 5-&lt;=7: 1, 7-&lt;=9: 0.5, 9-&lt;=11: -1, 11-&lt;=13: -2, &gt;13: -3</font></td>
                </tr>
                <tr>
                    <td><font>Defect rate</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Defect error / Dev team effort (MD), if it exceeds the first quality gate actual date that this value &lt; 1 then reporting yellow</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="1" sdnum="1033;"><font>1</font></td>
                <td align="right" valign="bottom" sdval="1.5" sdnum="1033;"><font>1.5</font></td>
                <td align="right" valign="bottom" sdval="2" sdnum="1033;"><font>2</font></td>
                <td><font>
                        </font></td><td align="left" valign="top" bgcolor="#FFFFFF"><font>{!! trans('help::seed-view.ProjectReport Summary Point Defect rate maining') !!}</font></td>
                <td><font>Value = Defect error/Dev team effort(MD)<br>Point: Follow Defect rate value: null: 2, &lt;=1: 2, 1-&lt;=3: 1, 3-&lt;=5: -1, &gt;5: -2</font></td>
                </tr>
                <tr>
                    <td><font>Late Schedule (pd)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Number days slower than schedule, PM fill</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="0" sdnum="1033;"><font>0</font></td>
                <td align="right" valign="bottom" sdval="1" sdnum="1033;"><font>1</font></td>
                <td align="right" valign="bottom" sdval="2" sdnum="1033;"><font>2</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Late Schedule maining') !!}</font></td>
                <td><font>Value = number days slower than schedule<br>Point: Follow late schedule value: null: 2, 0: 2, 0-&lt;=1: 1, 1-&lt;=2: -1, &gt;2: -2</font></td>
                </tr>
                <tr>
                    <td><font>Deliverable (%)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Total deliverable on time / Total deliver till now (%)</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="40" sdnum="1033;"><font>40</font></td>
                <td align="right" valign="bottom" sdval="70" sdnum="1033;"><font>70</font></td>
                <td align="right" valign="bottom" sdval="100" sdnum="1033;"><font>100</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Deliverable maining') !!}</font></td>
                <td><font>Value = Total deliver on time/Total deliver till now*100<br>Point: Follow deliver value: &lt;=40: -3, 40-&lt;=55: -2, 55-&lt;70: -1, =70: 0, 70-&lt;=85: 1, 85-&lt;100: 2, 100: 3</font></td>
                </tr>
                <tr>
                    <td><font>Process None Compliance</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Number process none compliance</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="0" sdnum="1033;"><font>0</font></td>
                <td align="right" valign="bottom" sdval="1" sdnum="1033;"><font>1</font></td>
                <td align="right" valign="bottom" sdval="2" sdnum="1033;"><font>2</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Process None Compliance maining') !!}</font></td>
                <td><font> Point: Follow process none compliance: 0: 3, =1: 2, =2: 1, =3: 0, =4: -1, =5: -2, &gt;5: -3</font></td>
                </tr>
                <tr>
                    <td><font>Project Reports</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Total report: report yes + report no + report delayed</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="5" sdnum="1033;"><font>5</font></td>
                <td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Summary Point Project Reports maining') !!}</font></td>
                <td><font>Point = 2 + report yes * 0.5 - report no * 1 - report delayed * 0.5, -2 &lt;= point &lt;= 2</font></td>
                </tr>
                <tr>
                    <td><font>Customer Satisfation (Point)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Get from css system, COO can fill, if after 30 days since last actual date of deliver that not get css, value = 0</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="60" sdnum="1033;"><font>60</font></td>
                <td align="right" valign="bottom" sdval="80" sdnum="1033;"><font>80</font></td>
                <td align="right" valign="bottom" sdval="100" sdnum="1033;"><font>100</font></td>
                <td><font>
                        </font></td><td><font>
                        </font></td><td><font>Point: Follow Customer satisfactions value: null: 0, 90-&lt;=100: 3, 80-&lt;=90: 2, 70-&lt;=80: 1, 60-&lt;=70: 0.5, 50-&lt;=60: -1, &lt;=50: -2</font></td>
                </tr>
                <tr>
                    <td><font>Project Point</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Effort Effectiveness + Customer Satisfation + Deliverable + Leakage + Process None Compliance + Project Reports</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td></tr>
                <tr>
                    <td><font>Project Evaluation</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Follow Total point: Excellent: &gt;20, Good: 15-&lt;=20, Fair: 10-&lt;=15, Acceptable: 0-&lt;=10, Failed: =0</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td></tr>
                </tbody></table>
        </div>
    </div>
    <div class="help-2-explain">        
        <dt>Summary Point: </dt>
        <dd>
            {!! trans('help::seed-view.ProjectReport Summary Point explain') !!}           
        </dd>
    </div>
</body>