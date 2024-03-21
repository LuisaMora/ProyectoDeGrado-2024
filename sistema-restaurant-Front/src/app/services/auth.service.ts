import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Administrador } from '../modelos/usuario/Administrador';

@Injectable()
export class AuthService {

  constructor(private  http: HttpClient) { }

  /**
    login
user: string, email: string   
donde user puede ser un nickname o un correo
**/
  public login(user: string, email: string) {
    return this.http.post<Administrador>;
  }
}
