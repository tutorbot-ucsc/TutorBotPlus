@extends('layout_plataforma.app', ['title_html' => 'Editorial', 'title' => 'Editorial - '.$problema->nombre])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="card border-danger">
            <div class="card-header">
                <div class="d-flex d-flex justify-content-between">
                    <div class="p-2">Editorial</div>
                    <div class="p-2"><a class="btn btn-primary" href="{{route('problemas.ver', ['id_curso'=>$id_curso, 'codigo'=>$problema->codigo])}}" role="button">Volver</a></div>
                  </div>
            </div>
            <div class="card-body px-5">
                {!! Str::markdown($problema->body_editorial)!!}
            </div>
        </div>
    </div>
@endsection
