@extends('layout_plataforma.app', ['title_html' => $problema->nombre, 'title' => 'Problema - ' . $problema->nombre . ' - Resolver', 'breadcrumbs'=>[["nombre"=>"Cursos", "route"=>route("cursos.listado")],["nombre"=>"Problemas", "route"=>route('problemas.listado', ['id'=>$id_curso])],["nombre"=>$problema->nombre,"route"=>route('problemas.ver', ['codigo'=>$problema->codigo, 'id_curso'=>$id_curso])], ["nombre"=>"Resolver"]]])
@section('content')
    <div class="container-fluid px-4 pb-4">
        @include('components.alert')
        <div class="row">
            <div class="col-sm col-xs-12">
                <div class="card border-danger" style="height:100%">
                    <div class="card-header">
                        Editor de Código
                    </div>
                    <div class="card-body">
                        @error('codigo')
                            <p class="text-danger text-xs pt-1"> Debes escribir al menos una línea de código para enviarlo como
                                solución.</p>
                        @enderror
                        <div id="editor">{{ isset($last_envio->codigo) ? old('codigo', $last_envio->codigo) : '' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="card border-danger" style="height:100%;">
                    <div class="card-body px-5">
                        <form
                            action="{{ route('problemas.guardar_codigo', ['codigo_problema' => $problema->codigo, 'id_problema' => $problema->id,'id_curso' => $id_curso, 'id_resolver'=>$last_envio->id_resolver, 'id_cursa'=>$last_envio->id_cursa]) }}"
                            method="POST" id="guardarForm">
                            @csrf
                            <div class="row px-5 mb-2">

                                <input type="hidden" id="codigo_save" name="codigo_save">
                                <input type="hidden" id="lenguaje_save" name="lenguaje_save">
                                <button class="btn btn-outline-primary btn-sm" type="submit">Guardar y Volver</button>

                            </div>
                        </form>
                        <form
                            action="{{ route('problemas.enviar', ['id_problema' => $problema->id, 'id_curso' => $id_curso, 'id_resolver'=>$last_envio->id_resolver, 'id_cursa'=>$last_envio->id_cursa]) }}"
                            method="POST" id="evaluacion_form">
                            @csrf
                            <div class="row px-5">
                                <button class="btn btn-primary" type="submit">Enviar Solución</button>
                            </div>
                            <h6 class="text-center mt-4">Lenguaje de Programación</h6>
                            <select class="form-select mb-3" id="lenguaje" name="lenguaje"
                                onchange="change_language(this)" required>
                                    <option value="">Selecciona un Lenguaje</option>
                                @foreach ($lenguajes as $item)
                                    <option value="{{ $item->codigo }}" id="{{$item->id}}" @if($last_envio->ProblemaLenguaje->lenguaje->id == $item->id) selected @endif>
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
        var formSubmited = false
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
        var set_lenguaje = lenguajes[{{ strtolower($last_envio->ProblemaLenguaje->lenguaje->codigo) }}]
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/" + set_lenguaje);
        editor.setOptions({
            fontSize: "10pt"
        })


        function change_language(item) {
            editor.session.setMode("ace/mode/" + lenguajes[item.value]);
           document.querySelector('#lenguaje_save').value = item.options[item.selectedIndex].id;
        }
        document.querySelector('#evaluacion_form').addEventListener('submit', e => {
            e.preventDefault();
            formSubmited = true
            submitCodigo()
        });
        document.querySelector('#guardarForm').addEventListener('submit', e => {
            document.querySelector('#codigo_save').value = editor.getValue();
            formSubmited = true
            document.getElementById('guardarForm').submit();
        });
        window.addEventListener("beforeunload", function (e) {
            if(!formSubmited){
                e.preventDefault();
                e.returnValue = '';
            }
            
        });
    </script>
@endpush
