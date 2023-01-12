import { Component } from "@angular/core";
import { Router } from "@angular/router";
import { Select } from "@ngxs/store";

import { Observable } from "rxjs";

import { StorageService } from "../../../services/storage.service";
import { AuthService } from "../../../services/auth.service";
import { CartState } from "../../../states/cart.state";

@Component({
  selector: "app-header",
  templateUrl: "./header.component.html",
  styleUrls: ["./header.component.css"]
})
export class HeaderComponent {
  @Select(CartState.count) cartCount$!: Observable<number>;

  public constructor(
    private readonly router: Router,
    public readonly storageService: StorageService,
    private readonly authService: AuthService
  ) {}

  public logout = ($event: MouseEvent) => {
    $event.preventDefault();
    this.authService.logout();
    void this.router.navigate(["/auth/login"]);
  };
}
