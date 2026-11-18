@php($lector = $lector ?? null)

<label for="nombre">Nombre completo *</label>
<input id="nombre" name="nombre" type="text" maxlength="120" required
       value="{{ old('nombre', $lector?->nombre) }}">
@error('nombre') <div class="error">{{ $message }}</div> @enderror

<label for="cedula">Cédula (10 dígitos) *</label>
<input id="cedula" name="cedula" type="text" minlength="10" maxlength="10" required
       placeholder="0912345678"
       value="{{ old('cedula', $lector?->cedula) }}">
@error('cedula') <div class="error">{{ $message }}</div> @enderror

<label for="correo">Correo electrónico *</label>
<input id="correo" name="correo" type="email" maxlength="160" required
       value="{{ old('correo', $lector?->correo) }}">
@error('correo') <div class="error">{{ $message }}</div> @enderror

<label for="telefono">Teléfono *</label>
<input id="telefono" name="telefono" type="text" maxlength="20" required
       placeholder="0981234567"
       value="{{ old('telefono', $lector?->telefono) }}">
@error('telefono') <div class="error">{{ $message }}</div> @enderror

<label for="fecha_registro">Fecha de registro *</label>
<input id="fecha_registro" name="fecha_registro" type="date" required
       value="{{ old('fecha_registro', $lector?->fecha_registro) }}">
@error('fecha_registro') <div class="error">{{ $message }}</div> @enderror
