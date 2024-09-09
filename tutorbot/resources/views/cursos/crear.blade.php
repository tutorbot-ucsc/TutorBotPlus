@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Curso'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('cursos.store') }}'>
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('cursos.form')
                    <input type="submit" class="btn btn-primary" value="Crear">
                </div>
            </div>            
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection


