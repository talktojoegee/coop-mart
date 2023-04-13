@extends('gateway::layouts.message')

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if($status == 200)
                <img class="image-2 mb-2" src="{{ asset('public/dist/img/coop.png') }}" alt="{{ __('Image') }}" />
                <div class="alert alert-success">{{$message ?? ''}}</div>
            @elseif($status == 400)
                <img class="image-2 mb-2" src="{{ asset('public/dist/img/coop.png') }}" alt="{{ __('Image') }}" />
                <div class="alert alert-success">{{$message ?? ''}}</div>
            @endif
        </div>
    </div>
@endsection
