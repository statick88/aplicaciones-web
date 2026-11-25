import { Component, inject, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule],
  template: ` <main class="auth-page">
    <section class="auth-card" aria-labelledby="login-title">
      <img
        src="logo-uniandes.png"
        alt="Universidad Regional Autónoma de los Andes"
        class="login-logo"
      />
      <p class="eyebrow">Gestión académica</p>
      <h1 id="login-title">Acceso a clientes</h1>
      <p class="intro">Ingresá con tus credenciales para administrar los registros.</p>
      @if (mensaje()) {
        <p class="notice error" role="alert">{{ mensaje() }}</p>
      }
      @if (sesionVencida) {
        <p class="notice" role="status">Tu sesión terminó. Iniciá sesión nuevamente.</p>
      }
      <form [formGroup]="form" (ngSubmit)="enviar()" novalidate>
        <label for="correo">Correo electrónico</label
        ><input id="correo" type="email" autocomplete="email" formControlName="correo" />
        @if (form.controls.correo.touched && form.controls.correo.invalid) {
          <small>Ingresá un correo válido.</small>
        }
        <label for="password">Contraseña</label
        ><input
          id="password"
          type="password"
          autocomplete="current-password"
          formControlName="password"
        />
        @if (form.controls.password.touched && form.controls.password.invalid) {
          <small>La contraseña es obligatoria.</small>
        }
        <button type="submit" [disabled]="form.invalid || enviando()">
          {{ enviando() ? 'Ingresando…' : 'Ingresar' }}
        </button>
      </form>
    </section>
  </main>`,
})
export class LoginComponent {
  private readonly fb = inject(FormBuilder);
  private readonly auth = inject(AuthService);
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);
  readonly enviando = signal(false);
  readonly mensaje = signal('');
  readonly sesionVencida = this.route.snapshot.queryParamMap.get('sesion') === 'vencida';
  readonly form = this.fb.nonNullable.group({
    correo: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required],
  });

  enviar() {
    if (this.form.invalid || this.enviando()) {
      this.form.markAllAsTouched();
      return;
    }
    this.enviando.set(true);
    this.mensaje.set('');
    this.auth.ingresar(this.form.getRawValue()).subscribe({
      next: () => void this.router.navigate(['/clientes']),
      error: (error: HttpErrorResponse) => {
        this.mensaje.set(error.error?.mensaje ?? 'No fue posible ingresar. Intentá nuevamente.');
        this.enviando.set(false);
      },
    });
  }
}
