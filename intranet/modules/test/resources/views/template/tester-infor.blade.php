<?php
if ($candidate) {
    $testerName = $candidate->fullname;
    $testerEmail = $candidate->email;
    $testerPhone = null;
} else {
    $testerName = $person['name'];
    $testerEmail = $person['email'];
    $testerPhone = $person['phone'];
}
?>
<div class="person_info row">
    <div class="col-md-8 col-md-offset-2">
        <div class="form-group row">
            <label class="col-sm-3">{{ trans('test::test.full_name') }} <em>*</em></label>
            <div class="col-sm-9">
                <strong>{{ $testerName }}</strong>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3">{{ trans('test::test.email') }} <em>*</em></label>
            <div class="col-sm-9">
                <strong>{{ $testerEmail }}</strong>
            </div>
        </div>
        @if ($testerPhone)
        <div class="form-group row">
            <label class="col-sm-3">{{ trans('test::test.phone_number') }} <em>*</em></label>
            <div class="col-sm-9">
                <strong>{{ $testerPhone }}</strong>
            </div>
        </div>
        @endif
        @if ($candidate)
        <input type="hidden" name="candidate" value="{{ $candidate->id }}" />
        @else
        <input type="hidden" name="key_person" value="{{ $keyPerson }}" />
        @endif
    </div>
</div>

