import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";

import { Observable } from "rxjs";

import { environment } from "../../environments/environment";
import { GetProductAPIType } from "../types/product.type";
import { ManufacturerAPIType } from "../types/manufacturer.type";

@Injectable({
  providedIn: "root"
})
export class ManufacturerService {
  env = environment;

  constructor(private readonly httpClient: HttpClient) {}

  public get = (): Observable<ManufacturerAPIType[]> =>
    this.httpClient.get<ManufacturerAPIType[]>(
      `${this.env.baseApi}/manufacturer`
    );

  public getProducts = (id: number): Observable<GetProductAPIType[]> => {
    return this.httpClient.get<GetProductAPIType[]>(
      `${this.env.baseApi}/manufacturer/${id}/products`
    );
  };
}
