import { Component } from '@angular/core';
import { HomeRestaurantComponent } from '../../home-restaurant/home-restaurant.component';
@Component({
  selector: 'app-lista-categorias',
  standalone: true,
  imports: [HomeRestaurantComponent],
  templateUrl: './lista-categorias.component.html',
  styleUrl: './lista-categorias.component.scss'
})
export class ListaCategoriasComponent {

}
