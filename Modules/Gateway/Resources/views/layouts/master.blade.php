<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- Favicon icon -->
    @include('gateway::partial.favicon')
    <title>{{ __('Gateway') }}</title>
    <link href="{{ asset('Modules/Gateway/Resources/assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('Modules/Gateway/Resources/assets/css/gateway.min.css') }}">
    @yield('css')
</head>

<body>
    <section class="card1">
        <div class="payment-loader">
            <div class="sp sp-circle"></div>
        </div>
        <div class="svg-1">
            @include('gateway::partial.logo')
        </div>
        <p class="para-1">{{ __('Amount to be paid') }}</p>
        <p class="para-2">{{ formatNumber($purchaseData->total) }}</p>
        <p class="para-1">{{ __('currency') }}</p>
        <p class="para-3 para-2">{{ $purchaseData->currency_code }}</p>
        <p class="para-6">{{ __('Choose Payments from below') }}</p>
        <div class="straight-line"></div>

        <div>
            {{--@include('gateway::partial.errors')--}}
            <div class="test justify-content-center">
               {{-- @yield('content')--}}
                <div class="form-group mt-3">
                    <label for="">Payment Method</label>
                    <select name="paymentMethod" id="paymentMethod" class="form-control">
                        <option disabled selected>-- Select payment method --</option>
                        <option value="1">COOP Savings</option>
                        <option value="2">COOP Loans</option>
                    </select>
                </div>
                <div class="form-group mt-3" id="loanPaymentMethod">
                    @if(!is_null($loanCollection))
                        <button class="btn btn-primary">Proceed</button>
                    @endif
                </div>
                <div class="form-group mt-3" id="savingPaymentMethod">
                    @if(!is_null($savingsCollection))
                        <button class="btn btn-primary">Proceed</button>
                    @endif
                </div>
            </div>
        </div>
        <a href="#" class="d-flex my-4 position-relative back return">
            <svg class="return-arrow position-absolute" xmlns="http://www.w3.org/2000/svg" width="15"
                height="10" viewBox="0 0 15 10" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M4.70711 0L6.12132 1.41421L3.82843 3.70711H13.4142C13.9665 3.70711 14.4142 4.15482 14.4142 4.70711C14.4142 5.25939 13.9665 5.70711 13.4142 5.70711H3.82843L6.12132 8L4.70711 9.41421L0 4.70711L4.70711 0Z" fill="currentColor" />
            </svg> {{ __('Close') }}</a>

    </section>
    <script>
        var response = {
            status: "failed",
            message: "{{ __('Payment cancelled.') }}"
        }
    </script>
    <script src="{{ asset('Modules/Gateway/Resources/assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('Modules/Gateway/Resources/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('Modules/Gateway/Resources/assets/js/app.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            $('#loanPaymentMethod').hide();
            $('#savingPaymentMethod').hide();
            $('#paymentMethod').on('change',function(e){
                e.preventDefault();
                let val = $(this).val();
                if(val === 1){
                    $('#savingPaymentMethod').show();
                    $('#loanPaymentMethod').hide();
                }else if(val === 2){
                    $('#savingPaymentMethod').hide();
                    $('#loanPaymentMethod').show();
                }
            });
        });
    </script>
    @yield('js')
</body>

</html>
