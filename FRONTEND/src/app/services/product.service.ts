import { Injectable } from "@angular/core";
import { HttpClient } from "@angular/common/http";

import { Observable } from "rxjs";

import { environment } from "src/environments/environment";
import { GetProductAPIType } from "../types/product.type";

@Injectable({
  providedIn: "root"
})
export class ProductService {
  env = environment;

  public constructor(private readonly httpClient: HttpClient) {}

  public get = (filter?: FilterType): Observable<GetProductAPIType[]> => {
    let queryParams: string | undefined;

    if (filter) {
      if (filter.name) {
        queryParams = `&name=${filter.name}`;
      }

      if (filter.ids) {
        if (!queryParams) {
          queryParams = "";
        }

        for (const id of filter.ids) {
          queryParams += `&models[]=${id}`;
        }
      }
    }

    queryParams = queryParams ? `?${queryParams.slice(1)}` : "";

    return this.httpClient.get<GetProductAPIType[]>(
      `${this.env.baseApi}/product${queryParams}`
    );
  };

  public toggle = (id: number) => {
    console.log(id);
    return this.httpClient.patch<GetProductAPIType>(
      `${this.env.baseApi}/product/${id}/toggle`,
      {}
    );
  };
}

interface FilterType {
  name?: string;
  ids?: number[];
}
