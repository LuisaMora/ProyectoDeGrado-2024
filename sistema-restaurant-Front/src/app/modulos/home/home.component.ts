import { Component } from '@angular/core';
import { NavComponent } from '../../../app/shared/components/nav/nav.component';
import { FooterComponent } from '../../shared/components/footer/footer.component';
@Component({
  selector: 'app-home',
  standalone: true,
  imports: [NavComponent,FooterComponent],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss'
})
export class HomeComponent {

}
