import { Component } from '@angular/core';
//import { RegistrarComponent } from '../../registro/registrar-platillo/registrar.component';
import { Router } from '@angular/router';
import { AuthService } from '../../../services/auth.service';
import { FormBuilder, FormGroup, Validators,  } from '@angular/forms';
@Component({
  selector: 'app-login',
  standalone: true,
  imports: [],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent 
{
  public formulario: FormGroup;

  constructor(private router: Router, private formBuilder: FormBuilder, private authService: AuthService) {
    this.formulario = this.formBuilder.group({
      user: [null,Validators.required, Validators.minLength(6), Validators.maxLength(20)],
      password: [null,Validators.required, Validators.minLength(6), Validators.maxLength(20)]
    });
  }

  public login() {
    const { user, password } = this.formulario.value;
    this.authService.login(user, password).subscribe(
      res => {
        if (res) {
          this.router.navigate(['/home']);
        }
      },
      err => {
        console.log(err);
        console.log(err.error.message);
       alert('Usuario o contraseña incorrectos.');
      });
  }
  Propietario() {
    this.router.navigate(['/registro']);
    }
  }
