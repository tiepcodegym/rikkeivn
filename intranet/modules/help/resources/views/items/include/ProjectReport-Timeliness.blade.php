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
                        <td><font>
                                </font></td><td><font>
                                </font></td></tr>
                    <tr>
                        <td><font>Late Schedule (pd)</font></td>
                        <td><a class="comment-indicator"></a>
                <comment>
                    {!! trans('help::seed-view.ProjectReport Timeliness Late Schedule comment') !!}</comment>
                <font>
                    </font></td><td><font>
                        </font></td><td align="right" valign="bottom" sdval="0" sdnum="1033;"><font>0</font></td>
                <td align="right" valign="bottom" sdval="1" sdnum="1033;"><font>1</font></td>
                <td align="right" valign="bottom" sdval="2" sdnum="1033;"><font>2</font></td>
                <td><font>
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Timeliness Late Schedule maining') !!}</font></td>
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
                        </font></td><td><font>{!! trans('help::seed-view.ProjectReport Timeliness Deliverable maining') !!}</font></td>
                <td><font>Value = Total deliver on time/Total deliver till now*100<br>Point: Follow deliver value: &lt;=40: -3, 40-&lt;=55: -2, 55-&lt;70: -1, =70: 0, 70-&lt;=85: 1, 85-&lt;100: 2, 100: 3</font></td>
                </tr>
                </tbody></table>
        </div>
    </div>
    <div class="help-2-explain">                 
        <dt>Timeliness:</dt>
        <dd>
            {!! trans('help::seed-view.ProjectReport Timeliness explain') !!}            
        </dd>
    </div>
</body>