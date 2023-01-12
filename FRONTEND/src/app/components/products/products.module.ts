import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule, Routes } from "@angular/router";
import { NgxSliderModule } from "@angular-slider/ngx-slider";
import { FormsModule } from "@angular/forms";

import { NgSelectModule } from "@ng-select/ng-select";

import { DetailComponent } from "./detail/detail.component";
import { CartComponent } from "../cart/cart.component";
import { CatalogueComponent } from "./catalogue/catalogue.component";
import { AuthGuard } from "../../guards/auth.guard";

const routes: Routes = [
  {
    path: "",
    component: CatalogueComponent
  },
  {
    path: ":id",
    component: DetailComponent
  },
  {
    path: "cart",
    component: CartComponent,
    canActivate: [AuthGuard],
    data: {
      roles: ["USER"]
    }
  },
  {
    path: "**",
    redirectTo: "profile"
  }
];
@NgModule({
  declarations: [CatalogueComponent, DetailComponent],
  imports: [
    CommonModule,
    NgSelectModule,
    NgxSliderModule,
    FormsModule,
    RouterModule.forChild(routes)
  ]
})
export class ProductsModule {}
