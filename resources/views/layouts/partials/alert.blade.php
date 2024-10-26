<div class="container">
    <div class="justify-content-center">
@if (Session::has('success'))
    <div id="success" class="alert alert-success alert-dismissible show" role="alert" style="background-color: #d4edda !important;
    color: black !important;
    border: solid 1px #d4edda;">
        {{Session::get('success')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="closeAlert('success')">
            <span aria-hidden="true">×</span>
        </button>
    </div>
@elseif(Session::has('error'))
    <div id="error" class="alert alert-error alert-dismissible show" role="alert" style="background-color: #ffafa5 !important;
color: black !important;
border: solid 1px #d4edda;">
        {{Session::get('error')}}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="closeAlert('error')">
            <span aria-hidden="true">×</span>
        </button>
    </div>
@endif
    </div>
</div>
