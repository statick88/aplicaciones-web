import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ClienteService } from './cliente.service';
import { Cliente, ClienteInput } from '../interfaces/cliente';

describe('ClienteService', () => {
  let service: ClienteService;
  let http: HttpTestingController;
  const cliente: Cliente = {
    id: 4,
    nombre: 'Ana',
    apellido: 'Paz',
    correo: 'ana@uniandes.edu.ec',
    telefono: '0999999999',
  };
  const input: ClienteInput = {
    nombre: 'Ana',
    apellido: 'Paz',
    correo: 'ana@uniandes.edu.ec',
    telefono: '0999999999',
  };

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(ClienteService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('usa los contratos CRUD de la API relativa', () => {
    service.listar().subscribe((resultado) => expect(resultado).toEqual([cliente]));
    http.expectOne('/api/api.php').flush([cliente]);

    service.obtener(4).subscribe((resultado) => expect(resultado).toEqual(cliente));
    http.expectOne('/api/api.php?id=4').flush(cliente);

    service.crear(input).subscribe((resultado) => expect(resultado.id).toBe(4));
    const crear = http.expectOne('/api/api.php');
    expect(crear.request.method).toBe('POST');
    expect(crear.request.body).toEqual(input);
    crear.flush({ estado: true, id: 4 }, { status: 201, statusText: 'Created' });

    service.actualizar(4, input).subscribe((resultado) => expect(resultado.estado).toBe(true));
    const actualizar = http.expectOne('/api/api.php?id=4');
    expect(actualizar.request.method).toBe('PUT');
    actualizar.flush({ estado: true, id: 4 });

    service.eliminar(4).subscribe((resultado) => expect(resultado.estado).toBe(true));
    const eliminar = http.expectOne('/api/api.php?id=4');
    expect(eliminar.request.method).toBe('DELETE');
    eliminar.flush({ estado: true });
  });
});
