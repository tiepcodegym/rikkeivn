<body>
    <div class="help-table-item">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                <tbody><tr>
                        <td><font>Project information</font></td>
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
                        <td height="43"><font>Billable Effort (MM)</font></td>
                        <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Cost Billable Effort comment') !!}</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Billable Effort maining') !!}</font></td>
                <td><font>
                        </font></td></tr>
                <tr>
                    <td height="43"><font>Plan Effort - total (MM)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Cost Plan Effort - total comment') !!}
                    </comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Plan Effort - total maining') !!}</font></td>
                <td><font>Point: Follow Plan Effort - total: &lt;10: 0.5, &lt;=10-&lt;20: 1, &lt;=20-&lt;30: 2, &gt;=30: 3</font></td>
                </tr>
                <tr>
                    <td height="43"><font>Plan Effort - current (MM)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Cost Plan Effort - current comment') !!}</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Plan Effort - current maining') !!}</font></td>
                <td><font>
                        </font></td></tr>
                <tr>
                    <td height="43"><font>Resource allocation - total (MM)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Cost Resource allocation - total comment') !!}</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Resource allocation - total maining') !!}
                </font></td>
                <td><font>
                        </font></td></tr>
                <tr>
                    <td height="43"><font>Calendar Effort - current (MM)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Cost Calendar Effort - current comment') !!}</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Calendar Effort - current maining') !!}</font></td>
                <td><font>
                        </font></td></tr>
                <tr>
                    <td height="43"><font>Actual Effort (MM)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Cost Actual Effort comment') !!}</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Actual Effort maining') !!}</font></td>
                <td><font>
                        </font></td></tr>
                <tr>
                    <td height="56"><font>Effort Effectiveness (%)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Actual Effort / Plan Effort - current</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="80" sdnum="1033;"><font>80</font></td>
                <td align="right" valign="bottom" sdval="100" sdnum="1033;"><font>100</font></td>
                <td align="right" valign="bottom" sdval="120" sdnum="1033;"><font>120</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Effort Effectiveness maining') !!}</font></td>
                <td><font>= Actual Effort/Plan Effort current *100<br>Point: Follow "Effort Effectiveness": null: 1, &lt;=80: 3, 80-&lt;=100: 2, 100-&lt;=110: 1, 110-&lt;=120: -1, 120-&lt;=130: -2, &gt;130: -3</font></td>
                </tr>
                <tr>
                    <td height="68"><font>Effort Efficiency (%)</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    Plan Effort - total / Resource allocation - total</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="50" sdnum="1033;"><font>50</font></td>
                <td align="right" valign="bottom" sdval="75" sdnum="1033;"><font>75</font></td>
                <td align="right" valign="bottom" sdval="100" sdnum="1033;"><font>100</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Effort Efficiency maining') !!}</font></td>
                <td><font>= Plan Effort total/Resource allocation total*100<br>Point: Follow Effort Efficiency: &lt;50: -2, =50-&lt;70: -1, =70-&lt;80: 0.5, =80-&lt;90: 1, &gt;=90: 2</font></td>
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
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Cost Busy rate maining') !!}</font></td>
                <td><font>= Actual Effort/Calendar Effort current *100<br>Point: Follow Busy rate: &lt;70: -2, =70-&lt;80: -1, =80-&lt;90: 1, =90-&lt;110: 2, =110-&lt;120: 1, =120-&lt;140: -1, &gt;140: -2</font></td>
                </tr>
                <tr>
                    <td><font>Productivity</font></td>
                    <td><font>
                            </font></td><td><font>LOC:</font></td>
                    <td><font>
                            </font></td><td><font>
                            </font></td><td><font>
                            </font></td><td><font>
                            </font></td><td><font>
                            </font></td><td><font>=Line of code (current) / actual Effort</font></td>
                </tr>
                </tbody></table>
        </div>
    </div>
    <div class="help-2-explain">                
        <dt>Cost:</dt>
        <dd>
            {!! trans('help::seed-view.ProjectReport Cost explain') !!}            
        </dd>
    </div>
</body>