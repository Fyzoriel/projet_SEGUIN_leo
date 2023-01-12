import { Component, OnInit } from "@angular/core";
import { ActivatedRoute } from "@angular/router";

import { ManufacturerService } from "../../../services/manufacturer.service";
import { map, Observable, of, switchMap } from "rxjs";
import { GetProductAPIType } from "../../../types/product.type";
import { ProductService } from "../../../services/product.service";

@Component({
  selector: "app-products",
  templateUrl: "./products.component.html",
  styleUrls: ["./products.component.css"]
})
export class ProductsComponent implements OnInit {
  public products$!: Observable<GetProductAPIType[]>;
  constructor(
    private readonly route: ActivatedRoute,
    private readonly manufacturerService: ManufacturerService,
    private readonly productService: ProductService
  ) {}

  ngOnInit(): void {
    const id: number = Number(this.route.snapshot.paramMap.get("id"));

    this.products$ = this.manufacturerService.getProducts(id);
  }

  onClick = (id: number) => {
    console.log(id);
    this.productService
      .toggle(id)
      .pipe(
        switchMap(updatedProduct => {
          return this.products$.pipe(
            map(products => {
              const index = products.findIndex(
                product => product.id === updatedProduct.id
              );
              products.splice(index, 1, updatedProduct);
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
