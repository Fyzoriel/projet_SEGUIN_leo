import { Injectable } from "@angular/core";
import {
  ActivatedRouteSnapshot,
  CanActivate,
  Router,
  RouterStateSnapshot
} from "@angular/router";

import { StorageService } from "../services/storage.service";
import { Role } from "../types/role.type";

@Injectable({
  providedIn: "root"
})
export class AuthGuard implements CanActivate {
  public constructor(
    private readonly router: Router,
    private readonly storageService: StorageService
  ) {}

  public canActivate(next: ActivatedRouteSnapshot): boolean {
    if (this.storageService.isLoggedIn()) {
      if (next.data["roles"]) {
        const userRole = this.storageService.getUserRole();

        if (userRole && next.data["roles"].includes(userRole)) {
          return true;
        } else {
          void this.router.navigate(["/auth/login"]);
          return false;
        }
      } else {
        return true;
      }
    }

    void this.router.navigate(["/auth/login"]);
    return false;
  }
}
