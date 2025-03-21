@props([
    'labelText' => null,
    'id',
    'required' => false
])
<label for="{{ $id }}" class="form-label">
    {{ $labelText ?? ucfirst($id) }}:
        <span class="text-danger">{{ $required ? '*' : '' }}</span>
</label>

<textarea
rows="3"
name="{{$id}}"
 id="{{$id}}"
 class="form-control">{{ old($id) }} </textarea>


@error($id)
    <small class="text-danger">{{'*' . $message }}</small>
@enderror
