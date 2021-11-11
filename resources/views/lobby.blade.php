@extends('layouts.app')

@section('content')
    <div class="container">

        <lobby :user="{{ auth()->user() }}"></lobby>

    </div>
@endsection
