import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { LoginComponent } from './login.component';
import { AuthService } from '../../services/auth.service';

describe('LoginComponent', () => {
  it('no envía credenciales mientras el formulario sea inválido', async () => {
    const ingresar = vi.fn();
    await TestBed.configureTestingModule({
      imports: [LoginComponent],
      providers: [provideRouter([]), { provide: AuthService, useValue: { ingresar } }],
    }).compileComponents();
    const fixture = TestBed.createComponent(LoginComponent);
    fixture.detectChanges();
    fixture.componentInstance.enviar();
    expect(ingresar).not.toHaveBeenCalled();
    expect(fixture.componentInstance.form.invalid).toBe(true);
  });
});
