import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";

import { map, Observable } from "rxjs";

import { environment } from "../../environments/environment";
import { UserAPIType } from "../types/user.type";
import { Response } from "../types/response.type";

@Injectable({
  providedIn: "root"
})
export class UserService {
  env = environment;

  public constructor(private readonly httpClient: HttpClient) {}

  public get = (id: number): Observable<Response<UserAPIType>> => {
    return this.httpClient
      .get<UserAPIType>(`${this.env.baseApi}/user/${id}`, {
        observe: "response"
      })
      .pipe(
        map(response => {
          return {
            data: response.body,
            status: response.status
          };
        })
      );
  };
}
