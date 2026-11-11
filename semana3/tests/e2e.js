async page => {
  // =====================================================================
  // Prueba E2E del CRUD de clientes (Semana 3).
  //
  // Se ejecuta contra el stack levantado con `docker compose up --build -d`
  // en http://localhost:8081. A diferencia de la Semana 2, aquí la base
  // arranca con 2 filas de semilla, así que la prueba:
  //   * limpia los restos de corridas anteriores (idempotencia);
  //   * mide el total inicial y lo usa como referencia;
  //   * crea sus propios registros con cédula y correo únicos;
  //   * los borra al final, para dejar la base como estaba.
  //
  // Nota sobre el borrado: el botón "Eliminar" dispara window.confirm y
  // la CLI de Playwright intercepta los modales, así que la prueba
  // comprueba el mensaje de confirmación (atributo data-confirm) y luego
  // envía el formulario con form.submit(), que no dispara el evento
  // 'submit' y por eso no abre el diálogo. El POST, el token
  // antifalsificación y la redirección PRG quedan cubiertos igual.
  // =====================================================================
  const base = 'http://localhost:8081/';
  const assert = (condicion, mensaje) => { if (!condicion) throw new Error(mensaje); };

  // El listado vive en la raíz ("/"); el h2 dice "Clientes registrados (N)".
  const heading = () => page.getByRole('heading', { name: /^Clientes registrados/ }).innerText();
  const total = async () => {
    const texto = await heading();
    const coincidencia = texto.match(/\((\d+)\)/);
    assert(coincidencia, 'List heading must show a record count');
    return Number(coincidencia[1]);
  };

  // Identificadores únicos por ejecución: así la prueba puede correr
  // más de una vez contra la misma base sin chocar con unicidad.
  const sello = Date.now();
  const cedulaE2E = String(sello).slice(-10);
  const correoE2E = `e2e-${sello}@example.com`;

  // Selector por `name`: los <label> incluyen el texto "obligatorio" /
  // "opcional", de modo que getByLabel con coincidencia exacta no aplica.
  // La casilla es especial: el tag helper de ASP.NET emite además un
  // <input type="hidden" name="Activo"> con el valor "false", así que
  // hay que apuntar explícitamente al checkbox.
  const campo = nombre => page.locator(`[name="${nombre}"]`);
  const casilla = () => page.locator('input[type="checkbox"][name="Activo"]');
  const alerta = () => page.getByRole('alert');

  // Envía un formulario de la tabla sin pasar por window.confirm.
  const enviarSinDialogo = async fila => {
    const respuesta = page.waitForResponse(r => r.request().method() === 'POST');
    await fila.locator('form[data-confirm]').evaluate(form => form.submit());
    const status = (await respuesta).status();
    await page.goto(base); // espera a que termine la navegación y refresca
    return status;
  };

  await page.goto(base);

  // ---------- 0. Limpieza de restos de corridas anteriores ----------
  for (let intento = 0; intento < 20; intento++) {
    const restos = page.locator('tbody tr').filter({ hasText: /e2e-\d+@example\.com/ });
    if (await restos.count() === 0) break;
    await enviarSinDialogo(restos.first());
  }
  await page.goto(base);
  const inicial = await total();

  // ---------- 1. Marca institucional ----------
  const logo = page.getByAltText('Universidad Regional Autónoma de los Andes — UNIANDES');
  assert(await logo.isVisible(), 'Institutional logo must be visible');
  assert(await logo.evaluate(img => img.complete && img.naturalWidth > 0), 'Logo must load (naturalWidth > 0)');

  const favicon = await page.locator('link[rel="icon"]').getAttribute('href');
  assert(favicon && favicon.includes('favicon.png'), 'Favicon must point to the brand image');

  // ---------- 1b. UTF-8 en HTML, CSS y JS ----------
  // El HTML lo declara el <meta charset> y lo confirma document.characterSet.
  // Los estáticos no llevan meta: su codificación vive únicamente en la
  // cabecera Content-Type, que es lo que comprueba este bloque.
  assert(await page.evaluate(() => document.characterSet === 'UTF-8'),
    'Document must be decoded as UTF-8 (document.characterSet)');

  for (const recurso of ['/css/site.css', '/js/site.js']) {
    const contentType = await page.evaluate(
      async url => (await fetch(url)).headers.get('content-type'), recurso);
    assert(contentType && contentType.includes('charset=utf-8'),
      `${recurso} must be served with charset=utf-8 (got: ${contentType})`);
  }

  // ---------- 2. Validación: correo inválido ----------
  await page.goto(base + 'Clientes/Crear');
  await campo('Cedula').fill(cedulaE2E);
  await campo('Nombre').fill('Cliente de prueba');
  await campo('Apellido').fill('E2E');
  await campo('Correo').fill('correo-invalido');

  const rechazo = page.waitForResponse(r => r.request().method() === 'POST');
  await page.getByRole('button', { name: 'Registrar cliente', exact: true }).click();
  assert((await rechazo).status() === 200, 'Rejected input must re-render the form (HTTP 200)');
  assert((await alerta().innerText()).includes('El correo electrónico no es válido.'),
    'Invalid email must show the Spanish server-side message');
  await page.goto(base);
  assert((await total()) === inicial, 'Rejected input must not add records');

  // ---------- 3. Crear con payload XSS ----------
  await page.goto(base + 'Clientes/Crear');
  const payload = '<script>window.__crudXss=1</script>';
  await campo('Cedula').fill(cedulaE2E);
  await campo('Nombre').fill(payload);
  await campo('Apellido').fill('E2E');
  await campo('Correo').fill(correoE2E);
  await campo('Telefono').fill('0990000000');
  await campo('Ciudad').fill('Ambato');
  await casilla().check();

  const creada = page.waitForResponse(r => r.request().method() === 'POST');
  await page.getByRole('button', { name: 'Registrar cliente', exact: true }).click();
  await page.waitForURL(url => url.pathname === '/');
  assert((await creada).status() === 302, 'Successful create must redirect (PRG)');

  let fila = page.locator('tbody tr').filter({ hasText: correoE2E });
  assert(await fila.count() === 1, 'Created record must be listed exactly once');
  assert((await total()) === inicial + 1, 'Create must add exactly one record');
  assert((await fila.innerText()).includes(payload), 'XSS payload must be displayed as text');
  assert(await page.evaluate(() => window.__crudXss === undefined), 'Script payload must not execute');
  await page.reload();
  assert(await fila.count() === 1, 'Created record must persist after reload');

  // ---------- 4. IDs malformados en la edición ----------
  for (const id of ['abc', '1foo', '0', '-1']) {
    await page.goto(base + 'Clientes/Editar/' + id);
    assert((await alerta().innerText()).includes('El identificador del cliente no es válido.'),
      `Malformed edit ID "${id}" must show the exact error`);
    await page.goto(base);
    assert((await total()) === inicial + 1, `Malformed edit ID "${id}" must not modify data`);
  }

  // ---------- 5. Actualizar ----------
  await page.goto(base);
  fila = page.locator('tbody tr').filter({ hasText: correoE2E });
  await fila.getByRole('link', { name: 'Editar', exact: true }).click();
  await page.waitForURL(url => url.pathname.includes('/Clientes/Editar/'));

  await campo('Nombre').fill('Cliente actualizado');
  await campo('Telefono').fill('0981111111');
  await casilla().uncheck();

  const actualizada = page.waitForResponse(r => r.request().method() === 'POST');
  await page.getByRole('button', { name: 'Guardar cambios', exact: true }).click();
  await page.waitForURL(url => url.pathname === '/');
  assert((await actualizada).status() === 302, 'Successful update must redirect (PRG)');

  fila = page.locator('tbody tr').filter({ hasText: correoE2E });
  const texto = await fila.innerText();
  assert(texto.includes('Cliente actualizado') && texto.includes('0981111111') && texto.includes('Inactivo'),
    'Update must persist every edited field');
  assert((await total()) === inicial + 1, 'Update must not change the record count');

  // ---------- 6. Eliminar (con confirmación previa) ----------
  fila = page.locator('tbody tr').filter({ hasText: correoE2E });
  const confirmacion = await fila.locator('form[data-confirm]').getAttribute('data-confirm');
  assert(confirmacion && confirmacion.includes('¿Eliminar a Cliente actualizado E2E?'),
    'Delete form must ask for confirmation with the client name');

  assert((await enviarSinDialogo(fila)) === 302, 'Successful deletion must redirect (PRG)');

  assert((await total()) === inicial, 'Deleted record must leave the count exactly as it started');
  assert(await page.locator('tbody tr').filter({ hasText: correoE2E }).count() === 0,
    'Deleted row must be absent');

  // ---------- 7. Salud del servicio ----------
  const salud = await page.evaluate(async () => (await fetch('/health')).status);
  assert(salud === 200, '/health must answer 200 while MySQL responds');

  return 'PASS: brand assets, UTF-8 (HTML/CSS/JS), invalid email, create/read/update/delete (PRG), escaped XSS, malformed edit IDs and /health';
}
