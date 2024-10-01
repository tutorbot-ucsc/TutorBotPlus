@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Inserción masiva de usuarios'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Inserción masiva de usuarios'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('usuarios.bulk_store') }}' onsubmit="event.preventDefault();submitFormCrear()" id="crearForm" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
                    <p>Para insertar usuarios de manera masiva, debe subir un archivo .csv con la información personal de los usuarios, cursos y roles.
                        <br><strong>Debe seguir el siguiente orden:</strong> 
                        <br><strong>Nombre de Usuario, Nombre, Apellido, Correo, los códigos de los cursos en un array, los nombres de los roles en un array.</strong>
                       <br><a href="{{asset('examples/ejemplo_bulk_usuarios.csv')}}" style="color: blue;" download>Haga click aquí</a> para descargar el archivo ejemplo de csv.
                    </p>
                    <p>La contraseña se asigna de manera automática y es el rut del usuario.</p>
                    <div class="mb-3">
                        <input class="form-control" type="file" id="formFile" id="csvFile" name="csvFile" required>
                        <label for="formFile" class="form-label">Formatos: .csv, .txt</label>
                    </div>
                    @error('csvFile')
                        <p class="text-danger text-xs pt-1"> {{ $message }} </p>
                    @enderror                 
                    <input type="submit" class="btn btn-primary" value="Insertar">
                    <a href="{{route('usuarios.index')}}" class="btn btn-outline-primary">Volver</a>
                </div>
            </div>            
        </form>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
@endpush