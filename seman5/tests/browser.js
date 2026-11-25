async page => {
  const baseUrl = 'http://127.0.0.1:18086';
  const email = 'integration@example.test';
  const password = 'Public-Test-Fixture-2026!';
  const suffix = Date.now();
  const cliente = {
    nombre: `E2E ${suffix}`,
    apellido: 'Desechable',
    correo: `e2e-${suffix}@example.test`,
    telefono: '0999999999',
  };
  const results = [];
  const check = (condition, name) => {
    if (!condition) throw new Error(name);
    results.push(name);
  };
  const fillClient = async data => {
    await page.getByRole('textbox', { name: 'Nombre' }).fill(data.nombre);
    await page.getByRole('textbox', { name: 'Apellido' }).fill(data.apellido);
    await page.getByRole('textbox', { name: 'Correo electrónico' }).fill(data.correo);
    await page.getByRole('textbox', { name: 'Teléfono' }).fill(data.telefono);
  };

  await page.context().clearCookies();
  await page.goto(`${baseUrl}/login`);

  const submit = page.getByRole('button', { name: 'Ingresar' });
  check(await submit.isDisabled(), 'empty login form is disabled');
  await page.getByRole('textbox', { name: 'Correo electrónico' }).fill('correo-invalido');
  await page.getByRole('textbox', { name: 'Contraseña' }).fill(password);
  check(await submit.isDisabled(), 'malformed email keeps login disabled');
  await page.getByRole('textbox', { name: 'Correo electrónico' }).fill(email);
  await page.getByRole('textbox', { name: 'Contraseña' }).fill('incorrecta-para-prueba');
  await submit.click();
  await page.getByRole('alert').waitFor();
  check((await page.getByRole('alert').textContent()).trim() === 'Correo o contraseña incorrectos.', 'invalid login has exact credential feedback');

  await page.getByRole('textbox', { name: 'Contraseña' }).fill(password);
  await submit.click();
  await page.waitForURL('**/clientes');
  check(page.url().endsWith('/clientes'), 'fresh direct login succeeds without a readiness sleep');
  check(await page.getByRole('img', { name: 'Universidad Regional Autónoma de los Andes' }).evaluate(img => img.complete && img.naturalWidth > 0), 'institutional logo loaded');

  await page.getByRole('link', { name: 'Nuevo cliente' }).click();
  await fillClient(cliente);
  await page.getByRole('button', { name: 'Guardar cliente' }).click();
  await page.waitForURL('**/clientes');
  let row = page.getByRole('row', { name: new RegExp(cliente.correo) });
  await row.waitFor();
  check(await row.getByText(cliente.nombre).count() === 1, 'client create and read work through the table');

  await row.getByRole('link', { name: 'Editar' }).click();
  await page.getByRole('textbox', { name: 'Nombre' }).fill(`${cliente.nombre} actualizado`);
  await page.getByRole('button', { name: 'Guardar cliente' }).click();
  await page.waitForURL('**/clientes');
  row = page.getByRole('row', { name: new RegExp(cliente.correo) });
  await row.waitFor();
  check(await row.getByText(`${cliente.nombre} actualizado`).count() === 1, 'client update works through the UI');
  await page.screenshot({ path: 'aplicaciones-web/seman5/docs/clientes.png', fullPage: true });

  await page.reload();
  await page.getByRole('button', { name: 'Cerrar sesión' }).waitFor();
  check(page.url().endsWith('/clientes'), 'reload restores the server session');

  await page.getByRole('link', { name: 'Nuevo cliente' }).click();
  await fillClient({ ...cliente, nombre: 'Duplicado' });
  await page.getByRole('button', { name: 'Guardar cliente' }).click();
  await page.getByRole('alert').waitFor();
  check(await page.getByRole('textbox', { name: 'Nombre' }).inputValue() === 'Duplicado', 'failed duplicate create retains the form');

  await page.setViewportSize({ width: 390, height: 844 });
  await page.getByRole('textbox', { name: 'Nombre' }).focus();
  const mobile = await page.evaluate(() => ({
    horizontalOverflow: document.documentElement.scrollWidth > window.innerWidth,
    focusVisible: (() => {
      const style = getComputedStyle(document.activeElement);
      return style.outlineStyle !== 'none' || style.boxShadow !== 'none';
    })(),
  }));
  check(!mobile.horizontalOverflow, 'mobile 390px layout has no horizontal overflow');
  check(mobile.focusVisible, 'keyboard focus is visibly styled');
  await page.screenshot({ path: 'aplicaciones-web/seman5/docs/mobile.png', fullPage: true });
  await page.setViewportSize({ width: 1280, height: 800 });

  await page.getByRole('link', { name: '← Volver a clientes' }).click();
  row = page.getByRole('row', { name: new RegExp(cliente.correo) });
  await row.getByRole('button', { name: 'Eliminar' }).click();
  await row.getByRole('button', { name: 'Confirmar eliminación' }).click();
  await row.waitFor({ state: 'detached' });
  check(true, 'client delete works through the UI');

  await page.route('**/api/auth.php', async route => {
    if (route.request().method() !== 'GET') {
      await route.fulfill({ status: 500, contentType: 'application/json', body: '{"error":"fallo simulado"}' });
    } else {
      await route.continue();
    }
  });
  await page.getByRole('button', { name: 'Cerrar sesión' }).click();
  await page.getByRole('alert').waitFor();
  check(await page.getByRole('button', { name: 'Cerrar sesión' }).isVisible(), 'logout 500 alerts and retains the session');
  await page.unroute('**/api/auth.php');

  await page.getByRole('button', { name: 'Cerrar sesión' }).click();
  await page.waitForURL('**/login');
  check(page.url().endsWith('/login'), 'logout retry removes the session');

  await page.getByRole('textbox', { name: 'Correo electrónico' }).fill(email);
  await page.getByRole('textbox', { name: 'Contraseña' }).fill(password);
  await page.getByRole('button', { name: 'Ingresar' }).click();
  await page.waitForURL('**/clientes');
  check(page.url().endsWith('/clientes'), 'login after successful logout works in the same SPA');
  await page.getByRole('button', { name: 'Cerrar sesión' }).click();
  await page.waitForURL('**/login');
  check(page.url().endsWith('/login'), 'second successful logout works in the same SPA');

  await page.goto(`${baseUrl}/clientes`);
  await page.waitForURL('**/login');
  check(page.url().endsWith('/login'), 'protected route redirects without a session');
  check(page.url().endsWith('/login'), 'protected URL redirects after logout');
  await page.context().clearCookies();
  await page.goto(`${baseUrl}/clientes`);
  await page.waitForURL('**/login');
  check(page.url().endsWith('/login'), 'cleared session cookies redirect after an unauthenticated request');

  return { status: 'PASS', checks: results };
}
