@php($categoria = $categoria ?? null)

<label for="nombre">Nombre *</label>
<input id="nombre" name="nombre" type="text" maxlength="80" required
       value="{{ old('nombre', $categoria?->nombre) }}">
@error('nombre') <div class="error">{{ $message }}</div> @enderror

<label for="descripcion">Descripción</label>
<textarea id="descripcion" name="descripcion" rows="3" maxlength="255">{{ old('descripcion', $categoria?->descripcion) }}</textarea>
@error('descripcion') <div class="error">{{ $message }}</div> @enderror

<label for="color">Color (hex) *</label>
<input id="color" name="color" type="color" required
       value="{{ old('color', $categoria?->color ?? '#00794a') }}">
@error('color') <div class="error">{{ $message }}</div> @enderror

<label for="orden">Orden</label>
<input id="orden" name="orden" type="number" min="0" max="255"
       value="{{ old('orden', $categoria?->orden ?? 0) }}">
@error('orden') <div class="error">{{ $message }}</div> @enderror

<label for="activa">
    <input type="hidden" name="activa" value="0">
    <input id="activa" name="activa" type="checkbox" value="1" style="width:auto"
           {{ old('activa', $categoria?->activa ?? true) ? 'checked' : '' }}>
    Categoría activa
</label>
@error('activa') <div class="error">{{ $message }}</div> @enderror
