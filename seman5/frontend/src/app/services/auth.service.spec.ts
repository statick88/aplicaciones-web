import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';
import { AuthService } from './auth.service';
import { Credenciales, Usuario } from '../interfaces/auth';

describe('AuthService', () => {
  let service: AuthService;
  let http: HttpTestingController;
  const credenciales: Credenciales = {
    correo: 'ana@uniandes.edu.ec',
    password: 'secreta',
  };
  const usuario: Usuario = { id: 1, nombre: 'Ana', correo: credenciales.correo };

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(AuthService);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('inicializa la sesión anónima antes de enviar credenciales', () => {
    service
      .ingresar(credenciales)
      .subscribe((respuesta) => expect(respuesta.usuario).toEqual(usuario));

    const inicializar = http.expectOne('/api/auth.php');
    expect(inicializar.request.method).toBe('GET');
    http.expectNone((request) => request.method === 'POST');
    inicializar.flush({ usuario: null });

    const ingresar = http.expectOne('/api/auth.php');
    expect(ingresar.request.method).toBe('POST');
    expect(ingresar.request.body).toEqual(credenciales);
    ingresar.flush({ usuario });
  });

  it('vuelve a inicializar antes de un nuevo login después de cookies borradas', () => {
    service.ingresar(credenciales).subscribe();
    http.expectOne('/api/auth.php', 'primera inicialización').flush({ usuario: null });
    http.expectOne('/api/auth.php', 'primer login').flush({ usuario });

    service.limpiarSesion();
    service
      .ingresar(credenciales)
      .subscribe((respuesta) => expect(respuesta.usuario).toEqual(usuario));
    const reinicializar = http.expectOne('/api/auth.php', 'reinicialización');
    expect(reinicializar.request.method).toBe('GET');
    reinicializar.flush({ usuario: null });
    const reingresar = http.expectOne('/api/auth.php', 'segundo login');
    expect(reingresar.request.method).toBe('POST');
    reingresar.flush({ usuario });
  });
});
