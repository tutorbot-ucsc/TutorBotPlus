@extends('layout_plataforma.app', ['title_html' => 'Resultados - ' . $envio->problema->nombre, 'title' => ' Envio #' . $envio->id . ' - Problema ' . $envio->problema->nombre])
@section('content')
    <div class="container-fluid py-3 px-4">
        @include('components.alert')
        <div class="row">
            <div class="col">
                <div class="card border-danger" style="height:100%">
                    <div class="card-header">
                        Resultados
                    </div>
                    <div class="card-body">

                        <div class="accordion accordion-flush" id="resultados_accord">
                            @for ($i = 0; $i < count($evaluaciones); $i++)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#resultados_{{ $i }}" aria-expanded="false"
                                            aria-controls="resultados_{{ $i }}">Caso de Prueba
                                            #{{ $i + 1 }}:<span
                                                class="mx-2 badge {{ $evaluaciones[$i]->estado == 'Aceptado' ? 'text-bg-success' : 'text-bg-danger' }}">{{ $evaluaciones[$i]->resultado }}</span>
                                            - Tiempo: {{ $evaluaciones[$i]->tiempo ? $evaluaciones[$i]->tiempo : '0' }}
                                            segundos - Memoria:
                                            {{ $evaluaciones[$i]->memoria ? $evaluaciones[$i]->memoria : '0' }} KB
                                        </button>
                                    </h2>
                                    <div id="resultados_{{ $i }}" class="accordion-collapse collapse"
                                        data-bs-parent="#resultados_accord">
                                        <div class="accordion-body">
                                            <div class="d-flex flex-row mb-3">
                                                @if(isset($evaluaciones[$i]->casos_pruebas->entradas))
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Entradas</h5>
                                                        <p class="card-text">
                                                            {!! nl2br($evaluaciones[$i]->casos_pruebas->entradas) !!}</p>
                                                    </div>
                                                </div>
                                                @endif
                                                @if(isset($evaluaciones[$i]->casos_pruebas->salidas))
                                                <div class="card mx-2">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Salidas Esperadas</h5>
                                                        <p class="card-text">{!! nl2br($evaluaciones[$i]->casos_pruebas->salidas) !!}
                                                        </p>
                                                    </div>
                                                </div>
                                                @endif
                                                <div class="card {{ $evaluaciones[$i]->estado == 'Rechazado' || $evaluaciones[$i]->estado == 'Error' ? 'border-danger' : 'border-success' }}">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Salidas</h5>
                                                        <p class="card-text">
                                                            {!! $evaluaciones[$i]->stout ? nl2br(base64_decode($evaluaciones[$i]->stout)) : 'Error' !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (isset($evaluaciones[$i]->error_compilacion))
                                                <div class="card border-danger">
                                                    <div class="card-body">
                                                        <h5 class="card-title">Mensaje del Compilador</h5>
                                                        <p class="card-text text-danger">
                                                            {!! $evaluaciones[$i]->error_compilacion ? nl2br(base64_decode($evaluaciones[$i]->error_compilacion)) : '-' !!}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4 col-sm-4">
                <div class="card border-danger">
                    <div class="card-body px-5">
                        @if ($envio->solucionado == false && $tieneRetroalimentacion == false)
                            <div class="row px-5">
                                <a class="btn btn-primary btn-block {{ $problema->habilitar_llm == true && $cant_retroalimentacion > 0 ? '' : 'disabled' }}"
                                    href="{{route('envios.generar_retroalimentacion', ['token'=>$envio->token])}}"
                                    role="button">{{ $problema->habilitar_llm == true && $cant_retroalimentacion > 0 ? 'Solicitar Retroalimentacion (Cantidad Disponible: ' . $cant_retroalimentacion . ')' : 'Retroalimentación no disponible' }}</a>
                            </div>
                        @elseif($tieneRetroalimentacion == true)
                            <div class="row px-5">
                                <a class="btn btn-primary text-nowrap btn-block" href="{{route('envios.retroalimentacion', ['token'=>$envio->token])}}" role="button">Ver Retroalimentación</a>
                            </div>
                        @endif
                        @if ($envio->solucionado == false)
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-primary text-nowrap btn-block" href="{{route('problemas.resolver', ['codigo'=>$envio->problema->codigo])}}" role="button">Volver al intento</a>
                            </div>
                        @endif
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-primary text-nowrap btn-sm btn-block"
                                href="{{ route('problemas.ver', ['codigo' => $problema->codigo]) }}" role="button">Volver al
                                Enunciado</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3 mx-2">
        <div class="col">
            <div class="card border-danger" style="height:100%">
                <div class="card-header">
                    Código Fuente
                </div>
                <div class="card-body">
                    <pre><code class="{{ $highlightjs_choice }}-html">{{ $envio->codigo }}</code></pre>
                </div>
            </div>
        </div>
    </div>
    </div>
    <link rel="stylesheet" href="{{ asset('assets/js/highlightjs/styles/dark.css') }}">
@endsection
@push('js')
    <script src="{{ asset('assets/js/highlightjs/highlight.min.js') }}" type="text/javascript"></script>
    <script>
        hljs.highlightAll();
    </script>
@endpush