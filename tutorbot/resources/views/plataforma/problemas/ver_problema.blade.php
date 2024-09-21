@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - '.$problema->nombre])
@section('content')
    <div class="container-fluid py-3 px-4">
            <div class="row">
                <div class="col">
                    <div class="card border-danger" style="height:100%">
                        <div class="card-header">
                            Enunciado
                        </div>
                        <div class="card-body">
                            {!! Str::markdown($problema->body_problema) !!}
                        </div>
                    </div>
                </div>
                <div class="col-4 col-sm-4">
                    <div class="card border-danger" style="height:100%">
                        <div class="card-body px-5">
                            <div class="row px-5">
                                <a class="btn btn-primary text-nowrap btn-block {{ $problema->disponible? '' : 'disabled' }}" @if(isset($id_curso))href="{{route('problemas.resolver', ['codigo'=>$problema->codigo, 'id_curso'=>$id_curso])}}"@endif
                                    role="button">{{ $problema->disponible? 'Resolver Problema' : 'Problema No Disponible' }}</a>
                            </div>
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-primary text-nowrap btn-sm btn-block {{ isset($problema->body_editorial) ? '' : 'disabled' }}"
                                    href="{{route('problemas.ver_editorial', ['codigo'=>$problema->codigo])}}"
                                    role="button">{{ isset($problema->body_editorial) ? 'Ver Editorial' : 'Editorial No Disponible' }}</a>
                            </div>
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-primary text-nowrap btn-sm btn-block" href="{{route('envios.listado', ['id_problema'=>$problema->id])}}" role="button">Ver Mis Envios</a></div>
                            </div>
                            <h6 class="ms-4">Puntos: {{$problema->casos_de_prueba()->sum('puntos')}}</h6>
                            <h6 class="ms-4 mt-3">Límite de Tiempo: {{$problema->tiempo_limite? $problema->tiempo_limite.' s' : 'No definido'}}</h6>
                            <h6 class="ms-4 mt-3">Límite de Memoria: {{$problema->memoria_limite? $problema->memoria_limite.' KB' : 'No definido'}}</h6>
                            <h6 class="ms-4 mt-3">Curso: {{implode(', ', $problema->cursos()->where('cursos.id','=', $id_curso)->pluck('nombre')->toArray())}}</h6>
                            <h6 class="ms-4 mt-3">Estado: {{auth()->user()->envios()->where('id_problema', '=', $problema->id)->where('id_curso', '=', $id_curso)->where('solucionado', '=', true)->exists()? 'Resuelto':'No Resuelto'}}</h6>
                            <h6 class="ms-4 mt-3">Categorías: {{implode(', ', $problema->categorias()->get()->pluck('nombre')->toArray())}}</h6>
                            <h6 class="ms-4 mt-3">Lenguajes: {{implode(', ', $problema->lenguajes()->get()->pluck('abreviatura')->toArray())}}</h6>
                        </div>
                    </div>
                </div>
            </div>     
    </div>
@endsection
