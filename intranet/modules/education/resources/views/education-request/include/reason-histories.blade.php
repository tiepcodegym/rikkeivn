@if($education['reason'] && count($education['reason']))
    @foreach($education['reason'] as $key => $item)
        <div class="card card-white">
            <div class="reason-heading">
                <div class="float-left image">
                    <img src="{{ $item['hr']['avatar_url'] }}" class="img-circle avatar" alt="user profile image">
                </div>
                <div class="float-left meta">
                    <div class="title h5">
                        <strong>{{ $item['hr']['name'] }}</strong>
                    </div>
                    <h6 class="text-muted time">{{ $item->created_at }}</h6>
                </div>
            </div>
            <div class="reason-description">
                {{ $item->description }}
            </div>
        </div>
    @endforeach
@endif