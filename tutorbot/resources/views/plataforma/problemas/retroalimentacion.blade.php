@extends('layout_plataforma.app', ['title_html' => 'Retroalimentación para Envio #' . $retroalimentacion->id_envio, 'title' => 'Retroalimentación - Envio #' . $retroalimentacion->id_envio])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="row">
            <div class="col">
                <div class="card border-danger" style="height:100%">
                    <div class="card-header">
                        Retroalimentación
                    </div>
                    <div class="card-body">
                        {!! Str::markdown($retroalimentacion->retroalimentacion) !!}
                    </div>
                </div>
            </div>
            <div class="col-4 col-sm-4">
                <div class="card border-danger" style="height:100%">
                    <div class="card-body px-5">
                        <div class="row px-5">
                           <img src="{{asset('img/ico_tutorbot.png')}}" alt="Mascota TutorBot" class="img-thumbnail mb-2">
                        </div>
                        <div class="row px-5">
                            <a class="btn btn-primary btn-block {{ $retroalimentacion->habilitar_llm == true && $cant_retroalimentacion > 0 ? '' : 'disabled' }}"
                                href="{{ route('envios.generar_retroalimentacion', ['token' => $token]) }}"
                                role="button" onclick="solicitarRetroalimentacion(event)">{{ $retroalimentacion->habilitar_llm == true && $cant_retroalimentacion > 0 ? 'Generar Nueva Retroalimentacion (Cantidad Disponible: ' . $cant_retroalimentacion . ')' : 'Retroalimentación no disponible' }}</a>
                        </div>
                        <div class="row px-5 mt-2">
                            <a class="btn btn-outline-primary text-nowrap btn-sm btn-block"
                                href="{{ route('envios.ver', ['token' => $token]) }}" role="button">Volver</a>
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
                    <pre><code class="{{ $highlightjs_choice }}-html">{{ $envios->codigo }}</code></pre>
                </div>
            </div>
        </div>
    </div>
    </div>
    <link rel="stylesheet" href="{{ asset('assets/js/highlightjs/styles/dark.css') }}">
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_plataforma.js') }}"></script>
    <script src="{{ asset('assets/js/highlightjs/highlight.min.js') }}" type="text/javascript"></script>
    <script>
        hljs.highlightAll();
    </script>
@endpush
