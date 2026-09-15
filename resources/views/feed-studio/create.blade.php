@extends('layouts.app')
@section('title', 'Buat Feed')
@section('content')
<div class="feed-studio-page"><div class="page-heading"><span class="eyebrow">FEED STUDIO · CREATE</span><h1>Buat konten baru</h1><p>Pilih format sesuai isi, bukan demi memenuhi grid.</p></div><form method="post" action="{{ route('feed-studio.store') }}" enctype="multipart/form-data">@csrf @include('feed-studio._form')</form></div>
@endsection
