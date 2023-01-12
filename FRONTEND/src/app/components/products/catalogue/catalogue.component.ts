import { Component, OnInit } from "@angular/core";

import {
  debounceTime,
  distinctUntilChanged,
  map,
  Observable,
  of,
  Subject,
  switchMap
} from "rxjs";

import { ProductService } from "../../../services/product.service";
import { GetProductAPIType } from "../../../types/product.type";
import { ModelsService } from "../../../services/models.service";
import { ModelAPIType } from "../../../types/model.type";

@Component({
  selector: "app-products",
  templateUrl: "./catalogue.component.html",
  styleUrls: ["./catalogue.component.css"]
})
export class CatalogueComponent implements OnInit {
  public products$!: Observable<GetProductAPIType[]>;
  public models$!: Observable<ModelAPIType[]>;

  public nameFilterChanged$ = new Subject<string>();
  public modelsFilterChanged$ = new Subject<string[]>();

  public modelsFilter: ModelAPIType[] = [];
  public nameFilter: string = "";

  public constructor(
    private readonly modelService: ModelsService,
    private readonly productService: ProductService
  ) {}

  public ngOnInit(): void {
    const debounceTimeMs = 300;
    this.nameFilterChanged$
      .pipe(debounceTime(debounceTimeMs), distinctUntilChanged())
      .subscribe(() => {
        this.fetchProducts();
      });

    this.modelsFilterChanged$
      .pipe(debounceTime(debounceTimeMs), distinctUntilChanged())
      .subscribe(() => {
        this.fetchProducts();
      });

    this.products$ = this.productService.get();
    this.models$ = this.modelService.get();
  }

  public fetchProducts = () => {
    let filter;
    if (this.nameFilter.length || this.modelsFilter.length) {
      filter = {
        name: this.nameFilter,
        ids: this.modelsFilter.map(model => Number(model.id))
      };
    }

    this.productService
      .get(filter)
      .pipe(
        switchMap(updatedProducts => {
          return this.products$.pipe(
            map(products => {
              for (const updateProduct of updatedProducts) {
                const index = products.findIndex(
                  product => product.id === updateProduct.id
                );
                products.splice(index, 1, updateProduct);
              }
              return products;
            })
          );
        })
      )
      .subscribe(updatedProducts => {
        this.products$ = of(updatedProducts);
      });
  };
  trackByProductId = (index: number, product: GetProductAPIType): number => {
    return product.id;
  };
}
