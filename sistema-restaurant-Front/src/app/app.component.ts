import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { NavComponent } from '././shared/components/nav/nav.component';
import { HomeComponent } from './modulos/home/home.component';
import { ReactiveFormsModule, FormBuilder } from '@angular/forms';

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet,HomeComponent,NavComponent, ReactiveFormsModule ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent {
  show = true;
  title = 'sistema-restaurant-Front';
}
