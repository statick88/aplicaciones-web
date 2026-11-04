async page => {
  const base = new URL('index.php', page.url()).href;
  const assert = (condition, message) => { if (!condition) throw new Error(message); };
  const heading = () => page.getByRole('heading', { name: /^Clientes registrados/ }).innerText();
  const submit = async name => {
    await Promise.all([
      page.waitForNavigation(),
      page.getByRole('button', { name, exact: true }).click(),
    ]);
  };
  await page.goto(base);
  assert((await heading()).includes('(0)'), 'Run only against an empty isolated test database');
  const logo = page.getByAltText('Universidad de los Andes');
  assert(await logo.isVisible(), 'Institutional logo must be visible');
  assert(await logo.evaluate(img => img.complete && img.naturalWidth > 0), 'Logo must load');

  await page.getByLabel('Nombre', { exact: true }).fill('Cliente de prueba');
  await page.getByLabel('Correo', { exact: true }).fill('correo-invalido');
  await submit('Registrar cliente');
  assert((await page.getByRole('alert').innerText()).includes('El correo electrónico no es válido.'), 'Invalid email must be rejected');
  assert((await heading()).includes('(0)'), 'Rejected input must not add records');

  const payload = '<script>window.__crudXss=1</script>';
  await page.getByLabel('Nombre', { exact: true }).fill(payload);
  await page.getByLabel('Correo', { exact: true }).fill('crud-proof@example.com');
  await page.getByLabel('Teléfono').fill('0990000000');
  await page.getByLabel('Cliente activo').check();
  const createdResponse = page.waitForResponse(r => r.request().method() === 'POST');
  await submit('Registrar cliente');
  assert((await createdResponse).status() === 302, 'Successful create must redirect');
  let row = page.locator('tbody tr').filter({ hasText: 'crud-proof@example.com' });
  assert(await row.count() === 1, 'Created record must be listed exactly once');
  assert((await row.locator('td').first().innerText()) === '1', 'Rejected input must not consume an ID');
  assert((await row.innerText()).includes(payload), 'XSS payload must be displayed as text');
  assert(await page.evaluate(() => window.__crudXss === undefined), 'Script payload must not execute');
  await page.reload();
  assert(await row.count() === 1, 'Created record must persist after reload');

  for (const id of ['abc', '1foo', '0', '-1']) {
    await page.goto(base + '?editar=' + id);
    assert((await page.getByRole('alert').innerText()).includes('El identificador del cliente no es válido.'), 'Malformed edit ID must show the exact error');
    assert(await page.locator('.formulario input[name="accion"]').inputValue() === 'crear', 'Malformed ID must not select another record');
    assert((await heading()).includes('(1)'), 'Malformed ID must not modify data');
  }
  await page.goto(base);
  await Promise.all([page.waitForNavigation(), row.getByRole('link', { name: 'Editar', exact: true }).click()]);
  await page.getByLabel('Nombre', { exact: true }).fill('Cliente actualizado');
  await page.getByLabel('Teléfono').fill('0981111111');
  await page.getByLabel('Cliente activo').uncheck();
  const updatedResponse = page.waitForResponse(r => r.request().method() === 'POST');
  await submit('Guardar cambios');
  assert((await updatedResponse).status() === 302, 'Successful update must redirect');
  row = page.locator('tbody tr').filter({ hasText: 'crud-proof@example.com' });
  const text = await row.innerText();
  assert(text.includes('Cliente actualizado') && text.includes('0981111111') && text.includes('Inactivo'), 'Update must persist every edited field');

  const deletedResponse = page.waitForResponse(r => r.request().method() === 'POST');
  await Promise.all([page.waitForNavigation(), row.getByRole('button', { name: 'Eliminar', exact: true }).click()]);
  assert((await deletedResponse).status() === 302, 'Successful deletion must redirect');
  await page.reload();
  assert((await heading()).includes('(0)'), 'Deleted record must remain absent after reload');
  assert(await page.locator('tbody tr').filter({ hasText: 'crud-proof@example.com' }).count() === 0, 'Deleted row must be absent');
  return 'PASS: create, read, update, delete, invalid email, escaped XSS, malformed edit IDs and institutional logo';
}
