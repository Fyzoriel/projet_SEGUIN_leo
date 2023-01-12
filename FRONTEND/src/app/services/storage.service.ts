import { Injectable } from "@angular/core";
import jwt_decode from "jwt-decode";
import { Role } from "../types/role.type";

@Injectable({
  providedIn: "root"
})
export class StorageService {
  private currentUserToken: string | undefined;

  // front-end
  public clearUserToken = (): void => {
    this.currentUserToken = undefined;
  };

  public saveUserToken = (currentUserToken: string): void => {
    this.currentUserToken = currentUserToken;
  };

  public getUserToken = (): string | null => {
    return this.currentUserToken ?? null;
  };

  public isLoggedIn = (): boolean => {
    return this.currentUserToken !== undefined;
  };

  public getUserRole(): Role | null {
    if (!this.isLoggedIn()) {
      return null;
    }

    const decodedToken = jwt_decode(this.currentUserToken as string) as {
      role: string;
    };
    return decodedToken.role as Role;
  }

  public hasRole = (role: Role): boolean => {
    return this.getUserRole() === role;
  };

  public getUserId(): number | null {
    if (!this.isLoggedIn()) {
      return null;
    }

    const decodedToken = jwt_decode(this.currentUserToken as string) as {
      userid: string;
    };

    return Number(decodedToken.userid);
  }

  public getManufacturerId(): number | null {
    if (!this.isLoggedIn()) {
      return null;
    }

    const decodedToken = jwt_decode(this.currentUserToken as string) as {
      manufacturerId: string;
    };

    return Number(decodedToken.manufacturerId);
  }
}
