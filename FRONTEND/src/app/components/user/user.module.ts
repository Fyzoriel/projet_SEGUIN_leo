import { NgModule } from "@angular/core";
import { CommonModule } from "@angular/common";
import { RouterModule, Routes } from "@angular/router";

import { ProfileComponent } from "./profile/profile.component";
import { PhonePipe } from "../../pipes/phone/phone.pipe";

const routes: Routes = [
  {
    path: ":id",
    component: ProfileComponent
  },
  {
    path: "**",
    redirectTo: "/"
  }
];

@NgModule({
  declarations: [ProfileComponent, PhonePipe],
  imports: [CommonModule, RouterModule.forChild(routes)]
})
export class UserModule {}
