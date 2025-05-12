@extends('layouts.app')
@section('title', 'Editar Producto')

@push('css')
    <style>
        #descripcion {
            resize: none;
            /* Evita que el textarea sea redimensionable */
        }
    </style>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@endpush

@section('content')
                    <div class="container-fluid px-4">
                        <h1 class="mt-4 text-center">Editar Producto</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
                            <li class="breadcrumb-item active">Editar producto</li>
                        </ol>

                        <div class="card">
                            <form action="{{ route('productos.update', ['producto' => $producto]) }}" method="post"
                                enctype="multipart/form-data">
                                @method('PATCH')
                                @csrf
                                <div class="card-body">
                                    <!-- Código y Código de Barras -->
                                    <div class="row g-4 mb-3">
                                        <div class="col-md-6">
                                            <label for="codigo" class="form-label">Código:</label>
                                            <input type="text" name="codigo" id="codigo" class="form-control"  readonly8
                                                value="{{ old('codigo', $producto->codigo) }}">
                                            @error('codigo')
                                                <small class="text-danger">{{ '* ' . $message }}</small>
                                            @enderror
                                        </div>
                                        </div>
                                    <div class="row g-4 mb-3">

            <div class="col-md-6 d-flex align-items-center">
                                                <?php
        use Illuminate\Support\Facades\Storage;

        // Obtener el código del producto
        $codigoProducto = $producto->codigo;

        // Generar el código de barras
        $barcode = (new Picqer\Barcode\Types\TypeEan13())->getBarcode($codigoProducto);

        // Renderizar el código de barras como HTML
        $renderer = new Picqer\Barcode\Renderers\HtmlRenderer();
        echo $renderer->render($barcode);

        // Guardar el código de barras como imagen PNG usando Storage
        Storage::put('public/barcodes/' . $codigoProducto . '.png', $renderer->render($barcode, $barcode->getWidth() * 3, 50));
                                                    ?>
                                        </div>
                                    </div>

                                    <!-- Nombre -->
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <label for="nombre" class="form-label fw-bold">Nombre:</label>
                                            <input type="text" name="nombre" id="nombre" class="form-control"
                                                value="{{ old('nombre', $producto->nombre) }}">
                                            @error('nombre')
                                                <small class="text-danger">{{ '* ' . $message }}</small>
                                            @enderror
                                        </div>

                                        <!-- Descripción -->
                                        <div class="col-12">
                                            <label for="descripcion" class="form-label fw-bold">Descripción:</label>
                                            <textarea name="descripcion" id="descripcion" rows="5"
                                                class="form-control">{{ old('descripcion', $producto->descripcion) }}</textarea>
                                            @error('descripcion')
                                                <small class="text-danger">{{ '* ' . $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-4 mt-3">
                                        <!-- Imagen -->
                                        <div class="col-md-6">
                                            <label for="img_path" class="form-label fw-bold">Imagen:</label>
                                            <input type="file" name="img_path" id="img_path" class="form-control" accept="image/*">
                                            @if ($producto->img_path)
                                                <div class="mt-3">
                                                    <p class="fw-bolder">Imagen actual:</p>
                                                    <img src="{{ asset($producto->img_path) }}" alt="{{ $producto->nombre }}"
                                                        class="img-thumbnail rounded" style="max-width: 200px;">
                                                </div>
                                            @endif
                                            @error('img_path')
                                                <small class="text-danger">{{ '* ' . $message }}</small>
                                            @enderror
                                        </div>

                                        <!-- Marca -->
                                        <div class="col-md-6">
                                            <label for="marca_id" class="form-label fw-bold">Marca:</label>
                                            <select data-size="4" title="Seleccione una marca" data-live-search="true" name="marca_id"
                                                id="marca_id" class="form-control selectpicker show-tick">
                                                <option value="">Sin marca</option>
                                                @foreach ($marcas as $item)
                                                    <option value="{{ $item->id }}" {{ old('marca_id', $producto->marca_id) == $item->id ? 'selected' : '' }}>
                                                        {{ $item->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('marca_id')
                                                <small class="text-danger">{{ '* ' . $message }}</small>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row g-4 mt-3">
                                        <!-- Presentación -->
                                        <div class="col-md-6">
                                            <label for="presentacione_id" class="form-label fw-bold">Presentación:</label>
                                            <select data-size="4" title="Seleccione una presentación" data-live-search="true"
                                                name="presentacione_id" id="presentacione_id" class="form-control selectpicker show-tick">
                                                @foreach ($presentaciones as $item)
                                                    <option value="{{ $item->id }}" {{ old('presentacione_id', $producto->presentacione_id) == $item->id ? 'selected' : '' }}>
                                                        {{ $item->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('presentacione_id')
                                                <small class="text-danger">{{ '* ' . $message }}</small>
                                            @enderror
                                        </div>

                                        <!-- Categoría -->
                                        <div class="col-md-6">
                                            <label for="categoria_id" class="form-label fw-bold">Categoría:</label>
                                            <select data-size="4" title="Seleccione una categoría" data-live-search="true"
                                                name="categoria_id" id="categoria_id" class="form-control selectpicker show-tick">
                                                <option value="">Sin categoría</option>
                                                @foreach ($categorias as $item)
                                                    <option value="{{ $item->id }}" {{ old('categoria_id', $producto->categoria_id) == $item->id ? 'selected' : '' }}>
                                                        {{ $item->nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('categoria_id')
                                                <small class="text-danger">{{ '* ' . $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer text-center">
                                    <button type="submit" class="btn btn-primary me-2">Guardar</button>
                                    <button type="reset" class="btn btn-secondary">Reiniciar</button>
                                </div>
                            </form>
                        </div>
                    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
@endpush
