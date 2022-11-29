<div class="form-group">
    <label class="col-md-3 control-label">{{ trans('welfare::view.Employee') }}</label>
    <div class="col-md-9">
        <input type="text" class="form-control" id="name-employee" disabled value="{{$costEmployee['name']}}">
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">{{ trans('welfare::view.Employee fee') }}</label>
    <div class="col-md-9">
        <input type="text" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" class="form-control convert_format_number text-right" value="{{number_format($costEmployee['em'])}}" id="emFee" min="1">
        <span class="hidden" id="error-money-emfee-length-9" style="color:red">{{ trans('welfare::view.Required cost length 9') }}</span>
    </div>
</div>
<div class="form-group">
    <label class="col-md-3 control-label">{{ trans('welfare::view.Company fee') }}</label>
    <div class="col-md-9">
        <input type="text" onkeyup="if (/\D/g.test(this.value)) this.value = this.value.replace(/\D/g,'')" class="form-control convert_format_number text-right" value="{{number_format($costEmployee['com'])}}" id="comFee" min="1">
        <span class="hidden" id="error-money-comfee-length-9" style="color:red">{{ trans('welfare::view.Required cost length 9') }}</span>
    </div>
</div>

