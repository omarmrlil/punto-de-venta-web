@extends('layouts.app')

@section('title', 'Editar categoría')

@push('css')
    <style>
        #descripcion {
            resize: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4">
        <h1 class="mt-4 text-center">Editar Categoría</h1>
        <x-breadcrumb.template>
            <x-breadcrumb.item :href="route('panel')" active="false" content=" Inicio" />
            <x-breadcrumb.item active="false" :href="route('categorias.index')" content=" Categorías" />
            <x-breadcrumb.item active="true" content=" Editar categoría" />
        </x-breadcrumb.template>

        <x-forms.template :action="route('categorias.update', ['categoria' => $categoria])" method="post">
            @method('PATCH')
            <div class="row g-4">
                <div class="col-md-6">
                    <x-forms.input id="nombre" required="true" :value="old('nombre', $categoria->caracteristica->nombre)" />
                </div>

                <div class="col-12">
                    <x-forms.textarea
                        id="descripcion">{{ old('descripcion', $categoria->caracteristica->descripcion) }}</x-forms.textarea>
                </div>
            </div>

            <x-slot name="footer">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <button type="reset" class="btn btn-secondary">Reiniciar</button>
            </x-slot>
        </x-forms.template>
    </div>
@endsection

@push('js')
@endpush
