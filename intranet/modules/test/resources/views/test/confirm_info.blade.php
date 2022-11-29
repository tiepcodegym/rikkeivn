@extends('test::layouts.front')

@section('title', trans('test::test.candidate_info'))

@section('content')
    
    <h1 class="page-title text-center">{{ trans('test::test.candidate_info') }}</h1>
    
    <br />
    {!! Form::open(['method' => 'post', 'target' => '_blank', 'route' => 'test::candidate_test']) !!}
    
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
            <table class="table infor-table">
                <tr>
                    <th>{{ trans('test::test.full_name') }}</th>
                    <td>{{ $candidate->fullname }}</td>
                </tr>
                <tr>
                    <th>{{ trans('test::test.email') }}</th>
                    <td>{{ $candidate->email }}</td>
                </tr>
                <tr>
                    <th>{{ trans('test::test.Phone number') }}</th>
                    <td>{{ $candidate->mobile }}</td>
                </tr>
                <tr>
                    <th>{{ trans('test::test.input_candidate_info') }}</th>
                    <td><a class="link" target="_blank" href="{{ route('test::candidate.input_infor') }}">Link</a></td>
                </tr>
                <tr>
                    <th>{{ trans('test::test.select_test') }}</th>
                    <td>
                        <?php 
                        $testTypes = $candidate->listTestTypes(); 
                        $hasTest = false;
                        ?>
                        @if ($testTypes && !$testTypes->isEmpty())
                            <select class="form-control select-search" name="test_type" id='select-type-test-action'>
                                @foreach($testTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <?php $hasTest = true; ?>
                        @else
                            {{ trans('test::validate.test_not_found') }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="text-center start-action-exam">
        <a href="{{ route('test::index') }}" class="btn-delete">{{ trans('test::test.not_me_back') }}</a>
        {!! Form::hidden('candidate_id', $candidate->id) !!}
        @if ($hasTest)
        <button class="btn btn-primary start-test btn-start-test" type="button">{{ trans('test::test.start_test') }}</button>
        <button class="btn btn-primary start-test btn-submit hidden">{{ trans('test::test.start_test') }}</button>
        @endif
    </div>
    {!! Form::close() !!}
    
    <script>
        $(document).bind("contextmenu", function(e) {
            return false;
        });

        $urlCheck = "{{route('test::candidate.check_infor')}}";
        $idCandidate = "{{$candidate->id}}";
        $(document).ready(function(){
            $( ".btn-start-test").click(function( event ) {
                event.preventDefault();
                
                $.ajax({
                    method: "POST",
                    url: $urlCheck,
                    data: {
                        '_token': "{{csrf_token()}}",
                        'id': $idCandidate
                    },
                    success: function (result) {
                       if (!result.status) {
                           alert(result.message);
                       } else if (result.text) {
                            if (confirm(result.text)) {
                                window.open(result.url, '_blank');
                            } else {
                                $('.btn-submit').click();
                            }
                       } else {
                        $('.btn-submit').click();
                       }
                    },
                    error: function (errors) {
                        alert(errors.responseJSON);
                    }
                });
            })
        })
    </script>
@stop
<script>
    var exam_url = "{{ $exam_url }}"
</script>

