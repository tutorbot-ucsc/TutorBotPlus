@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - '.$problema->nombre])
@php
    $estado = auth()->user()->envios()->where('id_problema', '=', $problema->id)->where('id_curso', '=', $id_curso)->where('solucionado', '=', true)->exists();    
@endphp
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
            <div class="row">
                <div class="col-8">
                    <div class="card border-danger" style="height:100%">
                        <div class="card-header">
                            Enunciado
                        </div>
                        <div class="card-body p-4 text-wrap" id="body_markdown">
                            {!! Str::markdown($problema->body_problema, [
                                'html_input' => 'strip',
                                'allow_unsafe_links' => false
                            ]) !!}
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
                                <a class="btn btn-outline-secondary btn-sm btn-block" href="{{route('problemas.pdf_enunciado', ['id_problema'=>$problema->id])}}"
                                    target="_blank" role="button">Descargar PDF del Enunciado</a>
                            </div>
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block {{ isset($problema->body_editorial) ? '' : 'disabled' }}"
                                    href="{{route('problemas.ver_editorial', ['codigo'=>$problema->codigo, 'id_curso'=>$id_curso])}}"
                                    role="button">{{ isset($problema->body_editorial) ? 'Ver Editorial' : 'Editorial No Disponible' }}</a>
                            </div>
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block" href="{{route('envios.listado', ['id_problema'=>$problema->id])}}" role="button">Ver Mis Envios</a></div>
                            </div>
                            @can('ver informe del problema')
                            <div class="row px-5 mt-2 mb-2">
                                <a class="btn btn-outline-secondary text-nowrap btn-sm btn-block"
                                    href="{{ route('informe.problema', ['id_curso' => $id_curso, 'id_problema' => $problema->id]) }}"
                                    role="button">Ver Informe del Problema</a>
                            </div>
                            @endcan
                            <p class="ms-4"> <strong>Puntos:</strong> {{$problema->casos_de_prueba()->sum('puntos')}}</p>
                            <p class="ms-4 mt-1"><strong>Límite de Tiempo:</strong> {{$problema->tiempo_limite? $problema->tiempo_limite.' s' : 'No definido'}}</p>
                            <p class="ms-4 mt-1"><strong>Límite de Memoria:</strong> {{$problema->memoria_limite? $problema->memoria_limite.' KB' : 'No definido'}}</p>
                            <p class="ms-4 mt-1"><strong>Curso:</strong> {{implode(', ', $problema->cursos()->where('cursos.id','=', $id_curso)->pluck('nombre')->toArray())}}</p>
                            <p class="ms-4 mt-1"><strong>Estado:</strong> <span class="badge {{$estado? 'text-bg-success' : 'text-bg-secondary'}}">{{$estado? 'Resuelto':'No Resuelto'}}</span></p>
                            <p class="ms-4 mt-1"><strong>Categorías:</strong> {{implode(', ', $problema->categorias()->get()->pluck('nombre')->toArray())}}</p>
                            <p class="ms-4 mt-1"><strong>Lenguajes:</strong> {{implode(', ', $problema->lenguajes()->get()->pluck('abreviatura')->toArray())}}</p>
                            @if(isset($problema->fecha_inicio))
                                <p class="ms-4 mt-1"><strong>Fecha de Inicio:</strong> {{$problema->fecha_inicio}}</p>
                            @endif
                            @if(isset($problema->fecha_termino))
                                <p class="ms-4 mt-1"><strong>Fecha de Termino:</strong> {{$problema->fecha_termino}}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>     
    </div>
@endsection

@push('js')
    
<script>
    var table = document.querySelector("#body_markdown table")
    if(table != null){
        var table_body = table.querySelector("tbody")
        table.classList.add("table")
        table.classList.add("table-bordered")
        table.classList.add("table-hover")
        table.classList.add("mt-3") 
        table.style.width = "auto"
        table_body.classList.add("table-group-divider")
    }
</script>
@endpush