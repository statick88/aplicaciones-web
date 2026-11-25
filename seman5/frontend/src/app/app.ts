import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { Router, RouterLink, RouterOutlet } from '@angular/router';
import { finalize } from 'rxjs';
import { AuthService } from './services/auth.service';

@Component({
  selector: 'app-root',
  imports: [RouterLink, RouterOutlet],
  templateUrl: './app.html',
  styleUrl: './app.css',
})
export class App {
  readonly auth = inject(AuthService);
  readonly cerrandoSesion = signal(false);
  readonly errorDeSalida = signal('');
  private readonly router = inject(Router);

  salir() {
    if (this.cerrandoSesion()) return;

    this.cerrandoSesion.set(true);
    this.errorDeSalida.set('');
    this.auth
      .salir()
      .pipe(finalize(() => this.cerrandoSesion.set(false)))
      .subscribe({
        next: () => void this.router.navigate(['/login']),
        error: (error: HttpErrorResponse) => {
          if (error.status === 401) {
            this.auth.limpiarSesion();
            void this.router.navigate(['/login'], { queryParams: { sesion: 'vencida' } });
            return;
          }
          this.errorDeSalida.set('No se pudo cerrar sesión. Tu sesión sigue activa; reintentá.');
        },
      });
  }
}
