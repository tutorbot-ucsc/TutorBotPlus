@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - ' . $problema->nombre . ' - Resolver'])
@section('content')
    <div class="container-fluid px-4">
        @include('components.alert')
        <div class="row">
            <div class="col">
                <div class="card border-danger" style="height:100%">
                    <div class="card-header">
                        Editor de Código
                    </div>
                    <div class="card-body">
                        @error('codigo')
                            <p class="text-danger text-xs pt-1"> Debes escribir al menos una línea de código para enviarlo como
                                solución.</p>
                        @enderror
                        <div id="editor">{{ isset($codigo) ? $codigo : '' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-4 col-sm-4">
                <div class="card border-danger" style="height:100%;">
                    <div class="card-body px-5">
                        <form
                            action="{{ route('problemas.enviar', ['id_problema' => $problema->id, 'id_curso' => $id_curso]) }}"
                            method="POST" id="evaluacion_form">
                            @csrf
                            <div class="row px-5">
                                <button class="btn btn-primary" type="submit">Enviar Solución</button>
                            </div>
                            <div class="row px-5 mt-2">
                                <a class="btn btn-outline-primary btn-sm "
                                    href="{{ route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso'=> $id_curso]) }}"
                                    role="button">Volver</a>
                            </div>
                            <h6 class="text-center mt-4">Lenguaje de Programación</h6>
                            <select class="form-select mb-3" id="lenguaje" name="lenguaje"
                                onchange="change_language(this)">
                                @foreach ($lenguajes as $item)
                                    <option value="{{ $item->codigo }}" abreviatura="{{ $item->abreviatura }}">
                                        {{ $item->nombre }}</option>
                                @endforeach
                            </select>
                            <h6 class="text-center mt-2">Evaluador</h6>
                            <select class="form-select mb-3" id="juez_virtual" name="juez_virtual">
                                <option value="0">Selección Aleatoria de Juez Virtual</option>
                                @foreach ($jueces as $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="codigo" name="codigo" value="">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/ace-builds/src-min/ace.js') }}" type="text/javascript" charset="utf-8"></script>
    <script src="{{ asset('assets/js/alertas_plataforma.js') }}"></script> 
    <script>
        var editor = ace.edit("editor");
        const lenguajes = {
            48: 'c_cpp',
            52: 'c_cpp',
            49: 'c_cpp',
            54: 'c_cpp',
            50: 'c_cpp',
            53: 'c_cpp',
            51: 'csharp',
            91: 'java',
            70: 'python',
            71: 'python',
            92: 'python',
            82: 'sql',
        }
        var set_lenguaje = lenguajes[{{ strtolower($lenguajes[0]->codigo) }}]
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/" + set_lenguaje);
        editor.setOptions({
            fontSize: "10pt"
        })
        

        function change_language(item) {
            editor.session.setMode("ace/mode/" + lenguajes[item.value]);
        }
        document.querySelector('#evaluacion_form').addEventListener('submit', e => {
            e.preventDefault();
            submitCodigo()
        });
    </script>
@endpush
