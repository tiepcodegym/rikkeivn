function formatRepo(e){if(e.loading)return e.text;var t="<div class='select2-result-repository clearfix'><div class='select2-result-repository__avatar' style='float: left;'><img style='border-radius: 50px;width: 50px; height: 50px;' src='"+e.avatar+"' /></div><div class='select2-result-repository__meta select2_text' style='float: left; padding: 17px 10px 17px 10px; font-size: 13px;'><div class='select2-result-repository__title'>"+e.text+"</div></div></div>";return t}function formatRepoSelection(e){return e.text}function select2Employees(e){$(e).select2({ajax:{url:$(e).data("remote-url"),dataType:"json",delay:250,data:function(e){return{q:e.term,page:e.page}},processResults:function(e,t){return t.page=t.page||1,{results:e.items,pagination:{more:5*t.page<e.total_count}}},cache:!0},placeholder:chooseEmployeeText,escapeMarkup:function(e){return e},minimumInputLength:3,templateResult:formatRepo,templateSelection:formatRepoSelection,maximumSelectionSize:5})}