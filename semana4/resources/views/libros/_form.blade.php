@php($libro = $libro ?? null)

<label for="titulo">Título *</label>
<input id="titulo" name="titulo" type="text" maxlength="200" required
       value="{{ old('titulo', $libro?->titulo) }}">
@error('titulo') <div class="error">{{ $message }}</div> @enderror

<label for="anio">Año de publicación *</label>
<input id="anio" name="anio" type="number" min="1000" max="2026" required
       value="{{ old('anio', $libro?->anio ?? date('Y')) }}">
@error('anio') <div class="error">{{ $message }}</div> @enderror

<label for="isbn">ISBN (13 dígitos) *</label>
<input id="isbn" name="isbn" type="text" minlength="13" maxlength="13" required
       placeholder="9781234567890"
       value="{{ old('isbn', $libro?->isbn) }}">
@error('isbn') <div class="error">{{ $message }}</div> @enderror

<label for="editorial_id">Editorial *</label>
<select id="editorial_id" name="editorial_id" required>
    <option value="">—Seleccione—</option>
    @foreach ($editoriales as $editorial)
        <option value="{{ $editorial->id }}"
            {{ (string) old('editorial_id', $libro?->editorial_id) === (string) $editorial->id ? 'selected' : '' }}>
            {{ $editorial->nombre }}
        </option>
    @endforeach
</select>
@error('editorial_id') <div class="error">{{ $message }}</div> @enderror

<label for="categoria_id">Categoría *</label>
<select id="categoria_id" name="categoria_id" required>
    <option value="">—Seleccione—</option>
    @foreach ($categorias as $categoria)
        <option value="{{ $categoria->id }}"
            {{ (string) old('categoria_id', $libro?->categoria_id) === (string) $categoria->id ? 'selected' : '' }}>
            {{ $categoria->nombre }}
        </option>
    @endforeach
</select>
@error('categoria_id') <div class="error">{{ $message }}</div> @enderror
