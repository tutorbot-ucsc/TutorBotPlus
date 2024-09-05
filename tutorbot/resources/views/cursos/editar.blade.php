@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Curso'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('cursos.update', ['id'=>$curso->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('cursos.form')
                    <input type="submit" class="btn btn-primary" value="Editar">
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection