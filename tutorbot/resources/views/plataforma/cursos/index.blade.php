@extends('layout_plataforma.app', ['title' => 'Cursos', 'title_html'=>'Cursos'])
@section('content')
@include('components.alert')
    <div class="container-fluid px-4">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex justify-content-start align-items-start px-5 mb-4">
                    <input class="form-control" type="text" placeholder="Buscar" style="width:30%" id="buscar_curso">
                    <button type="button" class="btn btn-primary mx-3">Filtro</button>
                    <button type="button" class="btn btn-primary">Ordenar</button>
                </div>
                @foreach ($cursos as $curso)
                    <a href="{{route('problemas.listado', ["id"=>$curso->id])}}"  style="text-decoration: none;">
                        <div class="card rounded mb-3 ms-5 me-4">
                            <div class="row g-0">
                                <div class="col-md-2">
                                    <div class="curso_image rounded"></div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $curso->nombre }}</h5>
                                        <p class="card-text mt-3">{{ $curso->descripcion }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
                <div class="d-flex flex-column justify-content-center mt-5 mx-5">
                    {{ $cursos->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
