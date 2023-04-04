@extends('admin.layouts.app')
@section('page_title', __('Shop'))
@section('css')
    {{-- Select2  --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('datta-able/plugins/select2/css/select2.min.css') }}">
@endsection
@section('content')

    <!-- Main content -->
    <div class="col-sm-12 list-container" id="shop-list-container">
        <div class="card">
            <div class="card-header">
                <h5><a href="">{{ __('Shop') }}</a></h5>
            </div>

            helsksk

            <div class="card-body p-0">
                <div class="card-block pt-2 px-2">
                    <div class="col-sm-12">
                       hello
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('js')

@endsection

