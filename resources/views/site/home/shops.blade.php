@extends('site.layouts.app')
@section('page_title', $page->name)

@section('seo')
    <meta name="robots" content="index, follow">
    <meta name="title" content="{{ $page->meta_title ?? $page->title }}">
    <meta name="description" content="{{ $page->meta_description }}" />
    <meta name="keywords" content="">

    <!-- Schema.org markup for Google+ -->
    <meta itemprop="name" content="{{ $page->meta_title ?? $page->title }}">
    <meta itemprop="description" content="{{ $page->meta_description }}">
    <meta itemprop="image" content="{{ $page->fileUrl() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $page->meta_title ?? $page->title }}">
    <meta property="og:description" content="{{ $page->meta_description }}">
    <meta property="og:image" content="{{ $page->fileUrl() }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ $page->meta_title ?? $page->title }}">
    <meta property="twitter:description" content="{{ $page->meta_description }}">
    <meta property="twitter:image" content="{{ $page->fileUrl() }}">
@endsection
//search-products?categories=&keyword=&brands=&attributes=&price_range=&rating=&sort_by=Price%20Low%20to%20High

@section('content')
    <section class="mx-4 lg:mx-4 xl:mx-32 2xl:mx-64 3xl:mx-92 my-10 md:my-12" style="margin-top:;margin-bottom:;">
        <p class="dm-bold text-sm text-center md:text-left mb-2.5 md:mb-5 md:text-22 text-gray-12 uppercase">Shops</p>
        <div><div class="flex flex-col md:flex-row mt-2 md:mt-5 md:space-x-29p">
                <a href="http://127.0.0.1:8000/search-products?brands=Aarong" class="relative h-36 md:h-80 md:w-1/4 border rounded-md">
                    <div class="inset-center">
                        <div class="grow">
                            <img class="w-29 h-29 object-contain" src="http://127.0.0.1:8000/dist/img/default-image.png" alt="Image">
                        </div>
                    </div>
                </a>
                <div class="w-full md:w-3/4 mt-5 md:mt-0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5 md:gap-7">
                        @foreach($shops as $shop)
                            <a href="{{route('site.shop.profile', $shop->alias)}}">
                                <div class="border rounded-md">
                                    <div class="grow p-6 flex flex-row h-36 justify-center items-center">
                                        <img class="w-80p h-20 object-contain" src="{{  optional($shop->vendor->logo)->fileUrl() ?? $vendorshop->fileUrl() }}" alt="Image">
                                    </div>
                                </div>
                                <p>{{$shop->vendor->name ?? '' }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        const ajaxLoadUrl = "{{ route('ajax-product') }}"
    </script>
    <script src="{{ asset('dist/js/custom/site/home.min.js') }}"></script>
    <script src="{{ asset('frontend/assets/slick/slick.min.js') }}"></script>
    <script src="{{ asset('dist/js/custom/site/common.min.js') }}"></script>
    <script src="{{ asset('dist/js/custom/site/wishlist.min.js') }}"></script>
    <script src="{{ asset('dist/js/custom/site/compare.min.js') }}"></script>
@endsection

