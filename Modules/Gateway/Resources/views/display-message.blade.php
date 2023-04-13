@extends('gateway::layouts.message')

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if($status == 200)
                <div class="alert alert-success">{{$message ?? ''}}</div>
            @elseif($status == 400)
                <div class="alert alert-success">{{$message ?? ''}}</div>
            @endif
        </div>
    </div>
@endsection
