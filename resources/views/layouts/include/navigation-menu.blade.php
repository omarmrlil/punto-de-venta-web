<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">

            <x-nav.heading>Menu</x-nav.heading>

            <x-nav.nav-link href="{{ route('panel') }}" icon="fas fa-tachometer-alt" content="Panel" />

                <x-nav.heading>Modulos</x-nav.heading>



                <!----Compras---->
@can('ver-compra')
    <x-nav.link-collapsed id="collapseCompras" icon="fa-solid fa-store" content="Compras">
        @can('ver-compra')
            <x-nav.link-collapsed-item :href="route('compras.index')" content="Ver" />
        @endcan
        @can('crear-compra')
            <x-nav.link-collapsed-item :href="route('compras.create')" content="Crear" />
        @endcan
    </x-nav.link-collapsed>
@endcan


                <!----Ventas---->
@can('ver-venta')
    <x-nav.link-collapsed id="collapseVentas" icon="fa-solid fa-cart-shopping" content="Venta">
        @can('ver-venta')
            <x-nav.link-collapsed-item :href="route('ventas.index')" content="Ver" />
        @endcan
        @can('crear-venta')
            <x-nav.link-collapsed-item :href="route('ventas.create')" content="Crear" />
        @endcan
    </x-nav.link-collapsed>
@endcan

                <!-- Módulo de Categorías -->
                @can('ver-categoria')
                    <a class="nav-link" href="{{ route('categorias.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-tag"></i></div>
                        Categorías
                    </a>
                @endcan

                <!-- Módulo de Presentaciones -->
                @can('ver-presentacione')
                    <a class="nav-link" href="{{ route('presentaciones.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-box-archive"></i></div>
                        Presentaciones
                    </a>
                @endcan

                <!-- Módulo de Marcas -->
                @can('ver-marca')
                    <a class="nav-link" href="{{ route('marcas.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-bullhorn"></i></div>
                        Marcas
                    </a>
                @endcan

                <!-- Módulo de Productos -->
                @can('ver-producto')
                    <a class="nav-link" href="{{ route('productos.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-brands fa-shopify"></i></div>
                        Productos
                    </a>
                @endcan

                <!-- Módulo de Clientes -->
                @can('ver-cliente')
                    <a class="nav-link" href="{{ route('clientes.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                        Clientes
                    </a>
                @endcan

                <!-- Módulo de Proveedores -->
                @can('ver-proveedore')
                    <a class="nav-link" href="{{ route('proveedores.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-user-group"></i></div>
                        Proveedores
                    </a>
                @endcan

                <!-- Sección de Administración -->
                @hasrole('administrador')
                <div class="sb-sidenav-menu-heading">OTROS</div>
                @endhasrole

                <!-- Módulo de Usuarios -->
                @can('ver-user')
                    <a class="nav-link" href="{{ route('users.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-user"></i></div>
                        Usuarios
                    </a>
                @endcan

                <!-- Módulo de Roles -->
                @can('ver-role')
                    <a class="nav-link" href="{{ route('roles.index') }}">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-person-circle-plus"></i></div>
                        Roles
                    </a>
                @endcan
                </div>
                </div>

                <!-- Pie de página del menú -->
                <div class="sb-sidenav-footer">
                    <div class="small">Bienvenido:</div>
                    {{ auth()->user()->name }}
                </div>
                </nav>
                </div>
                <!-- Fin del menú -->
