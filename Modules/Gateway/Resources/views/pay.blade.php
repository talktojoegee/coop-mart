@extends('gateway::layouts.master')

@section('content')
    <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'payment_method'=>'savings'])}}" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop_savings.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'payment_method'=>'loan'])}}" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop_loan.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'payment_method'=>'paystack'])}}" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/paystack.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
@endsection
