<?php

return [
    'nominate_email_subject' => 'Thông báo về cuộc ứng cử/đề cử - :vote_title',
    'nominate_email_content' => '<p>Mời bạn tham gia ứng cử/đề cử "<strong>:vote_title</strong>".</p>'
                            . '<p><a href=":self_nominate_link">Link ứng cử</a>, <a href=":nominate_link">Link đề cử</a></p>'
                            . ':start_content'
                            . ':end_content'
                            . '<p>Bạn có thể đề cử tối đa: :nominee_max người.</p>',
    'start_nominate_content' => '<p>Thời gian bắt đầu ứng cử/đề cử: :nominate_start.</p>',
    'end_nominate_content' => '<p>Thời gian kết thúc ứng cử/đề cử: :nominate_end.</p>',
    'vote_email_subject' => 'Thông báo về cuộc vote - :vote_title',
    'vote_email_content' => '<p>Mời bạn tham gia vote "<strong>:vote_title</strong>" <a href=":vote_link">tại đây</a>.</p>'
                            . '<p>Thời gian bắt đầu vote: :vote_start.</p>'
                            . '<p>Thời gian kết thúc vote: :vote_end.</p>'
                            . '<p>Bạn có thể vote tối đa: :vote_max người.</p>',
    'words_in_symbol_will_replace_with' => 'Các từ ở trong :symbol sẽ được thay thế bởi:',
    'confirm_nominate_from_vote' => 'Xác nhận được đề cử - :vote_title'
];

