@extends('gateway::layouts.master')

@section('css')
    <style>
        /* The Modal (background) */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        /* Modal Content/Box */
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }

        /* The Close Button */
        .close {
            color: #aaa;
            float: right;
            font-size: 14px;
            font-weight: bold;
        }
        .close-addon{
            border-radius: 50%;
            border: 1px solid #ccc;
            color: #fff;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
            color: #fff;
        }
    </style>
@endsection
@section('content')
    <a   id="savingsBtn"  href="javascript:void(0);" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop_savings.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a  id="loanBtn" href="javascript:void(0);" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/coop_loan.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>
    <a id="paystackBtn" href="javascript:void(0);" class="pay-box">
        <div class="grow">
            <img class="image-2" src="{{ asset('public/dist/img/paystack.png') }}" alt="{{ __('Image') }}" />
        </div>
    </a>


@endsection

@section('modal')
    <div class="modal fade" id="coopSavingsModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Are you sure?</h5>
                    <button type="button" class="close close-addon" data-dismiss="modal" aria-label="Close">
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


    <div id="savingsModal" class="modal">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Are you sure?</h5>
                <button type="button" class="close close-addon">
                    <span aria-hidden="true">x</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to pay with your <strong>COOP Savings?</strong></p>
                <div class="btn-group mt-2">
                    <a href="{{route('gateway.coop_savings.confirmation',['code'=>$purchaseData->code, 'payment_method'=>'savings'])}}" class="btn pay-box">Yes</a>
                    <button type="button" class="btn btn-secondary close" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div id="loanModal" class="modal">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Are you sure?</h5>
                <button type="button" class="close close-addon">
                    <span aria-hidden="true">x</span>
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

    <div id="paystackModal" class="modal">
        <div class="modal-content animate-top">
            <div class="modal-header">
                <h5 class="modal-title">Are you sure?</h5>
                <button type="button" class="close">
                    <span aria-hidden="true">x</span>
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
@endsection

@section('js')
    <script>
        let savingsModal = $('#savingsModal');
        let loansModal = $('#loanModal');
        let paystackModal = $('#paystackModal');
        let savingBtn = $("#savingsBtn");
        let loanBtn = $("#loanBtn");
        let paystackBtn = $("#paystackBtn");
        var span = $(".close");

        $(document).ready(function(){
            savingBtn.on('click', function() {
                savingsModal.show();
            });
            loanBtn.on('click', function() {
                loansModal.show();
            });
            paystackBtn.on('click', function() {
                paystackModal.show();
            });
            span.on('click', function() {
                savingsModal.fadeOut();
                loansModal.fadeOut();
                paystackModal.fadeOut();
            });
        });
        $('body').bind('click', function(e){
            if($(e.target).hasClass("modal")){
                savingsModal.fadeOut();
                loansModal.fadeOut();
                paystackModal.fadeOut();
            }
        });
    </script>
@endsection
