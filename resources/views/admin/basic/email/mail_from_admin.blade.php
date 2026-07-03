@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{__('Mail From Admin')}}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{route('admin.dashboard')}}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{__('Basic Settings')}}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{__('Email Settings')}}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{__('Mail From Admin')}}</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form action="{{route('admin.mailfromadmin.update')}}" method="post">
          @csrf
          <div class="card-header">
              <div class="row">
                  <div class="col-lg-12">
                      <div class="card-title">{{__('Mail From Admin')}}</div>
                  </div>
              </div>
          </div>
          <div class="card-body pt-5 pb-5">
            <div class="row">
              <div class="col-lg-6 offset-lg-3">
                <div class="alert alert-warning text-center" role="alert">
                    <strong>{{__('This mail address will be used to send all mails from this website.')}}</strong>
                </div>
                @csrf

                {{-- Mail Provider --}}
                <div class="form-group">
                  <label>{{__('Mail Provider')}} **</label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="is_smtp" value="0" class="selectgroup-input" {{$abe->is_smtp == 0 ? 'checked' : ''}}>
                      <span class="selectgroup-button">{{__('PHP Mail')}}</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="is_smtp" value="1" class="selectgroup-input" {{$abe->is_smtp == 1 ? 'checked' : ''}}>
                      <span class="selectgroup-button">{{__('SMTP')}}</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="is_smtp" value="2" class="selectgroup-input" {{$abe->is_smtp == 2 ? 'checked' : ''}}>
                      <span class="selectgroup-button">{{__('Brevo API')}}</span>
                    </label>
                  </div>
                  @if ($errors->has('is_smtp'))
                    <p class="mb-0 text-danger">{{$errors->first('is_smtp')}}</p>
                  @endif
                </div>

                {{-- SMTP Fields --}}
                <div id="smtp-fields" style="{{$abe->is_smtp == 1 ? '' : 'display:none'}}">
                  <div class="form-group">
                      <label>{{__('SMTP Host')}} **</label>
                      <input class="form-control" name="smtp_host" value="{{$abe->smtp_host}}">
                      @if ($errors->has('smtp_host'))
                          <p class="mb-0 text-danger">{{$errors->first('smtp_host')}}</p>
                      @endif
                  </div>
                  <div class="form-group">
                      <label>{{__('SMTP Port')}} **</label>
                      <input class="form-control" name="smtp_port" value="{{$abe->smtp_port}}">
                      @if ($errors->has('smtp_port'))
                          <p class="mb-0 text-danger">{{$errors->first('smtp_port')}}</p>
                      @endif
                  </div>
                  <div class="form-group">
                      <label>{{__('Encryption')}} **</label>
                      <input class="form-control" name="encryption" value="{{$abe->encryption}}">
                      @if ($errors->has('encryption'))
                          <p class="mb-0 text-danger">{{$errors->first('encryption')}}</p>
                      @endif
                  </div>
                  <div class="form-group">
                      <label>{{__('SMTP Username')}} **</label>
                      <input class="form-control" name="smtp_username" value="{{$abe->smtp_username}}">
                      @if ($errors->has('smtp_username'))
                          <p class="mb-0 text-danger">{{$errors->first('smtp_username')}}</p>
                      @endif
                  </div>
                  <div class="form-group">
                      <label>{{__('SMTP Password')}} **</label>
                      <input class="form-control" type="password" name="smtp_password" value="" placeholder="Leave blank to keep current">
                      @if ($errors->has('smtp_password'))
                          <p class="mb-0 text-danger">{{$errors->first('smtp_password')}}</p>
                      @endif
                  </div>
                </div>

                {{-- Brevo API Fields --}}
                <div id="brevo-fields" style="{{$abe->is_smtp == 2 ? '' : 'display:none'}}">
                  <div class="form-group">
                      <label>{{__('Brevo API Key')}} **</label>
                      <input class="form-control" type="password" name="smtp_password" value="" placeholder="Leave blank to keep current">
                      <small class="text-muted">Get your API key from <a href="https://app.brevo.com/settings/keys/api" target="_blank">app.brevo.com</a></small>
                      @if ($errors->has('smtp_password'))
                          <p class="mb-0 text-danger">{{$errors->first('smtp_password')}}</p>
                      @endif
                  </div>
                </div>

                {{-- From Email & Name (always shown) --}}
                <div class="form-group">
                    <label>{{__('From Email')}} **</label>
                    <input class="form-control" type="email" name="from_mail" value="{{$abe->from_mail}}">
                    @if ($errors->has('from_mail'))
                        <p class="mb-0 text-danger">{{$errors->first('from_mail')}}</p>
                    @endif
                </div>
                <div class="form-group">
                    <label>{{__('From Name')}} **</label>
                    <input class="form-control" name="from_name" value="{{$abe->from_name}}">
                    @if ($errors->has('from_name'))
                        <p class="mb-0 text-danger">{{$errors->first('from_name')}}</p>
                    @endif
                </div>

                {{-- Test Email Result --}}
                <div id="test-mail-result" class="mt-2" style="display:none"></div>

              </div>
            </div>
          </div>
          <div class="card-footer">
            <div class="form">
              <div class="form-group from-show-notify row">
                <div class="col-12 text-center">
                  <button type="submit" class="btn btn-success">{{__('Update')}}</button>
                  <button type="button" id="btn-test-mail" class="btn btn-info ml-2">{{__('Send Test Email')}}</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@push('scripts')
<script>
  (function () {
    var radios = document.querySelectorAll('input[name="is_smtp"]');
    var smtpFields  = document.getElementById('smtp-fields');
    var brevoFields = document.getElementById('brevo-fields');

    function toggleFields(val) {
      smtpFields.style.display  = (val === '1') ? '' : 'none';
      brevoFields.style.display = (val === '2') ? '' : 'none';
    }

    radios.forEach(function (r) {
      r.addEventListener('change', function () { toggleFields(this.value); });
    });

    document.getElementById('btn-test-mail').addEventListener('click', function () {
      var btn    = this;
      var result = document.getElementById('test-mail-result');
      btn.disabled = true;
      btn.textContent = 'Sending…';
      result.style.display = 'none';

      fetch('{{ route("admin.mailfromadmin.test") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ?
            document.querySelector('meta[name="csrf-token"]').content :
            '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        result.style.display = '';
        result.className = 'mt-2 alert ' + (data.success ? 'alert-success' : 'alert-danger');
        result.textContent = data.message;
      })
      .catch(function (e) {
        result.style.display = '';
        result.className = 'mt-2 alert alert-danger';
        result.textContent = 'Request failed: ' + e.message;
      })
      .finally(function () {
        btn.disabled = false;
        btn.textContent = '{{ __("Send Test Email") }}';
      });
    });
  }());
</script>
@endpush
