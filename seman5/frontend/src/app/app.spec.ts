import { HttpErrorResponse, provideHttpClient } from '@angular/common/http';
import { TestBed } from '@angular/core/testing';
import { provideRouter, Router } from '@angular/router';
import { signal, WritableSignal } from '@angular/core';
import { of, throwError } from 'rxjs';
import { App } from './app';
import { AuthService } from './services/auth.service';
import { Usuario } from './interfaces/auth';

describe('App', () => {
  it('muestra la marca institucional', async () => {
    await TestBed.configureTestingModule({
      imports: [App],
      providers: [provideHttpClient(), provideRouter([])],
    }).compileComponents();
    const fixture = TestBed.createComponent(App);
    fixture.detectChanges();
    expect((fixture.nativeElement as HTMLElement).textContent).toContain('Clientes');
  });

  it('vuelve a habilitar cerrar sesión después de una salida exitosa', async () => {
    const salir = vi.fn(() => of({ estado: true }));
    await TestBed.configureTestingModule({
      imports: [App],
      providers: [
        provideRouter([]),
        { provide: AuthService, useValue: { usuario: signal(null), salir } },
      ],
    }).compileComponents();
    const router = TestBed.inject(Router);
    vi.spyOn(router, 'navigate').mockResolvedValue(true);
    const fixture = TestBed.createComponent(App);
    fixture.detectChanges();

    fixture.componentInstance.salir();
    expect(fixture.componentInstance.cerrandoSesion()).toBe(false);
    fixture.componentInstance.salir();

    expect(salir).toHaveBeenCalledTimes(2);
  });

  it('conserva la sesión y ofrece reintentar cuando cerrar sesión falla', async () => {
    const usuario: WritableSignal<Usuario | null> = signal({
      id: 1,
      nombre: 'Ana',
      correo: 'ana@uniandes.edu.ec',
    });
    const limpiarSesion = vi.fn();
    const salir = vi.fn(() => throwError(() => new HttpErrorResponse({ status: 500 })));
    await TestBed.configureTestingModule({
      imports: [App],
      providers: [
        provideRouter([]),
        { provide: AuthService, useValue: { usuario, salir, limpiarSesion } },
      ],
    }).compileComponents();
    const router = TestBed.inject(Router);
    const navegar = vi.spyOn(router, 'navigate');
    const fixture = TestBed.createComponent(App);
    fixture.detectChanges();

    fixture.componentInstance.salir();
    fixture.detectChanges();

    expect(limpiarSesion).not.toHaveBeenCalled();
    expect(navegar).not.toHaveBeenCalled();
    expect(usuario()?.nombre).toBe('Ana');
    const alert = (fixture.nativeElement as HTMLElement).querySelector('[role="alert"]');
    expect(alert?.textContent).toContain('No se pudo cerrar sesión');
    expect(alert?.querySelector('button')?.textContent).toContain('Reintentar');
  });
});
