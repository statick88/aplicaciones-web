<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Biblioteca') — Sistema Biblioteca UNIANDES</title>
    <style>
        :root {
            --verde-uniandes: #00794a;
            --verde-oscuro: #005a37;
            --gris-fondo: #f4f6f5;
            --borde: #dfe5e2;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--gris-fondo);
            color: #1f2a26;
        }
        header.barra {
            background: var(--verde-uniandes);
            color: #fff;
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        header.barra .marca {
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.02em;
            text-decoration: none;
            color: #fff;
        }
        header.barra .marca small {
            display: block;
            font-weight: 400;
            font-size: 0.72rem;
            opacity: 0.85;
        }
        header.barra nav { display: flex; gap: 0.9rem; flex-wrap: wrap; }
        header.barra nav a {
            color: #eaf5ef;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.25rem 0.4rem;
            border-radius: 4px;
        }
        header.barra nav a:hover, header.barra nav a.activo {
            background: var(--verde-oscuro);
            color: #fff;
        }
        main { max-width: 1100px; margin: 1.5rem auto; padding: 0 1.5rem; }
        h1 { font-size: 1.4rem; color: var(--verde-oscuro); margin: 0 0 1rem; }
        .panel {
            background: #fff;
            border: 1px solid var(--borde);
            border-radius: 8px;
            padding: 1.25rem;
        }
        table { width: 100%; border-collapse: collapse; font-size: 0.92rem; }
        th, td { text-align: left; padding: 0.6rem 0.5rem; border-bottom: 1px solid var(--borde); }
        th { color: var(--verde-oscuro); font-size: 0.8rem; text-transform: uppercase; }
        tr:hover td { background: #f7faf9; }
        .acciones { display: flex; gap: 0.75rem; }
        a.btn, button.btn {
            display: inline-block;
            background: var(--verde-uniandes);
            color: #fff;
            border: none;
            padding: 0.45rem 0.9rem;
            border-radius: 5px;
            font-size: 0.88rem;
            text-decoration: none;
            cursor: pointer;
        }
        a.btn.secundario, button.btn.secundario { background: #56655f; }
        a.btn.peligro, button.btn.peligro { background: #b3261e; }
        a.btn:hover, button.btn:hover { filter: brightness(1.08); }
        form.inline { display: inline; }
        label { display: block; font-size: 0.85rem; font-weight: 600; margin: 0.8rem 0 0.25rem; }
        input, select, textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--borde);
            border-radius: 5px;
            font-size: 0.92rem;
            font-family: inherit;
        }
        .campo-error input, .campo-error select { border-color: #b3261e; }
        .error { color: #b3261e; font-size: 0.8rem; margin-top: 0.2rem; }
        .formulario { max-width: 560px; }
        .alerta { padding: 0.7rem 1rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; }
        .alerta.exito { background: #e3f4ea; color: var(--verde-oscuro); border: 1px solid #b7e0c8; }
        .alerta.error { background: #fdecea; color: #8c1d18; border: 1px solid #f3c1bd; }
        .vacio { color: #6b7a74; font-size: 0.9rem; padding: 0.75rem 0; }
        dl.detalle { display: grid; grid-template-columns: 180px 1fr; gap: 0.5rem 1rem; margin: 0; }
        dl.detalle dt { font-weight: 600; color: var(--verde-oscuro); font-size: 0.85rem; }
        dl.detalle dd { margin: 0; font-size: 0.92rem; }
        .pie { text-align: center; font-size: 0.8rem; color: #6b7a74; padding: 2rem 0; }
        .paginacion { margin-top: 1rem; font-size: 0.85rem; }
        .paginacion a { color: var(--verde-uniandes); }
    </style>
</head>
<body>
<header class="barra">
    <a class="marca" href="{{ route('home') }}">
        Sistema Biblioteca
        <small>Universidad de las Américas — UNIANDES</small>
    </a>
    <nav>
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'activo' : '' }}">Inicio</a>
        <a href="{{ route('editoriales.index') }}" class="{{ request()->routeIs('editoriales.*') ? 'activo' : '' }}">Editoriales</a>
        <a href="{{ route('categorias.index') }}" class="{{ request()->routeIs('categorias.*') ? 'activo' : '' }}">Categorías</a>
        <a href="{{ route('libros.index') }}" class="{{ request()->routeIs('libros.*') ? 'activo' : '' }}">Libros</a>
        <a href="{{ route('lectores.index') }}" class="{{ request()->routeIs('lectores.*') ? 'activo' : '' }}">Lectores</a>
        <a href="{{ route('prestamos.index') }}" class="{{ request()->routeIs('prestamos.*') ? 'activo' : '' }}">Préstamos</a>
    </nav>
</header>

<main>
    @if (session('exito'))
        <div class="alerta exito">{{ session('exito') }}</div>
    @endif
    @if (session('error'))
        <div class="alerta error">{{ session('error') }}</div>
    @endif

    @yield('contenido')
</main>

<footer class="pie">
    Semana 4 · Tarea 1 · Scaffolding con Laravel · © {{ date('Y') }} Universidad de las Américas
</footer>
</body>
</html>
