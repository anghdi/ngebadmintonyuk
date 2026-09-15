@extends('layouts.app')
@section('title', 'Editor Feed')
@section('content')
<div class="feed-studio-page"><div class="page-heading flex flex-wrap items-end justify-between gap-4"><div><span class="eyebrow">ROW {{ $feedDesign->row_number }} · EDITOR</span><h1>{{ $feedDesign->headline }}</h1><p>Edit copy dan sumber foto sebelum mengatur crop.</p></div><a class="btn primary" href="{{ route('feed-studio.show', $feedDesign) }}">Buka preview</a></div><form method="post" action="{{ route('feed-studio.update', $feedDesign) }}" enctype="multipart/form-data">@csrf @method('put') @include('feed-studio._form')</form></div>
@endsection
