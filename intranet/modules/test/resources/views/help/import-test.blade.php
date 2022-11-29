<h4><strong>Các dạng câu hỏi</strong></h4>

<ol>
    <li>
        <strong>Loại 1 - dạng điền từ: </strong><br />
        Câu hỏi điền từ vào chỗ trống (thường chỉ có 1 từ)
    </li>
    <li>
        <strong>Loại 2 - Dạng chọn nhiều đáp án</strong><br />
        - Câu hỏi có nhiều ý nhỏ, mỗi ý phải chọn 1 đáp án<br />
        - Danh sách đáp án có thể nhiều hơn số lượng các ý nhỏ
    </li>
    <li>
        <strong>Loại 3 - Dạng trắc nghiệm</strong><br />
        - Câu hỏi chọn 1 hoặc nhiều đáp án trong các đáp án đưa ra
    </li>
</ol>

<h4><strong>Format file excel</strong></h4>

<p style="padding-left: 20px;">
    1 file excel là một đề thi (<span style="color: #ff0000;">cần được format mỗi ô ở dạng dữ liệu là Text</span>), 
    các dạng câu hỏi <span style="color: #ff0000;">loại 1 và loại 3 được cho vào 1 sheet (tên là "Type1"), 
    dạng câu hỏi 2 được cho vào 1 sheet (tên là "Type2")</span><br />
    <a href="https://drive.google.com/open?id=0B6opbRnJ60WJSFQtSUt0dHVaMnM" target="_blank">Xem file mẫu</a>
</p>
<ol>
    <li>
        <strong>Loại 1 + loại 3</strong><br />
        <strong>Gồm các cột: </strong>
        <ul>
            <li>Type: loại câu hỏi</li>
            <li>Content: nội dung câu hỏi</li>
            <li>A, B, C, D, E, F, G, H: nội dung đáp án A, B, C, D, E, F, G, H (tối đa 8 đáp án)<br />
                Với loại 1 bỏ trống các cột này
            </li>
            <li>Correct: đáp án đúng<br />
                Với loại 1 (điền từ) nhập các từ đúng<br />
                Với loại 3 nhập đáp án (A, B, C, D, E, F, G, H)<br />
                Nếu nhiều đáp án cách nhau bở dấu phẩy ",".<br />
            </li>
            <li>Explain: giải thích cho đáp án. Sẽ hiển thị ra ở trang kết quả bài test.</li>
            <li>Multi choice: chọn được một hay nhiều đáp án cho câu hỏi câu hỏi <i style="color: #ff0000;">(giá trị 1: có multi choice, trống hoặc 0: single choice)</i></li>
            <li>Category: mặc định có 3 cột thể loại để nhập (dùng để tùy chọn hiển thị/lọc câu hỏi),<br />
                ví dụ Category 1: Tiếng nhật, Category 2: N1, Category 3: Nghe<br />
                với các chuyên môn khác có thể là: Category 1: Senior...</li>
            <li>Disable: câu hỏi không muốn hiển thị <i style="color: #ff0000;">(giá trị 1: không hiển thị, trống hoặc 0: hiển thị)</i></li>
        </ul>
        <p style="margin-top: 15px;"><img class="img-responsive" src="/tests/images/type1_3.png" alt="type 1, 3"></p>
        <p style="text-align: center"><i>Hình 1: sheet excel loại 1 và 3</i></p>
    </li>
    <li>
        <strong>Loại 2</strong><br />
        <strong>Gồm các cột: </strong>
        <ul>
            <li>ID: id câu hỏi<br />
                (Mỗi câu hỏi gồm nhiều hàng đánh <strong>ID trùng nhau và sắp xếp liên tiếp nhau</strong>,<br />
                <i style="color: #ff0000;">mỗi hàng nếu có nội dung là một ý nhỏ</i>,<br />
                <i style="color: #ff0000;">nếu không có nội dung thì đó chỉ là hàng đáp án</i><br />
                <i>các hàng được thu thập làm một list đáp án cho câu hỏi</i></li>
            <li>Content: nội dung câu hỏi</li>
            <li>Correct: label đáp án (A, B, C, ......), nếu thuộc hàng có nội dung thì là đáp án đúng của ý nhỏ đó</li>
            <li>Answer content: nội dung đáp án</li>
            <li>Các cột Explain, Category, Disable, (không có cột Multi choice) giống như loại 1 và 3,<br />
                <span style="color: #ff0000">Nội dung của các cột này chỉ để ở hàng đầu tiên của mỗi câu hỏi (ý đầu tiên)</span></li>
        </ul>
        <p style="margin-top: 15px;"><img class="img-responsive" src="/tests/images/type2.png" alt="type 1, 3"></p>
        <p style="text-align: center"><i>Hình 2: sheet excel loại 2</i></p>
    </li>
</ol>

<h4><strong>Format Nội dung (code, image, audio)</strong></h4>

<p style="padding-left: 20px;">
    Có thể upload file (image/audio) trên trang này: 
    <a href="/test/upload-files" target="_blank">http://rikkei.vn/test/upload-files</a>
</p>
<p style="padding-left: 20px;">
    Nếu muốn chèn code ngôn ngữ, image, audio thì nhập vào cột Content (Nội dung câu hỏi) theo định dạng sau: <br />
    (Code và image, trừ audio có thể chèn vào các cột nội dung đáp án trừ loại 1 - loại điền từ)
</p>
<ol>
    <li>
        <strong>Code: </strong><br />
        cú pháp: ```&lt;ngôn ngữ&gt; &lt;code&gt; ```<br />
        ví dụ:<br />
        ```php
        <ul style="list-style: none;">
            <li>$a = 3;</li>
            <li>$b = 5;</li>
            <li>$c = $a + $b;</li>
        </ul>
            ```
    </li>
    <li>
        <strong>Image (cho phép các file: jpeg, jpg, png, gif)</strong><br />
        cú pháp: ```&lt;image&gt; &lt;link&gt; ```<br />
        <span style="margin-right: 20px;">ví dụ:</span> ```image http://rikkei.vn/../image.png ```
    </li>
    <li>
        <strong>Audio (cho phép các file: mp3, wma, wav)</strong><br />
        cú pháp: ```&lt;audio&gt; &lt;link&gt; ```<br />
        <span style="margin-right: 20px;">ví dụ:</span> ```audio http://rikkei.vn/../audio.mp3 ```
    </li>
</ol>

<h4><strong>Upload file ảnh cùng lúc với import bài test bằng file excel</strong></h4>
<ul>
    <li>Vì không chèn ảnh vào từng ô trong excel được nên để chèn ảnh cho từng nội dung câu hỏi thì chèn ảnh bình thường vào file excel</li>
    <li>Sau đó click chuột phải vào từng ảnh, chọn "Size and properties"</li>
    <li>Click vào "Alt Text", và điền số thứ tự câu hỏi vào trường "Description", nếu là sheet "type2" thì điền ID của câu hỏi.</li>
</ul>
<p style="margin-top: 15px;"><img class="img-responsive" src="/tests/images/image_excel.png" alt="type 1, 3"></p>
