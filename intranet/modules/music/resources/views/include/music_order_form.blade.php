<div class="music-form row">
    <div class="col-md-2"></div>
    <div class="col-md-10">
        <div class="box box-order">
            <h2 class="align-center">Order Music</h2>
            <div class="with-border music-order">
                @if(Session::has('error'))
                <div class="alert alert-warning">
                    <ul>
                        <li>
                            {{ Session::get('error') }}
                        </li>
                    </ul>
                </div>
                @endif
                @if(Session::has('saveSuccess'))
                <div class="alert alert-success">
                    <ul>
                        <li>
                            {{ Session::get('saveSuccess') }}
                        </li>
                    </ul>
                </div>
                @endif
                <form id="form-order-music" method="post" action="{{ URL::route('music::order.save') }}">
                    <div class="box-body">
                        {!! csrf_field() !!}
                        @if ($office_id)
                        <input type="hidden" name="music[office_id]" value="{{ $office_id }}" />
                        @endif
                        <div class="form-group">
                            <label for="link" class="control-label required">{{ trans('music::view.Link') }} <em>*</em></label>
                            <input name="music[link]" class="form-control input-field" type="text" id="link" 
                                   value="" placeholder="{{ trans('http://...') }}" />
                        </div>
                        <div class="form-group">
                            <label for="name" class="control-label required">{{ trans('music::view.Song') }} <em>*</em></label>
                            <input name="music[name]" class="form-control input-field" type="text" id="name" 
                                   value="" placeholder="{{ trans('music::view.Name Song') }}..." />
                        </div>
                        <div class="form-group">
                            <label for="sender" class="control-label">{{ trans('music::view.Sender') }}</label>
                            <input name="music[sender]" class="form-control input-field" type="text" id="sender" 
                                   value="" placeholder="{{ trans('music::view.People Name') }}..." />
                        </div>
                        <div class="form-group">
                            <label for="receiver" class="control-label">{{ trans('music::view.Receiver') }}</label>
                            <input name="music[receiver]" class="form-control input-field" type="text" id="receiver" 
                                   value="" placeholder="{{ trans('music::view.People Name') }}..." />
                        </div>
                        <div class="form-group">
                            <label for="message-order" class="control-label required">{{ trans('music::view.Message order') }} <em>*</em></label>
                            <textarea name="music[message]" class="form-control" rows="3" id="message-order" placeholder="{{ trans('music::view.With Message') }}..."></textarea>
                        </div>
                        <div class="box-footer align-center">
                            <button type="submit" class="btn btn-order">{{trans('music::view.Send')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- <div class="col-md-1"></div> -->
</div>

