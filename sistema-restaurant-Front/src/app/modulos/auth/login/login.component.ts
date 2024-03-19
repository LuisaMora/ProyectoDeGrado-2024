import { Component } from '@angular/core';
//import { RegistrarComponent } from '../../registro/registrar-platillo/registrar.component';
import { Router } from '@angular/router';
@Component({
  selector: 'app-login',
  standalone: true,
  imports: [],
  templateUrl: './login.component.html',
  styleUrl: './login.component.scss'
})
export class LoginComponent 
{
  constructor(private router: Router) {}

  Propietario() {
    this.router.navigate(['/registro']);
    }
  }
