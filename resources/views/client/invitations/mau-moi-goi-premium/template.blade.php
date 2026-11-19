@extends('client.layouts.invitation')

@section('title', 'Thiệp Cưới ' . ($templateData['groom_name'] ?? 'Chú Rể') . ' & ' . ($templateData['bride_name'] ?? 'Cô Dâu'))

@push('styles')
    <style>
        .mayhen_invitation_container {
            max-width: 480px;
            margin: 0 auto;
            background: #FFFDF9;
            min-height: 100vh;
            position: relative;
        }
    </style>
@endpush

@section('content')
<div class="mayhen_invitation_container">
    {{-- Section 1: Opening Envelope --}}
    <section class="mayhen_invitation_opening" id="section-opening" data-sync-target="opening">
        <!-- Opening content -->
    </section>

    {{-- Section 2: Hero Cover --}}
    <section class="mayhen_invitation_hero" id="section-hero">
        <h1 data-sync-target="hero_title">{{ $templateData['groom_name'] ?? 'Minh Quân' }} & {{ $templateData['bride_name'] ?? 'Ái Như' }}</h1>
    </section>
</div>
@endsection