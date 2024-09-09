@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url'=>'Editar Editorial'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Problema '.$problema->codigo' - Editar Editorial'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" action="{{ route('problemas.update', ['id'=>$problema->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-body">
                    @include('problemas.form')
                    <input type="submit" class="btn btn-primary" value="Editar">
                </div>
            </div>
        </form>
        @include('layouts.footers.auth.footer')

    </div>
@endsection