import { Injectable } from "@angular/core";
import { HttpClient, HttpEvent, HttpResponse } from "@angular/common/http";
import { Observable } from "rxjs";

import { environment } from "../../environments/environment";
import { StorageService } from "./storage.service";
import { UserRegisterType } from "../types/user.type";

@Injectable({
  providedIn: "root"
})
export class AuthService {
  env = environment;

  public constructor(
    private readonly httpClient: HttpClient,
    private readonly storageService: StorageService
  ) {}

  public register = (user: UserRegisterType): Observable<any> => {
    return this.httpClient.post<UserRegisterType[]>(
      `${this.env.baseApi}/auth/register`,
      { ...user }
    );
  };

  public login = (
    email: string,
    passphrase: string
  ): Observable<HttpEvent<unknown>> => {
    return this.httpClient.post<HttpEvent<unknown>>(
      `${this.env.baseApi}/auth/login`,
      {
        email,
        passphrase
      },
      { headers: { test: "header" }, observe: "response" as "body" }
    );
  };

  public logout = (): Observable<any> => {
    this.storageService.clearUserToken();

    return new Observable(observer => {
      observer.next();
    });
  };
}
