import { Routes } from "@angular/router";
import { ListaCategoriasComponent } from "./lista-categorias/lista-categorias.component";
import { ListaComponent } from "./lista-platillos/lista.component";

export const ROUTES_MENU:Routes=[
    {
        path:'lista-categorias',
        component:ListaCategoriasComponent
    },
    {
        path:'lista-platillos',
        component:ListaComponent
    }
]