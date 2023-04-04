@extends('admin.layouts.app')
@section('page_title', __('Appearance'))
@section('css')
    <link rel="stylesheet" href="{{ Module::asset('cms:css/style.min.css') }}">
    <link href="{{ Module::asset('cms:css/draganddrop.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ Module::asset('mediamanager:css/media-manager.min.css') }}">

    {{-- Color picker --}}
    <link rel="stylesheet" href="{{ asset('datta-able/plugins/mini-color/css/jquery.minicolors.min.css') }}">

@endsection
@section('content')
    <div class="col-sm-12 list-container mt-10">
        <div class="card">
            <div class="card-body" id="main-appearance">
                @include('cms::theme.appearance')
            </div>
        </div>
    </div>
    @include('mediamanager::image.modal_image')
    @include('cms::partials.themes.page.add-layout')
    @include('cms::partials.themes.page.edit-layout')

    {{-- Delete modal --}}
    @include('admin.layouts.includes.delete-modal')
@endsection
@section('js')
    <script>
        'use strict';
        var appearance_menu = "{{ session('appearanceMenu') }}";
    </script>

    <!-- minicolors Js -->
    <script src="{{ asset('datta-able/plugins/mini-color/js/jquery.minicolors.min.js') }}"></script>

    <script src="{{ asset('dist/js/custom/validation.min.js') }}"></script>
    <script src="{{ asset('dist/js/condition.min.js') }}"></script>
    <script src="{{ Module::asset('cms:js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('dist/js/custom/jquery.blockUI.min.js') }}"></script>
    <script src="{{ Module::asset('cms:js/theme.min.js') }}"></script>
@endsection
