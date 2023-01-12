import { Injectable } from "@angular/core";
import { environment } from "../../environments/environment";
import { HttpClient } from "@angular/common/http";
import { Observable } from "rxjs";
import { ModelAPIType } from "../types/model.type";

@Injectable({
  providedIn: "root"
})
export class ModelsService {
  env = environment;

  constructor(private readonly httpClient: HttpClient) {}

  public get = (): Observable<ModelAPIType[]> => {
    return this.httpClient.get<ModelAPIType[]>(`${this.env.baseApi}/model`);
  };
}
