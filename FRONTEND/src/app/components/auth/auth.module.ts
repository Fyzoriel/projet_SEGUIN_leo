import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule, Routes } from "@angular/router";
import { ReactiveFormsModule } from "@angular/forms";

import { RegisterFormComponent } from "./register-form/register-form.component";
import { LoginFormComponent } from "./login-form/login-form.component";
import { ClientComponent } from "./register-form/client/client.component";
import { SellerComponent } from "./register-form/seller/seller.component";
import { NgSelectModule } from "@ng-select/ng-select";

const routes: Routes = [
  {
    path: "register",
    component: RegisterFormComponent
  },
  {
    path: "login",
    component: LoginFormComponent
  },
  {
    path: "**",
    redirectTo: "login"
  }
];

@NgModule({
  declarations: [
    RegisterFormComponent,
    LoginFormComponent,
    ClientComponent,
    SellerComponent
  ],
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterModule.forChild(routes),
    NgSelectModule
  ]
})
export class AuthModule {}
