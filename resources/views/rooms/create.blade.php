@extends('layouts.app')
@section('title', 'Tambah Unit Kamar')
@section('header', 'Tambah Unit Kamar Baru')

@section('content')
<div class="mb-5">
    <a href="{{ route('rooms.index') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-stone-600 hover:text-stone-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Kembali ke Daftar Unit Kamar</span>
    </a>
</div>

<form action="{{ route('rooms.store') }}" method="POST">
    @csrf
    @include('rooms._form')
</form>
@endsection