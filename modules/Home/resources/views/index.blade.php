@extends('layouts.client')
@section('content')
    @include('home::banner')
    @include('home::my_course_home')
    @include('home::all_course_home')
    {{-- @include('home::skill_extention') --}}
    @include('home::question')
    @include('home::cta-box')
    {{-- @include('home::partner') --}}
    @include('home::about_us')
@endsection

