<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Test\Models\WrittenQuestion;

$testLimitQuestion = old('limit_question') ? old('limit_question') : ($item ? (int) $item->limit_question : null);
$testTotalQuestion = old('total_question');
if (!$testTotalQuestion && $item) {
    $testTotalQuestion = $item->total_question;
    if (!$item->total_question) {
        $testTotalQuestion = $item->questions()
                ->where('status', ViewTest::STT_ENABLE)
                ->get()->count();
    }
}
$totalWrittenQuestion = isset($item->total_written_question) && $item->total_written_question ? $item->total_written_question : null;
$displayOption = null;
if ($item) {
    $displayOption = $item->getDisplayOption();
    $currentCollectCats = [];
    if ($collectCats && !$collectCats->isEmpty()) {
        foreach ($collectCats as $type => $catGroup) {
            $currentCollectCats[$type] = $catGroup->lists('name', 'id')->toArray();
        }
    }
}
$writtenCats = WrittenQuestion::listWrittenCatByTestID($item->id);

?>

<div class="row form-group">
    <div class="col-md-6">
        <label style="text-transform: uppercase">
            {{ trans('test::test.question multiple choice') }}:
        </label>
        <div>
            <label style="margin-top:20px">
                <input type="checkbox" name="limit_question" value="1" class="" data-active="#excel_file" id="check_total" 
                       {{ $testLimitQuestion == 1 ? 'checked' : '' }}> {{ trans('test::test.choose_total_question') }}
            </label>
            <input type="number" name="total_question" id="total_q" class="form-control" 
                   min="1" placeholder="{{ trans('test::test.enter_display_question') }}" 
                   value="{{ $testTotalQuestion ? $testTotalQuestion : 1 }}" />
        </div>
        <label style="margin-top:20px">{{ trans('test::test.detail_selection') }}</label>
        <div id="display_option_box" class="margin-bottom-5">
            @if ($displayOption)
                @foreach ($displayOption as $index => $option)
                    @include('test::manage.includes.display-item')
                @endforeach
            @endif
        </div>
        
        <button type="button" id="add_display_option_btn" class="btn btn-primary">
            {{ trans('test::test.add_display_option') }} <i class="fa fa-plus"></i>
        </button>
    </div>
    <div class="col-md-6">
        <label style="text-transform: uppercase">
            {{ trans('test::test.question written') }}:
        </label>
        <div>
            <label style="margin-top:20px">
                <input type="checkbox" name="limit_question" value="1" class="" id="limit_written_question"
                        {{ $testLimitQuestion == 1 ? 'checked' : '' }}> {{ trans('test::test.choose_total_question') }}
            </label>
            <input type="number" name="total_written_question" id="total_q" class="form-control limit_written_question"
                   min="0" placeholder="{{ trans('test::test.enter_display_question') }}"
                   value="{{ $totalWrittenQuestion }}" />
        </div>
        <div>
        <label style="margin-top:20px">
            <input type="checkbox" name="test_id" value="{{ $item->id }}" id="check_written_cat"
                    {{ isset($item['written_cat']) && $item['written_cat'] ? 'checked' : '' }}>
            {{ trans('test::test.written option') }}
        </label>
        <select class="category_4 select-cat form-control ignore written_cat" data-cat="4" name="written_cat"
                {{ !isset($item['written_cat']) || $item['written_cat'] == 0 ? 'disabled' : '' }}>
            <option value="">&nbsp;</option>
            @if (isset($writtenCats) && $writtenCats)
                @foreach ($writtenCats as $key)
                    <option value="{{ $key->cat_id }}" {{ (isset($item['written_cat']) && $item['written_cat'] == $key->cat_id) ? 'selected' : '' }}>{{ $key->name }}</option>
                @endforeach
            @endif
        </select>
    </div>
    </div>
</div>

<script>
    var currentCollectCats = {};
    var test_id = '{{ $item->id }}';
    var url = '{{ route('test::admin.test.getWrittenCat') }}';
    @if (isset($currentCollectCats))
        currentCollectCats = JSON.parse('<?php echo json_encode($currentCollectCats); ?>');
    @endif
    $('#check_written_cat').change(function() {
        var isCheck = $(this).is(':checked');
        $('.written_cat').prop('disabled', !isCheck);
        var dom = '<option value="">&nbsp;</option>';
        if (isCheck === true) {
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    test_id: test_id,
                },
                success: function (data) {
                    if (data.response.length !== 0) {
                        for (let i = 0; i < data.response.length; i++) {
                            let th = '<option value="' + data.response[i].cat_id + '">' + $("<div/>").text(data.response[i].name).html() + '</option>';
                            dom += th;
                        }
                        $(".written_cat").html(dom);
                    }
                },
                fail: function () {
                    alert("Ajax failed to fetch data");
                }
            });
        } else {
            $(".written_cat").html(dom);
        }
    });

    $('#limit_written_question').change(function () {
        var isCheck = $(this).is(':checked');
        $('.limit_written_question').prop('disabled', !isCheck);
    });
    if ($('.limit_written_question').val() == '') {
        $('.limit_written_question').prop('disabled', true);
        $('#limit_written_question').prop('checked', false);
    }
</script>
