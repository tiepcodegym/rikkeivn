<?php
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Resource\View\FreeEffort;

$foreignLangs = CoreView::getLangLevelSplit();
$languages = ['' => '&nbsp;'] + $languages;
$frameworks = ['' => '&nbsp;'] + $frameworks;
?>

<div class="row">
    <div class="col-md-4">
        <h4>{{ trans('resource::view.Available time') }}</h4>

        <table class="table">
            <tr>
                <td><strong>{{ trans('resource::view.From date') }}: </strong></td>
                <td><input type="text" class="form-control filter-grid filter-noauto date-filter" autocomplete="off" id="from_date"
                           name="filter[search][from_date]" value="{{ CoreForm::getFilterData('search', 'from_date') }}"
                           placeholder="Y-m-d"></td>
            </tr>
            <tr>
                <td><strong>{{ trans('resource::view.To date') }}: </strong></td>
                <td>
                    <input type="text" class="form-control filter-grid filter-noauto date-filter" autocomplete="off" id="to_date"
                           name="filter[search][to_date]" value="{{ CoreForm::getFilterData('search', 'to_date') }}"
                           placeholder="Y-m-d">
                    <p class="error hidden" id="to_date_error">{{ trans('resource::message.To date must be greater than From date') }}</p>
                </td>
            </tr>
        </table>
    </div>
    @if ($isScopeCompany)
    <div class="col-md-4">
        <div class="form-group">
            <h4>{{ trans('resource::view.Team') }}: </h4>
            <table class="table">
                <tr>
                    <td>
                        <select class="form-control filter-grid bootstrap-multiselect"
                                multiple name="filter[search][team_id][]">
                            @if ($teamList)
                                <?php $filterTeamId = CoreForm::getFilterData('search', 'team_id'); ?>
                                @foreach ($teamList as $option)
                                <option value="{{ $option['value'] }}" {{ is_array($filterTeamId) && in_array($option['value'], $filterTeamId) ? 'selected' : '' }}>{{ $option['label'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif
    <div class="col-md-4">
        <div class="form-group">
            <h4>{{ trans('resource::view.Employee') }}</h4>
            <table class="table">
                <tr>
                    <td>
                        <input type="text" name="filter[search][name]" class="filter-grid form-control"
                                   value="{{ CoreForm::getFilterData('search', 'name') }}" placeholder="{{ trans('resource::view.Search') }}...">
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4">
        <h4>{{ trans('resource::view.Foreign language') }}</h4>
        <?php
        $aryFilterLangs = [
            FreeEffort::JAPANESE_LANG_ID => [
                'key' => 'ja',
                'name' => trans('sales::view.Japanese'),
                'type' => 'select',
                'options' => $foreignLangs['ja'],
                'values' => isset($dataSearch['foreign']['ja']) ? $dataSearch['foreign']['ja'] : []
            ],
            FreeEffort::ENGLISH_LANG_ID => [
                'key' => 'en',
                'name' => trans('sales::view.English'),
                'type' => 'select',
                'options' => $foreignLangs['en'],
                'values' => isset($dataSearch['foreign']['en']) ? $dataSearch['foreign']['en'] : []
            ]
        ];
        ?>

        <table class="table list-filter-foreigns">
            @foreach ($aryFilterLangs as $langId => $langData)
            <?php
                $langValues = $langData['values'];
            ?>
            <tr>
                <td>{{ $langData['name'] }}</td>
                <td>{!! Form::select(
                        'filter[search][foreign][' . $langData['key'] . '][compare]',
                        $compares,
                        isset($langValues['compare']) ? $langValues['compare'] : null,
                        ['class' => 'form-control filter-grid select-search', 'data-name' => 'compare', 'style' => 'width: 80px;']
                    ) !!}</td>
                <td width="200">
                    @if ($langData['type'] == 'select')
                    <select class="form-control select-search filter-grid select-filter lang-level"
                             name="filter[search][foreign][{{ $langData['key'] }}][level]"
                             style="width: 100%;">
                        <option value="">&nbsp;</option>
                        @foreach ($langData['options'] as $value => $label)
                        <option value="{{ $label }}" {{ isset($langValues['level']) && $langValues['level'] == $label ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @else
                    <input type="{{ $langData['type'] }}" name="filter[search][foreign][{{ $langData['key'] }}][level]" 
                           class="form-control filter-grid lang-level"
                           value="{{ isset($langValues['level']) ? $langValues['level'] : '' }}">
                    @endif
                </td>
            </tr>
            @endforeach
        </table>

    </div>
    <div class="col-lg-4">
        <h4>{{ trans('resource::view.Programing language') }}</h4>
        <table class="table list-filter-items" data-name="filter[search][lang]">
            <tbody>
                <tr>
                    <td>
                        <input type="text" class="form-control filter-grid" name="filter[search][lang_input][name]"
                               placeholder="{{ trans('resource::view.Begin with') }}"
                               value="{{ isset($dataSearch['lang_input']['name']) ? $dataSearch['lang_input']['name'] : null }}">
                    </td>
                    <td>{!! Form::select(
                            'filter[search][lang_input][compare]',
                            $compares,
                            isset($dataSearch['lang_input']['compare']) ? $dataSearch['lang_input']['compare'] : null,
                            ['class' => 'form-control filter-grid select-search']
                        ) !!}</td>
                    <td>{!! Form::select(
                            'filter[search][lang_input][year]',
                            $rangeYears,
                            isset($dataSearch['lang_input']['year']) ? $dataSearch['lang_input']['year'] : null,
                            ['class' => 'form-control filter-grid select-search', 'style' => 'width: 100%']
                        ) !!}</td>
                    <td></td>
                </tr>
                @if (isset($dataSearch['lang']) && $dataSearch['lang'])
                    @foreach ($dataSearch['lang'] as $sIndex => $langSearch)
                        @include('resource::available.search-lang-item', ['searchItem' => $langSearch])
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td class="text-right">
                        <button type="button" class="btn btn-primary btn-add-filter-item" title="{{ trans('resource::view.Add filter') }}"
                                data-template="#filter_lang_item"><i class="fa fa-plus"></i></button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="col-lg-4">
        <h4>{{ trans('resource::view.Framework') }}</h4>
        <table class="table list-filter-items" data-name="filter[search][framework]">
            <tbody>
                @if (isset($dataSearch['framework']) && $dataSearch['framework'])
                    @foreach ($dataSearch['framework'] as $sIndex => $frameworkSearch)
                        @include('resource::available.search-framework-item', ['searchItem' => $frameworkSearch])
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"></td>
                    <td class="text-right">
                        <button type="button" class="btn btn-primary btn-add-filter-item" title="{{ trans('resource::view.Add filter') }}"
                                data-template="#filter_framework_item"><i class="fa fa-plus"></i></button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="hidden">
    @include('resource::available.search-lang-item')
    @include('resource::available.search-framework-item')
</div>
