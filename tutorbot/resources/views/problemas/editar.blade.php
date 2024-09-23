@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100', 'title_url' => 'Editar Problema'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Editar Problema'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <form role="form" method="POST" id="problema_form" action="{{ route('problemas.update', ['id' => $problema->id]) }}"
            enctype="multipart/form-data" onsubmit="event.preventDefault();submitFormEditar('{{'el problema '.$problema->nombre}}')" id="editarForm">
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

@push('js')
    <script src="{{ asset('assets/js/alertas_administracion.js') }}"></script> 
    <script type="module">
        const checkbox = document.getElementById('sql')
        const lenguajes = document.getElementById("lenguajes");
        const sql_file = document.getElementById("sql_file");
        const editor = new Editor({
            el: document.querySelector('#editor'),
            height: '600px',
            initialEditType: 'markdown',
            placeholder: 'Ingrese el enunciado del problema',
            initialValue: `{{ isset($problema) ? old('body_problema', $problema->body_problema) : old('body_problema') }}`,
        })
        document.querySelector('#problema_form').addEventListener('submit', e => {
            e.preventDefault();
            document.querySelector('#body_problema').value = editor.getMarkdown();
            e.target.submit();
        });
        checkbox.addEventListener('change', (event) => {
            if (event.currentTarget.checked) {
                lenguajes.disabled = true;
                sql_file.classList.remove("d-none");
            } else {
                sql_file.classList.add("d-none");
                lenguajes.disabled = false;
            }
        })
    </script>
@endpush
