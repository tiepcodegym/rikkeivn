<div class="box box-rikkei">
    <div class="box-header with-border">
        <h3 class="box-title">{{ trans('test::test.Language') }}</h3>
    </div>
    <div class="box-body">
        <?php
        $currentTestLang = isset($testLangs[$currentLang]) ? $testLangs[$currentLang] : null;
        $langParams = [];
        if ($isIgnored = request()->get('ignore_testing')) {
            $langParams['ignore_testing'] = $isIgnored;
        }
        if ($currentTestLang) {
            $langParams = array_merge($langParams, [
                'test_lang_id' => $currentTestLang->id,
                'group_id' => $currentTestLang->group_id
            ]);
        }
        $langGroupId = null; //check case create
        ?>
        @foreach ($allLangs as $langCode => $langName)
        <div class="form-group">
            @if ($currentLang == $langCode)
                <label class="text-green"><i class="fa fa-check"></i> <strong>{{ $langName }}: </strong></label>
                <div><span class="text-blue">{{ trans('test::test.Current page item') }}</span></div>
            @else
                <?php
                $testLang = isset($testLangs[$langCode]) ? $testLangs[$langCode] : null;
                $langParams['lang'] = $langCode;
                if ($testLang) {
                    $routeEditCreate = 'test::admin.test.edit';
                    $langParams['id'] = $testLang->id;
                    if (!isset($langParams['test_lang_id'])) {
                        $langParams['test_lang_id'] = $testLang->id;
                    }
                    if (!$langGroupId) {
                        $langGroupId = $testLang->group_id;
                    }
                    $linkIcon = '<i class="fa fa-edit"></i>';
                } else {
                    unset($langParams['id']);
                    $routeEditCreate = 'test::admin.test.create';
                    $linkIcon = '<i class="fa fa-plus"></i>';
                }
                ?>
                <label>{{ $langName }}: &nbsp;&nbsp;<a href="{{ route($routeEditCreate, $langParams) }}" class="link">{!! $linkIcon !!}</a></label>
                <input type="text" class="form-control" disabled value="{{ $testLang ? $testLang->name : null }}" />
                <input type="hidden" name="group_ids[{{ $langCode }}]" value="{{ $testLang ? $testLang->id : null }}" />
            @endif
        </div>
        @endforeach
        <input type="hidden" name="group_id" value="{{ $langGroupId }}" />
        <input type="hidden" name="lang_code" value="{{ $currentLang }}" />
    </div>
</div>