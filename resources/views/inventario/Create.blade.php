@extends('layouts.app')

@section('title', 'Inicializar Producto')

@push('css')
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Inicializar Producto</h1>
        <x-breadcrumb.template>
            <x-breadcrumb.item :href="route('panel')" content="Inicio" />
            <x-breadcrumb.item :href="route('productos.index')" content="Productos" />
            <x-breadcrumb.item active="true" content="Inicializar Producto" />
        </x-breadcrumb.template>

        <div class="mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verPlanoModal">
                Ver Planos
            </button>
        </div>

        <x-forms.template :action="route('inventario.store')" method="post">

            <x-slot name='header'>
                <p>Producto: <span class="fw-bold">{{ $producto->nombre }}</span></p>
            </x-slot>

            <div class="row g-4">

                <!-- Producto ID -->
                <input type="hidden" name="producto_id" value="{{ $producto->id }}">

                <!-- Ubicación -->
                <div class="col-12">
                    <label for="ubicacione_id" class="form-label">Seleccione una ubicación:</label>
                    <select name="ubicacione_id" id="ubicacione_id" class="form-select" required>
                        @foreach ($ubicaciones as $item)
                            <option value="{{ $item->id }}" {{ old('ubicacione_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('ubicacione_id')
                        <small class="text-danger">{{ '* ' . $message }}</small>
                    @enderror
                </div>

                <!-- Cantidad -->
                <div class="col-md-6">
                    <label for="cantidad" class="form-label">Cantidad:</label>
                    <input type="number" name="cantidad" id="cantidad" class="form-control" required
                        value="{{ old('cantidad') }}">
                    @error('cantidad')
                        <small class="text-danger">{{ '* ' . $message }}</small>
                    @enderror
                </div>

                <!-- Fecha de vencimiento -->
                <div class="col-md-6">
                    <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento:</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento" class="form-control"
                        value="{{ old('fecha_vencimiento') }}">
                    @error('fecha_vencimiento')
                        <small class="text-danger">{{ '* ' . $message }}</small>
                    @enderror
                </div>

            </div>

            <x-slot name="footer">
                <button type="submit" class="btn btn-primary">Inicializar</button>
            </x-slot>
        </x-forms.template>

        <!-- Modal para ver planos -->
        <div class="modal fade" id="verPlanoModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Plano de Ubicaciones</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <img src="{{ asset('assets/img/plano.png') }}" alt="Plano de Ubicaciones"
                                    class="img-fluid img-thumbnail border rounded">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
@endpush
