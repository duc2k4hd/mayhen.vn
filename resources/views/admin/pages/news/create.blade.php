@extends('admin.layouts.master')

@section('title', 'Đăng Bản Tin Mới — Tin Tức Mây Hẹn')

@section('content')
    @include('admin.pages.news._form', ['post' => null])
@endsection
