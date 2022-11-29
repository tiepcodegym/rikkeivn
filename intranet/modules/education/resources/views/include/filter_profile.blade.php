<?php
if (!isset($domainTrans) || !$domainTrans) {
    $domainTrans = 'team';
}
?>
<div class="filter-action">
    @if (isset($idProject))
        <a href="{{ route('project::task.index.approve', ['id' => $idProject ]) }}" class="btn btn-primary pull-left" target="_blank"><span>{{trans('project::view.History approved')}}</span></a>
    @endif
    @if(isset($buttons) && count($buttons))
        @foreach ($buttons as $button)
            @if (isset($button['type']) && $button['type'] == 'link')
                <a href="<?php if (isset($button['url']) && $button['url']): ?>{{ $button['url'] }}<?php endif; ?>"
                   class="add-general-task {{isset($button['class']) ? ' ' . $button['class'] : '' }}"
                   <?php if (isset($button['disabled']) && $button['disabled']): ?> disabled<?php endif; ?>
                   <?php if (isset($button['url']) && $button['url']): ?> data-url="{{ $button['url'] }}"<?php endif; ?>
                    <?php if (isset($button['option']) && $button['option']): ?>{{ $button['option'] }}<?php endif; ?>
                >{!!isset($button['label_prefix']) ? $button['label_prefix'] : ''!!}{!! trans($domainTrans . '::view.' . $button['label']) !!}</a>
            @else
                <button type="button" class="add-general-task btn btn-primary{{ isset($button['class']) ? ' ' . $button['class'] : '' }}"
                        <?php if (isset($button['disabled']) && $button['disabled']): ?> disabled<?php endif; ?>
                        <?php if (isset($button['url']) && $button['url']): ?> data-url="{{ $button['url'] }}"<?php endif; ?>
                >{!! trans($domainTrans . '::view.' . $button['label']) !!}<?php if (isset($button['icon_refresh']) && $button['icon_refresh']): ?> <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i><?php endif; ?></button>
            @endif
        @endforeach
    @endif
    @if(isset($isSettingVideo))
        <a type="button" class="btn btn-primary margin-right-50" target="_blank" href="{{route('slide_show::create-video-default')}}">{{trans('slide_show::view.Add')}}</a>
        <div class="col-md-4">
            <div class="form-group">
                <label for="password" class=" col-sm-4" stype="margin-top: 7px">{{trans('slide_show::view.Password slide screen')}}:</label>
                <span class="col-sm-5">
                    @if ($password)
                        <input type="text" class="form-control" id="password" name="password" value="{{$password->value}}" placeholder="{{trans('slide_show::view.Password')}}" style="border-radius: 4px">
                    @else
                        <input type="text" class="form-control" id="password" name="password" placeholder="{{trans('slide_show::view.Password')}}">
                    @endif
                </span>
                <div>
                    <button type="button" class="btn btn-primary" id="edit-password" data-message-success="{{trans('slide_show::message.Update paswword success')}}" data-message-error="{{trans('slide_show::message.Update paswword error')}}">{{trans('slide_show::view.Submit')}}</button>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group">
                <label for="password" class=" col-sm-3" stype="margin-top: 7px">{{trans('slide_show::view.Birthday company')}}:</label>
                <span class="col-sm-7">
                    <div class="input-group ">
                        @if ($birthday)
                            <input type="text" class="form-control date-picker" id="birthday_company" name="birthday_company" value="{{$birthday->value}}" placeholder="{{trans('slide_show::view.YYYY-mm-dd H:i A')}}" style="border-radius: 4px">
                        @else
                            <input type="text" class="form-control date-picker" id="birthday_company" name="birthday_company" placeholder="{{trans('slide_show::view.YYYY-mm-dd H:i A')}}">
                        @endif
                        <span class="input-group-btn">
                            <button class="btn btn-default calendar-button" type="button"><i class="fa fa-calendar"></i></button>
                        </span>
                    </div>
                </span>
                <div>
                    <button type="button" class="btn btn-primary" id="edit-birhtday" data-message-success="{{trans('slide_show::message.Update birthday company success')}}" data-message-error="{{trans('slide_show::message.Update birthday company error')}}">{{trans('slide_show::view.Submit')}}</button>
                </div>
            </div>
        </div>
    @endif
    @if (isset($css) && count($css))
        <a href="{{ route('project::point.edit', ['id' => $css->projs_id]) }}" target="_blank" class="link-workorder-filter">{{ trans('sales::view.Project Report') }}</a>
        <a href="{{ route('project::project.edit', ['id' => $css->projs_id]) }}" target="_blank" class="link-workorder-filter">{{ trans('sales::view.View workorder') }}</a>
    @endif

    <button class="btn btn-primary btn-reset-filter">
        <span>{{ trans($domainTrans . '::view.Reset filter') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
    <button class="btn btn-primary btn-search-filter">
        <span>{{ trans($domainTrans . '::view.Search') }} <i class="fa fa-spin fa-refresh hidden"></i></span>
    </button>
</div>
