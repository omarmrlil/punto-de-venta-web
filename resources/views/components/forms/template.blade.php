
@props([
    'action',
    'method'
])

<div class="card text-bg-light">
    <form action="{{ $action }}" method="{{ $method }}">
        @csrf
        <div class="card-body">
            {{ $slot }}
        </div>

        <div class="card-footer text-center">
            {{ $footer }}
        </div>
    </form>
</div>


