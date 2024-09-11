@extends('layout_plataforma.app', ['title_html' => 'Editorial', 'title' => 'Editorial - '.$problema->nombre])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="card border-danger">
            <div class="card-header">
                Editorial
            </div>
            <div class="card-body px-5">
                {!! Str::markdown($problema->body_editorial)!!}
            </div>
        </div>
    </div>
@endsection
