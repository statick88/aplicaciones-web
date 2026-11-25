import { Component, OnInit, inject, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { ClienteService } from '../../services/cliente.service';

@Component({
  selector: 'app-cliente-form',
  imports: [ReactiveFormsModule, RouterLink],
  template: ` <section class="page narrow" aria-labelledby="form-title">
    <a routerLink="/clientes" class="back">← Volver a clientes</a>
    <p class="eyebrow">{{ editando ? 'Edición' : 'Nuevo registro' }}</p>
    <h1 id="form-title">{{ editando ? 'Editar cliente' : 'Registrar cliente' }}</h1>
    @if (cargando()) {
      <p class="state" role="status">Cargando datos…</p>
    } @else {
      <form class="client-form" [formGroup]="form" (ngSubmit)="guardar()" novalidate>
        <div class="fields">
          <div>
            <label for="nombre">Nombre</label><input id="nombre" formControlName="nombre" />
            @if (invalido('nombre')) {
              <small>El nombre es obligatorio.</small>
            }
          </div>
          <div>
            <label for="apellido">Apellido</label><input id="apellido" formControlName="apellido" />
            @if (invalido('apellido')) {
              <small>El apellido es obligatorio.</small>
            }
          </div>
          <div>
            <label for="correo">Correo electrónico</label
            ><input id="correo" type="email" formControlName="correo" />
            @if (invalido('correo')) {
              <small>Ingresá un correo válido.</small>
            }
          </div>
          <div>
            <label for="telefono">Teléfono</label
            ><input id="telefono" type="tel" formControlName="telefono" />
            @if (invalido('telefono')) {
              <small>Ingresá un teléfono válido.</small>
            }
          </div>
        </div>
        @if (mensaje()) {
          <p class="notice error" role="alert">{{ mensaje() }}</p>
        }
        <div class="form-actions">
          <a routerLink="/clientes">Cancelar</a
          ><button type="submit" [disabled]="form.invalid || guardando()">
            {{ guardando() ? 'Guardando…' : 'Guardar cliente' }}
          </button>
        </div>
      </form>
    }
  </section>`,
})
export class ClienteFormComponent implements OnInit {
  private readonly fb = inject(FormBuilder);
  private readonly service = inject(ClienteService);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  readonly guardando = signal(false);
  readonly cargando = signal(false);
  readonly mensaje = signal('');
  readonly id = Number(this.route.snapshot.paramMap.get('id'));
  readonly editando = Number.isInteger(this.id) && this.id > 0;
  readonly form = this.fb.nonNullable.group({
    nombre: ['', [Validators.required, Validators.maxLength(80)]],
    apellido: ['', [Validators.required, Validators.maxLength(80)]],
    correo: ['', [Validators.required, Validators.email, Validators.maxLength(160)]],
    telefono: ['', [Validators.required, Validators.pattern(/^[+0-9() .-]{7,25}$/)]],
  });
  ngOnInit() {
    if (this.editando) {
      this.cargando.set(true);
      this.service.obtener(this.id).subscribe({
        next: (cliente) => {
          this.form.patchValue(cliente);
          this.cargando.set(false);
        },
        error: (error: HttpErrorResponse) => {
          this.mensaje.set(error.error?.mensaje ?? 'No se pudo cargar el cliente.');
          this.cargando.set(false);
        },
      });
    }
  }
  invalido(campo: keyof typeof this.form.controls) {
    const control = this.form.controls[campo];
    return control.touched && control.invalid;
  }
  guardar() {
    if (this.form.invalid || this.guardando()) {
      this.form.markAllAsTouched();
      return;
    }
    this.guardando.set(true);
    this.mensaje.set('');
    const operacion = this.editando
      ? this.service.actualizar(this.id, this.form.getRawValue())
      : this.service.crear(this.form.getRawValue());
    operacion.subscribe({
      next: () =>
        void this.router.navigate(['/clientes'], {
          state: { mensaje: this.editando ? 'Cliente actualizado.' : 'Cliente creado.' },
        }),
      error: (error: HttpErrorResponse) => {
        this.mensaje.set(
          error.error?.mensaje ?? 'No se pudo guardar. Revisá los datos e intentá nuevamente.',
        );
        this.guardando.set(false);
      },
    });
  }
}
