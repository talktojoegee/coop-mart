@extends('gateway::layouts.master')

@section('content')
    <a href="#" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/paystack.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'total'=>$purchaseData->total])}}" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a href="#" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
   {{-- @forelse ($gateways as $gateway)
        <a href="{{ route('gateway.pay', withOldQueryIntegrity(['gateway' => $gateway->alias])) }}" class="pay-box">
            <div class="grow">
                <img class="image-2" src="{{ asset(config($gateway->alias . '.logo')) }}" alt="{{ __('Image') }}" />
            </div>
        </a>
    @empty
        <a href="javascript:void(0)" onclick="history.back()" class="pay-box">
            <div class="grow">
                <h3>{{ __('No gateway found.') }}</h3>
            </div>
        </a>
    @endforelse--}}
@endsection
