@extends('layout_plataforma.app', ['title_html' => 'Envios', 'title'=>'Mis envios'])
@section('content')
    <div class="container-fluid py-3 px-4">
        <div class="card border-danger">
            <div class="card-body px-5">
                <table id="table" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Problema</th>
                            <th>Lenguaje</th>
                            <th>¿Resuelto?</th>
                            <th>Inicio</th>
                            <th>Termino</th>
                            <th>Creado</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($envios as $envio)
                            <tr>
                                <td>{{$envio->id_envio}}</td>
                                <td><a href="{{route('problemas.ver', ['codigo'=>$envio->codigo_problema])}}">{{$envio->nombre_problema}}</a></td>
                                <td>{{$envio->nombre_lenguaje}}</td>
                                <td>{{$envio->solucionado == true? "Si":"No"}}</td>
                                <td>{{$envio->inicio}}</td>
                                <td>{{$envio->termino}}</td>
                                <td>{{$envio->created_at}}</td>
                                <td><a href="{{route('envios.ver', ['token'=>$envio->token])}}">Ver</a></td>
                            </tr>
                        @endforeach
                    </tbody>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <link href="{{ asset('assets/js/DataTables/datatables.min.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/DataTables/datatables.min.js') }}"></script>

    <script src="{{ asset('assets/js/DataTables/gestion_initialize_es_cl.js') }}"></script>
@endpush
