<nav class="navbar navbar-expand-lg rounded border-bottom shadow-sm bg-body-tertiary py-2 px-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('cursos.listado') }}">
            <img src="{{ asset('img/ucsc_logo2.png') }}" alt="UCSC Logo" height="60" class="me-3">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('cursos.*') || Route::is('problemas.*') ? 'active fw-bold' : '' }}"
                        aria-current="page" href="{{ route('cursos.listado') }}">Cursos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="#">Evaluaciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Route::is('envios.*') ? 'active fw-bold' : '' }}"
                        href="{{ route('envios.listado') }}">Envios</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        {{ auth()->user()->username }}
                    </a>
                    <ul class="dropdown-menu">
                        @can('acceso al panel de administración')
                            <li><a class="dropdown-item" href="{{ route('home') }}">Ir al Panel de Admnistración</a>
                            </li>
                        @endcan
                        <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        @auth
                            <form role="form" method="post" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <li><a class="dropdown-item"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                        href="{{ route('logout') }}">Cerrar Sesión</a></li>
                            </form>
                        @endauth
                        @guest
                            <li><a class="dropdown-item" href="{{ route('login') }}">Iniciar Sesión</a></li>
                        @endguest
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
