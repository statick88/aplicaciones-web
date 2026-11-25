import { TestBed } from '@angular/core/testing';
import { provideRouter, Router } from '@angular/router';
import { firstValueFrom, Observable, of } from 'rxjs';
import { authGuard } from './auth.guard';
import { AuthService } from '../services/auth.service';

describe('authGuard', () => {
  it('restaura la sesión antes de permitir una ruta protegida', async () => {
    const session = vi.fn(() => of({ id: 1, nombre: 'Ana', correo: 'ana@uniandes.edu.ec' }));
    TestBed.configureTestingModule({
      providers: [
        provideRouter([]),
        { provide: AuthService, useValue: { restaurarSesion: session } },
      ],
    });
    const result = await TestBed.runInInjectionContext(() =>
      firstValueFrom(
        authGuard({} as never, {} as never) as Observable<
          true | ReturnType<Router['createUrlTree']>
        >,
      ),
    );
    expect(result).toBe(true);
    expect(session).toHaveBeenCalledOnce();
  });

  it('redirige al login cuando no existe sesión', async () => {
    TestBed.configureTestingModule({
      providers: [
        provideRouter([]),
        { provide: AuthService, useValue: { restaurarSesion: () => of(null) } },
      ],
    });
    const router = TestBed.inject(Router);
    const result = await TestBed.runInInjectionContext(() =>
      firstValueFrom(
        authGuard({} as never, {} as never) as Observable<
          true | ReturnType<Router['createUrlTree']>
        >,
      ),
    );
    expect(router.serializeUrl(result as ReturnType<Router['createUrlTree']>)).toBe('/login');
  });
});
