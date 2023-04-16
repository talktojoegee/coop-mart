@extends('gateway::layouts.master')

@section('content')
    <a data-toggle="modal" data-target="#coopSavingsModal" href="javascript:void(0);" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop_savings.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a data-toggle="modal" data-target="#coopSavingsModal" href="javascript:void(0);" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop_loan.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a data-toggle="modal" data-target="#paystackModal" href="javascript:void(0);" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/paystack.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>

    <div class="modal fade" id="coopSavingsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Are you sure?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to pay with your <strong>COOP Savings?</strong></p>
                    <div class="btn-group mt-2">
                        <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'payment_method'=>'savings'])}}" class="btn pay-box">Yes</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="coopLoanModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Are you sure?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to pay with your <strong>COOP Loan?</strong></p>
                    <div class="btn-group mt-2">
                        <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'payment_method'=>'loan'])}}" class="btn pay-box">Yes</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="paystackModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Are you sure?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>You will be re-directed to a third-party payment gateway to complete this transaction. Will like to proceed?</p>
                    <div class="btn-group mt-2">
                        <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'payment_method'=>'paystack'])}}" class="btn pay-box">Yes</a>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
