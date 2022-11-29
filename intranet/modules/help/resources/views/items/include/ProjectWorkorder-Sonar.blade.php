<h2>1. Tạo project ở workorder</h2>

<ul>
	<li>Nếu source code dự án dùng <strong>gitlab ngoài công ty</strong>, thì PM chỉ cần click vào nút <strong>create project sonar</strong>, sao đó dùng <strong>sonar lint</strong> để check trên local. Link trang chủ sonar lint&nbsp;<a href="http://www.sonarlint.org" target="_blank">https://www.sonarlint.org</a></li>
	<li>Nếu source code dự án dùng gitlab của công ty, thì PM thực hiện các bước sau:</li>
</ul>

<ol style="margin-left: 40px;">
	<li>Tạo project gitlab - Click vào nút <strong>Sync project in server</strong> ở phần&nbsp;Use Rikkei GitLab trong workorder</li>
	<li>Truy cập vào project trên gitlab, enable deploy key&nbsp;<meta charset="utf-8" /><b id="docs-internal-guid-65741f41-55d1-050e-1d56-9aaab5224475">jenkins&nbsp;</b>(Mục: Setting /<meta charset="utf-8" /><b> </b>Repository <b>/ </b>Deploy keys)</li>
	<li>Tạo dự án Sonar - Click vào nút <strong>Create&nbsp;project sonar</strong> ở phần&nbsp;Sonar trong <strong>workorder</strong></li>
	<li>Tạo dự án Jenkins - Click vào nút <strong>Create&nbsp;project jenkins</strong> ở phần&nbsp;Sonar trong <strong>workorder</strong></li>
	<li>Kiểm tra: Truy cập vào project gitlab, mục&nbsp;<meta charset="utf-8" /><b id="docs-internal-guid-65741f41-55d4-dd2f-c3c1-7df0ceb37540">Settings / Integrations</b>, xem có thấy danh sách 2 webhooks không. Nếu thấy click vào Button <strong>Test</strong>, chọn <strong>Push event</strong> để xem git đã kết nối với jenkins chưa. Nếu response 200 thì đã thành công</li>
</ol>

<h2>2. Config project ở source code</h2>

<h3>2.1. PHP project</h3>

<p><meta charset="utf-8" /></p>

<p dir="ltr" style="margin-top:0pt; margin-bottom:0pt"><span style="line-height:1.38"><span style="font-size:11pt"><span style="font-family:Arial"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">Thêm file </span></span></span></span></span></span><span style="font-size:11pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-weight:700"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">sonar-project.properties </span></span></span></span></span></span></span><span style="font-size:11pt"><span style="font-family:Arial"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">ở thư mục root dự án với nội dung:</span></span></span></span></span></span></span></p>

<p dir="ltr" style="margin-top:0pt; margin-bottom:0pt"><em><span style="line-height:1.38"><span style="font-size:11pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">sonar.sources=app. resource</span></span></span></span></span></span></span></em></p>

<p dir="ltr" style="margin-top:0pt; margin-bottom:0pt"><em><span style="line-height:1.38"><span style="font-size:11pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">sonar.exclusions=resources/assets/sass/**/*</span></span></span></span></span></span></span></em></p>

<ul>
	<li>sources: folder phân tích source code</li>
	<li>exclusions: folder, file không phân tích source code</li>
</ul>

<h3 dir="ltr">2.2. Android project</h3>

<ul>
	<li>Thêm các thuộc tính sau vào file <strong>build.gradle</strong>. Thuộc tính <i>plugins</i> phải ở trên thuộc tính <strong>allprojects{}</strong></li>
</ul>

<p dir="ltr" style="margin-top:0pt; margin-bottom:0pt"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">plugins {</span></span></span></span></span></span></span></p>

<p dir="ltr" style="margin-top: 0pt; margin-bottom: 0pt; margin-left: 40px;"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">id &quot;org.sonarqube&quot; version &quot;2.6&quot;</span></span></span></span></span></span></span></p>

<p dir="ltr" style="margin-top:0pt; margin-bottom:0pt"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">}</span></span></span></span></span></span></span></p>

<ul dir="ltr">
	<li style="list-style-type: disc; font-size: 11pt; font-family: Arial; font-variant-numeric: normal; font-variant-east-asian: normal; vertical-align: baseline; white-space: pre; margin-top: 0pt; margin-bottom: 0pt;"><span style="font-size:11pt"><span style="font-family:Arial"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">Thuộc tính </span></span></span></span></span></span><span style="font-size:11pt"><span style="font-family:Arial"><span style="font-weight:700"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">projejct</span></span></span></span></span></span></span><span style="font-size:11pt"><span style="font-family:Arial"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap"> là option, dùng để bỏ qua phân tích source code của 1 module</span></span></span></span></span></span></li>
</ul>

<p dir="ltr" style="margin-top:0pt; margin-bottom:0pt"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">project(&quot;:module-name&quot;) {</span></span></span></span></span></span></span></p>

<p dir="ltr" style="margin-top: 0pt; margin-bottom: 0pt; margin-left: 40px;"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">sonarqube {</span></span></span></span></span></span></span></p>

<p dir="ltr" style="margin-top: 0pt; margin-bottom: 0pt; margin-left: 80px;"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">skipProject = true</span></span></span></span></span></span></span></p>

<p dir="ltr" style="margin-top: 0pt; margin-bottom: 0pt; margin-left: 40px;"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">}</span></span></span></span></span></span></span></p>

<p dir="ltr" style="margin-top:0pt; margin-bottom:0pt"><span style="line-height:1.38"><span style="font-size:10pt"><span style="font-family:&quot;Roboto Mono&quot;"><span style="font-variant-numeric:normal"><span style="font-variant-east-asian:normal"><span style="vertical-align:baseline"><span style="white-space:pre-wrap">}</span></span></span></span></span></span></span></p>

<h3>2.3. Java project</h3>

<p>Java project không cần config gi thêm, nhưng phải&nbsp;được build thành công qua maven</p>

<p>* <strong>Note</strong>:</p>

<ol>
	<li>Hiện tại sonar chỉ hỗ trợ các dự án được xây dựng dựa trên: <strong>PHP</strong>, <strong>Java</strong>, <strong>Java Android</strong></li>
	<li>Nếu gặp vấn đề phát sinh lỗi, liên hệ GiangNT2 (skype: giangsoda@hotmail.com) để được hỗ trợ</li>
</ol>
