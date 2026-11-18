@php($editorial = $editorial ?? null)

<label for="nombre">Nombre *</label>
<input id="nombre" name="nombre" type="text" maxlength="120" required
       value="{{ old('nombre', $editorial?->nombre) }}">
@error('nombre') <div class="error">{{ $message }}</div> @enderror

<label for="pais">País *</label>
<input id="pais" name="pais" type="text" maxlength="60" required
       value="{{ old('pais', $editorial?->pais) }}">
@error('pais') <div class="error">{{ $message }}</div> @enderror

<label for="pagina_web">Página web</label>
<input id="pagina_web" name="pagina_web" type="url" maxlength="120"
       placeholder="https://…" value="{{ old('pagina_web', $editorial?->pagina_web) }}">
@error('pagina_web') <div class="error">{{ $message }}</div> @enderror

<label for="fundacion">Año de fundación</label>
<input id="fundacion" name="fundacion" type="number" min="1000" max="2026"
       value="{{ old('fundacion', $editorial?->fundacion) }}">
@error('fundacion') <div class="error">{{ $message }}</div> @enderror

<label for="activa">
    <input type="hidden" name="activa" value="0">
    <input id="activa" name="activa" type="checkbox" value="1" style="width:auto"
           {{ old('activa', $editorial?->activa ?? true) ? 'checked' : '' }}>
    Editorial activa
</label>
@error('activa') <div class="error">{{ $message }}</div> @enderror
