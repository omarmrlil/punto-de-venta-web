@extends('layouts.app')

@section('title', 'Inventario')

@push('css-datatable')
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
@endpush

@push('css')
@endpush

@section('content')



    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Inventario</h1>

        <x-breadcrumb.template>
            <x-breadcrumb.item :href="route('panel')" active="false" content=" Inicio" />
            <x-breadcrumb.item active="true" content="Inventario" />
        </x-breadcrumb.template>


        <div class="card">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Tabla Inventario
            </div>
            <div class="card-body">
                <table id="datatablesSimple" class="table-striped fs-6">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock</th>
                            <th>Ubicación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventario as $item)
                            <tr>
                                <td>
                                    {{$item->producto->nombre}}
                                </td>
                                <td>
                                    {{$item->cantidad}}
                                </td>
                                <td>
                                    {{$item->ubicacione->nombre}}
                                </td>
                                <td>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>

    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
    <script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush
