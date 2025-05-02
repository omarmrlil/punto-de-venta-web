@extends('layouts.app')

@section('title', 'Crear cliente')

@push('css')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <style>
        #box-razon-social {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Crear Cliente</h1>

        <x-breadcrumb.template>
            <x-breadcrumb.item :href="route('panel')" active="false" content="Inicio" />
            <x-breadcrumb.item :href="route('clientes.index')" active="false" content="Clientes" />
            <x-breadcrumb.item active="true" content="Crear cliente" />
        </x-breadcrumb.template>

        <div class="card">
            <form action="{{ route('clientes.store') }}" method="post">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <!----Tipo de persona----->
                        <div class="col-md-6">
                            <label for="tipo" class="form-label">Tipo de Cliente:</label>
                            <select class="form-select" name="tipo" id="tipo">
                                <option value="" selected disabled>Seleccione una opción</option>
                                @foreach ($optionsTipopersona as $item)
                                <option value="{{ $item->value }}" {{ old('tipo')==$item->value ? 'selected' : '' }}>
                                    {{ $item->value }}
                                </option>
                                @endforeach
                            </select>
                            @error('tipo')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!-------Razón social------->
                        <div class="col-12" id="box-razon-social">
                            <label id="label-natural" for="razon_social" class="form-label">Nombres y apellidos:</label>
                            <label id="label-juridica" for="razon_social" class="form-label">Nombre de la empresa:</label>
                            <input required type="text" name="razon_social" id="razon_social" class="form-control"
                                value="{{ old('razon_social') }}" placeholder="">
                            @error('razon_social')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <!------Dirección---->
                        <div class="col-12">
                            <label for="direccion" class="form-label">Dirección:</label>
                            <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion') }}"
                                placeholder="Ingrese la dirección">
                            @error('direccion')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>

                        <!------Email---->
                        <div class="col-12">
                            <label for="email" class="form-label">Correo electrónico:</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}"
                                placeholder="Ingrese el correo electrónico">
                            @error('email')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>

                        <!------Teléfono---->
                        <div class="col-12">
                            <label for="telefono" class="form-label">Teléfono:</label>
                            <input type="number" name="telefono" id="telefono" class="form-control" value="{{ old('telefono') }}"
                                placeholder="Ingrese el teléfono">
                            @error('telefono')
                                <small class="text-danger">{{ '*' . $message }}</small>
                            @enderror
                        </div>

                        <!--------------Documento------->
                        <div class="col-md-6">
                            <label for="documento_id" class="form-label">Tipo de documento:</label>
                            <select class="form-select" name="documento_id" id="documento_id">
                                <option value="" selected disabled>Seleccione una opción</option>
                                @foreach ($documentos as $item)
                                <option value="{{ $item->id }}" {{ old('documento_id')==$item->id ? 'selected' : '' }}>
                                    {{ $item->nombre }}
                                </option>
                                @endforeach
                            </select>

                            @error('documento_id')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="numero_documento" class="form-label">Número de documento:</label>
                            <input required type="text" name="numero_documento" id="numero_documento" class="form-control"
                                value="{{ old('numero_documento') }}" placeholder="Ingrese el número de documento">
                            @error('numero_documento')
                            <small class="text-danger">{{ '*'.$message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
                </form>
                </div>
                </div>
                @endsection

@push('js')
<script>
    $(document).ready(function () {
        let selectValue = $('#tipo').val();
        if (selectValue) {
            $('#box-razon-social').show();
            if (selectValue == 'Natural') {
                $('#label-juridica').hide();
                $('#label-natural').show();
            } else {
                $('#label-natural').hide();
                $('#label-juridica').show();
            }
        } else {
            $('#box-razon-social').hide();
        }

        $('#tipo').on('change', function () {
            let selectValue = $(this).val();
            if (selectValue == 'Natural') {
                $('#label-juridica').hide();
                $('#label-natural').show();
            } else {
                $('#label-natural').hide();
                $('#label-juridica').show();
            }
            $('#box-razon-social').show();
        });
    });
</script>
@endpush
