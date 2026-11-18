@php($prestamo = $prestamo ?? null)

<label for="fecha_prestamo">Fecha de préstamo *</label>
<input id="fecha_prestamo" name="fecha_prestamo" type="date" required
       value="{{ old('fecha_prestamo', $prestamo?->fecha_prestamo) }}">
@error('fecha_prestamo') <div class="error">{{ $message }}</div> @enderror

<label for="fecha_devolucion">Fecha de devolución</label>
<input id="fecha_devolucion" name="fecha_devolucion" type="date"
       value="{{ old('fecha_devolucion', $prestamo?->fecha_devolucion) }}">
@error('fecha_devolucion') <div class="error">{{ $message }}</div> @enderror

<label for="estado">Estado *</label>
<select id="estado" name="estado" required>
    <option value="activo" {{ old('estado', $prestamo?->estado ?? 'activo') === 'activo' ? 'selected' : '' }}>Activo</option>
    <option value="devuelto" {{ old('estado', $prestamo?->estado) === 'devuelto' ? 'selected' : '' }}>Devuelto</option>
</select>
@error('estado') <div class="error">{{ $message }}</div> @enderror

<label for="lector_id">Lector *</label>
<select id="lector_id" name="lector_id" required>
    <option value="">—Seleccione—</option>
    @foreach ($lectores as $lector)
        <option value="{{ $lector->id }}"
            {{ (string) old('lector_id', $prestamo?->lector_id) === (string) $lector->id ? 'selected' : '' }}>
            {{ $lector->nombre }} ({{ $lector->cedula }})
        </option>
    @endforeach
</select>
@error('lector_id') <div class="error">{{ $message }}</div> @enderror

<label for="libro_id">Libro *</label>
<select id="libro_id" name="libro_id" required>
    <option value="">—Seleccione—</option>
    @foreach ($libros as $libro)
        <option value="{{ $libro->id }}"
            {{ (string) old('libro_id', $prestamo?->libro_id) === (string) $libro->id ? 'selected' : '' }}>
            {{ $libro->titulo }}
        </option>
    @endforeach
</select>
@error('libro_id') <div class="error">{{ $message }}</div> @enderror
