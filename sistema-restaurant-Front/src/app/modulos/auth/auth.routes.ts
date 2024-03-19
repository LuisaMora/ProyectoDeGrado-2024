import { Routes } from "@angular/router";
import { FormRegistroComponent } from "./form-registro/form-registro.component";
import { LoginComponent } from "./login/login.component";

export const AUTH_ROUTES:Routes=[
{
    path:'register',
    component:FormRegistroComponent
},
{
    path:'login',
    component:LoginComponent
}
] 