<body>
    <div class="help-table-item">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data not-padding-th">
                <tbody><tr>
                    </tr><tr>
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
                        <td><font>Customer satisfactions</font></td>
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
                    <td><font>
                            </font></td><td><font>
                            </font></td><td><font>
                            </font></td><td><font>Positive</font></td>
                    <td><font>Negative</font></td>
                    <td><font>
                            </font></td><td><font>
                            </font></td><td><font>
                            </font></td><td><font>
                            </font></td></tr>

                </tbody></table>
        </div>
    </div>
    <div class="help-2-explain">
        <dt>CSS:</dt>
        <dd>
            {!! trans('help::seed-view.ProjectReport CSS explain') !!}            
        </dd>
    </div>
    <?php /*<div class="help-2-explain">
        <h3>Thưởng CSS điểm cao</h3>
        <dt>1. Cơ chế lấy CSS: </dt>
        <dd>
            <p>Người gửi CSS: PQA</p>
            <p>- Với cá nhân, OSDC: lấy 3 tháng 1 lần hoặc khi kết thúc OSDC</p>
            <p>- Với Project base: kết thúc dự án.</p>
            <p>Hoặc Sale ghi nhận nhận xét của KH trong quá trình thực hiện dự án.</p>
        </dd>
        <dt>2. Cơ chế thưởng:</dt>
        <dd>
            <p>- Thưởng ngay sau khi có CSS, deadline: ko quá ngày nhận CSS 1 tuần (5 workingdays)</p>
            <p>- PQA confirm trước với leader và gửi tin cho truyền thông.</p>
            <p>- Truyền thông vinh danh CSS cao >95 hoặc KH khen ngợi, 1 tháng 1 lần (intranet).</p>
        </dd>
        <dt>3. Mức thưởng: trích từ quỹ chi phí dự án của D, Leader dựa vào Guideline dưới để cân nhắc đưa ra quyết định:</dt>
        <dd>
            <p>Loại 1. Cá nhân: 100 đ, 500K (trường hợp onsite, đánh giá cá nhân)</p>
            <p>Loại 2. Project: CSS>=95</p>
            <p>- Billable<5MM: 500k</p>
            <p>- Billable>=5MM: 1M</p>
            <p>- Billable>=10MM: 1.5M</p>
            <p>- Billable >=20MM: 2M</p>
            <p>Loại 3: KH ko cho điểm nhưng có khen ngợi thành tích xuất sắc: Leader cân nhắc và ra quyết mực thưởng hợp lý. (không cao hơn mức thưởng CSS cao nhất)</p>
        </dd>
        <dt>4. Trách nhiệm các bên: </dt>
        <dd>
            <p>- PQA: chịu trách nhiệm đề xuất với leader khi nhận đc CSS, hoặc sale nhận đc khen thưởng thì cũng FW lại và đề xuất với Leader. Và PQA confirm trước với leader sau đó gửi tin cho truyền thông.</p>
            <p>- Leader: có nhiệm vụ phản hồi nhanh chóng trong vòng 1 workingday.</p>
            <p>- Admin: làm quyết định khen thưởng và giải ngân trong vòng 5 workingday.</p>
            <p>- Truyền thông vinh danh CSS cao >95 hoặc KH khen ngợi, 1 tháng 1 lần (kênh intranet). </p>
        </dd>
    </div>*/ ?>
</body>