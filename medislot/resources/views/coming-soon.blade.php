@extends('layouts.app')

@section('title', $title . ' - MEDISLOT')
@section('page-title', $title)
@section('page-subtitle', 'Fitur ini sedang dalam pengembangan')

@section('content')
<div style="
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; min-height: 60vh; text-align: center;
    color: #5a7a70;
">
    <div style="font-size: 64px; margin-bottom: 20px;">🚧</div>
    <h2 style="font-size: 22px; font-weight: 700; color: #1a3c34; margin-bottom: 10px;">
        {{ $title }}
    </h2>
    <p style="font-size: 15px; max-width: 360px; line-height: 1.6;">
        Fitur ini sedang dikembangkan dan akan segera tersedia. Pantau terus pembaruan sprint kami!
    </p>
    <a href="{{ route('dashboard') }}" style="
        margin-top: 24px; background: #2d9e72; color: #fff;
        padding: 10px 24px; border-radius: 8px; text-decoration: none;
        font-weight: 600; font-size: 14px; transition: background 0.18s;
    ">← Kembali ke Dashboard</a>
</div>
@endsection
