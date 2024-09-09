@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Crear Categoría de Problema'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Crear Categoría de Problema'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form method="POST" action='{{ route('categorias.store') }}'>
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('categoria_problemas.form')
                    <input type="submit" class="btn btn-primary" value="Crear">
                </div>
            </div>            
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection
