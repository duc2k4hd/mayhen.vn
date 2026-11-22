@extends('admin.layouts.master')

@section('title', 'Chỉnh Sửa Bản Tin: ' . $post->title)

@section('content')
    @include('admin.pages.news._form', ['post' => $post])
@endsection
