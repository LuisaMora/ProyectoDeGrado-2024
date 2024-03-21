import { Component } from '@angular/core';
import { HomeRestaurantComponent } from '../../home-restaurant/home-restaurant.component';
@Component({
  selector: 'app-lista',
  standalone: true,
  imports: [HomeRestaurantComponent],
  templateUrl: './lista.component.html',
  styleUrl: './lista.component.scss'
})
export class ListaComponent {

}
