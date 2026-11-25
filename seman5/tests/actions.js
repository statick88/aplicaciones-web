async page => {
  const base = 'http://127.0.0.1:18086';
  const email = `acciones-${Date.now()}@example.test`;
  const checks = [];
  let identifier;
  const check = (condition, message) => {
    if (!condition) throw new Error(message);
    checks.push(message);
  };
  const api = async (method, path, data) => {
    const cookies = await page.context().cookies(base);
    const token = cookies.find(cookie => cookie.name === 'XSRF-TOKEN')?.value;
    const response = await page.request.fetch(`${base}/api/${path}`, {
      method,
      data,
      headers: token ? { 'X-XSRF-TOKEN': token } : {},
    });
    if (!response.ok()) throw new Error(`Fixture API ${method} ${path}: ${response.status()}`);
    return response.json();
  };
  const measure = async element => element.evaluate(control => {
    const walker = document.createTreeWalker(control, NodeFilter.SHOW_TEXT);
    let text;
    while ((text = walker.nextNode()) && !text.textContent.trim()) {}
    if (!text) throw new Error('Action has no visible label');
    const range = document.createRange();
    range.selectNodeContents(text);
    const label = range.getBoundingClientRect();
    const box = control.getBoundingClientRect();
    const style = getComputedStyle(control);
    return {
      textY: label.y + label.height / 2, y: box.y, height: box.height,
      display: style.display, alignItems: style.alignItems,
      lineHeight: style.lineHeight, font: style.font,
    };
  });
  try {
    await page.context().clearCookies();
    await api('GET', 'auth.php');
    await api('POST', 'auth.php', {
      correo: 'integration@example.test', password: 'Public-Test-Fixture-2026!',
    });
    identifier = (await api('POST', 'api.php', {
      nombre: 'Ana', apellido: 'Pérez', correo: email, telefono: '+593 99 000 1424',
    })).id;
    await page.goto(`${base}/clientes`);
    const row = page.getByRole('row').filter({ hasText: email });
    await row.waitFor();
    for (const width of [1280, 390]) {
      await page.setViewportSize({ width, height: width === 390 ? 844 : 800 });
      const edit = row.getByRole('link', { name: 'Editar', exact: true });
      const remove = row.getByRole('button', { name: 'Eliminar', exact: true });
      const left = await measure(edit);
      const right = await measure(remove);
      check(Math.abs(left.textY - right.textY) <= 1,
        `${width}px: labels must share a vertical center; Editar=${JSON.stringify(left)}, Eliminar=${JSON.stringify(right)}`);
      check(Math.abs(left.height - right.height) <= 1 && left.height >= 44,
        `${width}px: action controls share a height of at least 44px`);
      check(!(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth)),
        `${width}px: page has no horizontal overflow`);
      await page.screenshot({
        path: `aplicaciones-web/seman5/docs/${width === 390 ? 'mobile' : 'clientes'}.png`,
        fullPage: true,
      });
    }
    await page.setViewportSize({ width: 1280, height: 800 });
    await row.getByRole('link', { name: 'Editar', exact: true }).click();
    await page.getByLabel('Nombre', { exact: true }).waitFor();
    check(await page.getByLabel('Nombre', { exact: true }).inputValue() === 'Ana',
      'Editar still navigates to the populated edit form');
    await page.getByRole('link', { name: '← Volver a clientes' }).click();
    await row.getByRole('button', { name: 'Eliminar', exact: true }).click();
    check((await api('GET', `api.php?id=${identifier}`)).correo === email,
      'First Eliminar click preserves the record and asks for confirmation');
    await row.getByRole('button', { name: 'Confirmar eliminación', exact: true }).click();
    await row.waitFor({ state: 'detached' });
    const remaining = await api('GET', 'api.php');
    check(!remaining.some(client => String(client.id) === String(identifier)),
      'Confirmed Eliminar still deletes only the fixture record');
    identifier = undefined;
    return { status: 'PASS', checks };
  } finally {
    if (identifier !== undefined) {
      const fixture = await api('GET', `api.php?id=${identifier}`);
      if (fixture.correo !== email) throw new Error('Unexpected fixture identity; nothing deleted');
      await api('DELETE', `api.php?id=${identifier}`);
    }
    await api('DELETE', 'auth.php');
  }
}
