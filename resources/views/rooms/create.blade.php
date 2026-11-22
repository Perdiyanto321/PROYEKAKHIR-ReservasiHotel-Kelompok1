@extends('layouts.app')
@section('title', 'Tambah Unit Kamar')
@section('header', 'Tambah Unit Kamar Baru')

@section('content')
<div class="bg-white border border-stone-200 rounded-xl p-6 max-w-2xl">
    <form action="{{ route('rooms.store') }}" method="POST">
        @csrf
        @include('rooms._form')
        <div class="mt-6 flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-800 text-white text-sm font-medium hover:bg-amber-900">Simpan Unit</button>
            <a href="{{ route('rooms.index') }}" class="px-4 py-2.5 rounded-lg border border-stone-300 text-sm text-stone-700 hover:bg-stone-50">Batal</a>
        </div>
    </form>
</div>
@endsection