@extends('client.layouts.master')

@section('title', ($page->meta_title ?: $page->title) . ' — ' . config('app.name'))
@section('meta_description', $page->meta_description ?: $page->lead_summary)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/info-pages.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container_narrow mayhen_info_wrapper">
        <header class="mayhen_info_header">
            <span class="mayhen_info_subtitle">Thông Tin &amp; Chính Sách</span>
            <h1 class="mayhen_info_title">{{ $page->title }}</h1>
            @if($page->lead_summary)
                <p class="mayhen_info_lead">
                    {{ $page->lead_summary }}
                </p>
            @endif
        </header>

        <article class="mayhen_info_content">
            {!! $page->content !!}
        </article>
    </div>
@endsection

