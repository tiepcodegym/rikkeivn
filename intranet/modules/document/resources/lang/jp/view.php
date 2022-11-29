<?php

return [
    'Add accounts' => 'Thêm tài khoản',
    'Attach file' => '添付ファイル',
    'Approver' => '承認者',
    'Author' => '作成者',
    'Back' => '戻る',
    'Changed' => '修正',
    'Add' => '追加する',
    'Close' => '閉じる',
    'Coordinator' => 'コーディネーター',
    'Comment' => 'コメント',
    'Content' => '内容',
    'Create' => '新作成',
    'Created document' => '資料を作成',
    'Create type' => '新しい文書タイプを追加する',
    'Create document' => '資料を追加',
    'Current version' => '現バージョン',
    'Created time' => '作成時間',
    'Delete' => '削除',
    'Document' => '資料',
    'Document code' => '資料コード',
    'Document creator' => '書類の作成者',
    'Document request' => '資料を要求',
    'Document help' => '資料の案内',
    'Help' => '案内',
    'Description' => '説明',
    'Download' => 'ダウンロード',
    'Email subject' => 'タイトルメール',
    'Email content' => '内容メール',
    'Edit' => '編集',
    'Edit type' => '資料タイプを編集',
    'Edit document' => '資料を編集',
    'Feedback reason' => 'フィードバック理由',
    'Document file' => 'File tài liệu',
    'Document types' => '資料タイプ',
    'File name' => 'ファイル名',
    'File upload' => 'ファイルアップロード',
    'Or input link' => 'または、引用リンクを記入する',
    'Group' => 'グループ',
    'History' => '歴史',
    'Mimetype' => 'Mimetype',
    'List versions' => '各バージョン',
    'Name' => '名',
    'Not assigne' => '割り当てない',
    'Note' => 'メモ',
    'Parent' => '親科目',
    'Publish document' => '資料を発行',
    'Reviewer' => 'レビューアー',
    'Save' => 'セーブ',
    'Send mail' => 'メールを送信する',
    'show less' => 'ẩn bớt',
    'show more' => 'xem thêm',
    'Status' => '状態',
    'Sort order' => '順番',
    'Search' => '検索',
    'Submit' => '提出',
    'Set as current' => 'Đặt làm bản hiện tại',
    'Title' => 'タイトル',
    'Type file' => 'ファイルタイプ',
    'Unapproved Value' => 'Unapproved Value',
    'Upload file' => 'ファイルの選択',
    'Version' => 'バージョン',
    'Version manage' => 'Quản lý phiên bản',
    'mail_publish' => [
        'subject' => 'Thông báo ban hành tài liệu :code',
        'content' => '<p><strong>Kính gửi {{ name }},</strong></p>'
            . '<p>Tài liệu <strong>:code</strong> đã được ban hành</p>'
            . '<p><a href=":link">Chi tiết</a></p>'
    ],
    'Publisher' => '出版社',
    /** request **/
    'Request name' => 'リクエスト名',
    'Created document request' => '資料リクエストを作成',
    'Create document request' => '資料リクエストを作成',
    'Edit document request' => '資料リクエストを編集',
    'All documnets' => 'Tất cả tài liệu',
    'Account' => 'アカウント',
    'Published to' => 'に出版',
    'Re publish document' => 'Publish lại tài liệu',
    'File document' => 'File tài liệu',
    'search for' => 'tìm kiếm cho',
    'Editor' => '編集者',
    'Review' => 'レビュー',
    'Feedback' => 'フィードバック',
    'Approve' => '承認',
    'Publish' => '発行',
    'guide_create_doc' => '<ul style="line-height: 25px;">
                    <li>資料要求：このアイテムをアクセスするために、先に資料を作成してください。</li>
                    <li>コード：作成したい資料コードを記入</li>
                    <li>ファイルファイル：ファイルをアップロードする、またはファイルのリンクを記入</li>
                    <li>添付ファイル：添付ファイルを追加、有か否か</li>
                    <li>グループ：チーム、または事業部を選択</li>
                    <li>資料タイプ：資料タイプを選択、ない場合、「＋」を押して追加
最終選択</li>
                    <li>セーブ：変更をセーブする（臨時的、後で編集ができる）</li>
                    <li>提出：コーディネーター提出（提出の後で、編集ができない）</li>
                </ul>',
    'guide_document' => '<h4><b>過程</b></h4>
                <ol style="line-height: 25px;">
                    <li>資料発行要求を作成資</li>
                    <li>料要求はCOOによって承認させる</li>
                    <li>資料発行要求を承認、フィードバックがある場合、リクエスト者に送る</li>
                    <li>要求された人は資料を作成</li>
                    <li>資料はレビューアーを選択のため、コーディネーターに送る</li>
                    <li>選択されたレビューアーはフィードバックがあれば、作成者に送る</li>
                    <li>資料は発行人を選択のため、コーディネーターに再送</li>
                    <li>選択された発行人はフィードバックがあれば、作成者に送る</li>
                </ol>
                <p style="color: #3333ff;"><i>コーディネーターはシステムデータ→一般→資料コーディネーターに設定された</i></p>',
    'guide_publish_document' => '<h4><b>資料を発行</b></h4>
                <ul style="line-height: 25px;">
                    <li>「発行」ボタンを押して、ポップアップが出る</li>
                    <li>お知らせしたいグループやアカウントを選択（これらのグループやアカウントのみ発行された資料を見られる）</li>
                    <li>お知らせ必要のタイトルや内容を記入</li>
                    <li>メールを送信</li>
                    <li>「送信」ボタンを押すと、資料は発行される。ミスがあれば、「RE発行」を押して、再発行する</li>
                </ul>',
];
