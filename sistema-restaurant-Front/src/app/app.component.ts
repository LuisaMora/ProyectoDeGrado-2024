import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { NavComponent } from '././shared/components/nav/nav.component';
import { HomeComponent } from './modulos/home/home.component';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet,HomeComponent,NavComponent ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent {
  show = true;
  title = 'sistema-restaurant-Front';
}
