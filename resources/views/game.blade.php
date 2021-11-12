@extends('layouts.app')

@section('content')
    <div class="container">

        <game :session="{{ $game }}" :user="{{ auth()->user() }}"></game>

    </div>
@endsection
