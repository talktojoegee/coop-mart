@extends('gateway::layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <h5>COOP Savings</h5>
            <form action="{{route('coop_savings.process-coop_savings_payment')}}" method="POST">
                @csrf
                <div class="form-group">
                    <input type="hidden" name="memberId" value="{{Auth::user()->member_id}}">
                    <input type="hidden" name="total" value="{{$purchaseData->total ?? 0 }}">
                    <input type="hidden" name="code" value="{{$purchaseData->code ?? 0 }}">
                    <button class="btn btn-primary btn-lg mt-2">Pay Now</button>
                </div>
            </form>
        </div>
    </div>
@endsection
