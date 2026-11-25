import { Injectable, computed, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { catchError, concatMap, map, of, tap } from 'rxjs';
import { Credenciales, SesionRespuesta, Usuario } from '../interfaces/auth';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly usuarioSignal = signal<Usuario | null>(null);
  readonly usuario = this.usuarioSignal.asReadonly();
  readonly autenticado = computed(() => this.usuarioSignal() !== null);

  restaurarSesion() {
    return this.http.get<SesionRespuesta>('/api/auth.php').pipe(
      map(({ usuario }) => usuario),
      tap((usuario) => this.usuarioSignal.set(usuario)),
      catchError(() => {
        this.usuarioSignal.set(null);
        return of(null);
      }),
    );
  }

  ingresar(credenciales: Credenciales) {
    return this.http.get<SesionRespuesta>('/api/auth.php').pipe(
      concatMap(() => this.http.post<SesionRespuesta>('/api/auth.php', credenciales)),
      tap(({ usuario }) => this.usuarioSignal.set(usuario)),
    );
  }

  salir() {
    return this.http
      .delete<{ estado: boolean }>('/api/auth.php')
      .pipe(tap(() => this.usuarioSignal.set(null)));
  }

  limpiarSesion() {
    this.usuarioSignal.set(null);
  }
}
