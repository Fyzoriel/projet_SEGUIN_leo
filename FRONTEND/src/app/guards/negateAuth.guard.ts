import { Injectable } from "@angular/core";
import { CanActivate, Router } from "@angular/router";

import { StorageService } from "../services/storage.service";

@Injectable({
  providedIn: "root"
})
export class NegateAuthGuard implements CanActivate {
  public constructor(
    private readonly router: Router,
    private readonly storageService: StorageService
  ) {}

  public canActivate(): boolean {
    if (!this.storageService.isLoggedIn()) {
      return true;
    }
    void this.router.navigate(["/"]);
    return false;
  }
}
