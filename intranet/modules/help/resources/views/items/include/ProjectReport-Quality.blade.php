<body>
    <div class="help-table-item"> 
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                <tbody><tr>
                        <td><font>Project Information</font></td>
                        <td><font>Errors number</font></td>
                        <td><font>Value</font></td>
                        <td><font>Point</font></td>
                        <td><font>LCL</font></td>
                        <td><font>Target</font></td>
                        <td><font>UCL</font></td>
                        <td><font>Note</font></td>
                        <td><font>
                                </font></td><td><font>
                                </font></td></tr>
                    <tr>
                        <td height="68"><font>Leakage (%)</font></td>
                        <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Quality Leakage comment') !!}</comment>
                <font>
                    </font></td><td><a class="comment-indicator"></a>
                <comment>
                    Leakage error / Defect error</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="3" sdnum="1033;"><font>3</font></td>
                <td align="right" valign="bottom" sdval="5" sdnum="1033;"><font>5</font></td>
                <td align="right" valign="bottom" sdval="7" sdnum="1033;"><font>7</font></td>
                <td><font>
                        </font></td><td align="left" valign="top"><font>{!! trans('help::seed-view.ProjectReport Quality Leakage maining') !!}</font></td>
                <td><font>Value = Leakage error/Defect error*100<br>Point: Follow Leakage value: null: 3, &lt;=3: 3, 3-&lt;=5: 2, 5-&lt;=7: 1, 7-&lt;=9: 0.5, 9-&lt;=11: -1, 11-&lt;=13: -2, &gt;13: -3</font></td>
                </tr>
                <tr>
                    <td height="64"><font>Defect rate</font></td>
                    <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Quality Defect rate comment') !!}</comment>
                <font>
                    </font></td><td><a class="comment-indicator"></a>
                <comment>
                    Defect error / Dev team effort (MD), if it exceeds the first quality gate actual date that this value &lt; 1 then reporting yellow</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="1" sdnum="1033;"><font>1</font></td>
                <td align="right" valign="bottom" sdval="1.5" sdnum="1033;"><font>1.5</font></td>
                <td align="right" valign="bottom" sdval="2" sdnum="1033;"><font>2</font></td>
                <td><font>
                        </font></td><td align="left" valign="top" bgcolor="#FFFFFF"><font>{!! trans('help::seed-view.ProjectReport Quality Defect rate maining') !!}</font></td>
                <td><font>Value = Defect error/Dev team effort(MD)<br>Point: Follow Defect rate value: null: 2, &lt;=1: 2, 1-&lt;=3: 1, 3-&lt;=5: -1, &gt;5: -2</font></td>
                </tr>
                </tbody></table>
        </div>
    </div>
    <div class="help-2-explain">                  
        <dt>Quality:</dt>
        <dd>
            {!! trans('help::seed-view.ProjectReport Quality explain') !!}            
        </dd>
    </div>
</body>