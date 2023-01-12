import { NgModule } from "@angular/core";
import { RouterModule, Routes } from "@angular/router";

import { NegateAuthGuard } from "./guards/negateAuth.guard";
import { AuthGuard } from "./guards/auth.guard";

const routes: Routes = [
  {
    path: "",
    pathMatch: "full",
    loadChildren: async () =>
      (await import("./components/products/products.module")).ProductsModule,
    canActivate: [AuthGuard]
  },
  {
    path: "products",
    loadChildren: async () =>
      (await import("./components/products/products.module")).ProductsModule,
    canActivate: [AuthGuard]
  },
  {
    path: "auth",
    loadChildren: async () =>
      (await import("./components/auth/auth.module")).AuthModule,
    canActivate: [NegateAuthGuard]
  },
  {
    path: "user",
    loadChildren: async () =>
      (await import("./components/user/user.module")).UserModule,
    canActivate: [AuthGuard],
    data: {
      roles: ["CLIENT"]
    }
  },
  {
    path: "cart",
    loadChildren: async () =>
      (await import("./components/cart/cart.module")).CartModule,
    canActivate: [AuthGuard],
    data: {
      roles: ["CLIENT"]
    }
  },
  {
    path: "manage",
    loadChildren: async () =>
      (await import("./components/manage/manage.module")).ManageModule,
    canActivate: [AuthGuard],
    data: {
      roles: ["SELLER"]
    }
  },
  { path: "**", redirectTo: "" }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule {}
