<?php
use Rikkei\Vote\View\VoteConst;
if (!isset($vote)) {
    $vote = null;
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label required">{{ trans('vote::view.title') }} <em>*</em></label>
            <div class="col-sm-8">
                @if ($permissEdit)
                <input type="text" name="title" data-value="{{ $vote ? $vote->title : '' }}" 
                       value="{{ old('title') === null ? $vote ? $vote->title : '' : old('title') }}" class="form-control vote-field" placeholder="{{ trans('vote::view.title') }}">
                @else
                <div class="view-value">{{ $vote ? $vote->title : '' }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label required">{{ trans('vote::view.status') }} <em>*</em></label>
            <div class="col-sm-8">
                @if ($permissEdit)
                <select name="status" class="form-control select-search vote-field" data-value="{{ $vote ? $vote->status : '' }}">
                    <?php 
                    $statuses = VoteConst::getVoteStatuses(); 
                    $oldStatus = old('status');
                    if ($oldStatus === null && $vote) {
                        $oldStatus = $vote->status;
                    }
                    ?>
                    @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" {{ $oldStatus == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @else
                <div class="view-value">{{ $vote ? $vote->getStatusLabel() : '' }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label">{{ trans('vote::view.nominate_start_at') }}</label>
            <div class="col-sm-8">
                <?php $nominateStartAt = $vote ? $vote->nominate_start_at ? $vote->nominate_start_at->format('Y-m-d H:i') : null : null ?>
                @if ($permissEdit)
                <input type="text" name="nominate_start_at" data-value="{{ $nominateStartAt }}"
                       value="{{ old('nominate_start_at') === null ? $nominateStartAt ? $nominateStartAt : '' : old('nominate_start_at') }}" 
                       class="form-control vote-field input_date" placeholder="YYYY-MM-DD">
                @else
                <div class="view-value">{{ $nominateStartAt }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label">{{ trans('vote::view.nominate_end_at') }}</label>
            <div class="col-sm-8">
                <?php $nominateEndAt = $vote ? $vote->nominate_end_at ? $vote->nominate_end_at->format('Y-m-d H:i') : null : null ?>
                @if ($permissEdit)
                <input type="text" name="nominate_end_at" data-value="{{ $nominateEndAt }}"
                       value="{{ old('nominate_end_at') === null ? $nominateEndAt ? $nominateEndAt : '' : old('nominate_end_at') }}" 
                       class="form-control vote-field input_date" placeholder="YYYY-MM-DD">
                @else
                <div class="view-value">{{ $nominateEndAt }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label required">{{ trans('vote::view.vote_start_at') }} <em>*</em></label>
            <div class="col-sm-8">
                <?php $voteStartAt = $vote ? $vote->vote_start_at->format('Y-m-d H:i') : null ?>
                @if ($permissEdit)
                <input type="text" name="vote_start_at" data-value="{{ $voteStartAt }}"
                       value="{{ old('vote_start_at') === null ? $voteStartAt ? $voteStartAt : '' : old('vote_start_at') }}" 
                       class="form-control vote-field input_date" placeholder="YYYY-MM-DD">
                @else
                <div class="view-value">{{ $voteStartAt }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label required">{{ trans('vote::view.vote_end_at') }} <em>*</em></label>
            <div class="col-sm-8">
                <?php $voteEndAt = $vote ? $vote->vote_end_at->format('Y-m-d H:i') : null; ?>
                @if ($permissEdit)
                <input type="text" name="vote_end_at" data-value="{{ $voteEndAt }}"
                       value="{{ old('vote_end_at') === null ? $voteEndAt ? $voteEndAt : '' : old('vote_end_at') }}" 
                       class="form-control vote-field input_date" placeholder="YYYY-MM-DD">
                @else
                <div class="view-value">{{ $voteEndAt }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label">{{ trans('vote::view.nominee_max') }}</label>
            <div class="col-sm-8">
                <?php $nomineeMax = $vote ? $vote->nominee_max !== null ? $vote->nominee_max : null : null; ?>
                @if ($permissEdit)
                <input type="number" min="0" step="1" name="nominee_max" data-value="{{ $vote ? $vote->nominee_max : null }}"
                       value="{{ (old('nominee_max') === null) ? ($nomineeMax !== null) ? $nomineeMax : null : old('nominee_max') }}" class="form-control vote-field">
                @else
                <div class="view-value">{{ $nomineeMax }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group row">
            <label class="col-sm-4 control-label">{{ trans('vote::view.vote_max') }}</label>
            <div class="col-sm-8">
                <?php $voteMax = $vote ? $vote->vote_max !== null ? $vote->vote_max : null : null; ?>
                @if ($permissEdit)
                <input type="number" min="0" step="1" name="vote_max" data-value="{{ $vote ? $vote->vote_max : null }}"
                       value="{{ (old('vote_max') === null) ? ($voteMax !== null) ? $voteMax : null : old('vote_max') }}" class="form-control vote-field">
                @else
                <div class="view-value">{{ $voteMax }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group row">
            <label class="col-sm-4 col-md-2 control-label required">{{ trans('vote::view.description') }} <em>*</em></label>
            <div class="col-sm-8 col-md-10">
                <?php
                $voteContent = $vote ? $vote->content : '';
                ?>
                @if ($permissEdit)
                <textarea name="content" id="vote_content" data-value="" class="form-control vote-field no-resize" rows="3" placeholder="{{ trans('vote::view.description') }}">{!! old('content') === null ? htmlentities($voteContent) : old('content') !!}</textarea>
                @else
                <div class="view-value">{!! $voteContent !!}</div>
                @endif
            </div>
        </div>
    </div>
</div>

