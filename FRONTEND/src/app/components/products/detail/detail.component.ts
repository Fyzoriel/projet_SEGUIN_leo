import { Component, HostBinding, OnInit } from "@angular/core";
import { ActivatedRoute } from "@angular/router";

import { ProductService } from "../../../services/product.service";
import { GetProductAPIType } from "../../../types/product.type";
import { Store } from "@ngxs/store";
import { AddProduct } from "../../../actions/cart.action";
import { StorageService } from "../../../services/storage.service";

@Component({
  selector: "app-detail",
  templateUrl: "./detail.component.html",
  styleUrls: ["./detail.component.css"]
})
export class DetailComponent implements OnInit {
  @HostBinding("class.app_content_centered_margin")
  public product: GetProductAPIType | undefined;
  public imageSource!: string;

  constructor(
    private readonly route: ActivatedRoute,
    private readonly productService: ProductService,
    private readonly store: Store,
    readonly storageService: StorageService
  ) {}

  ngOnInit(): void {
    const id: number = Number(this.route.snapshot.paramMap.get("id"));

    this.productService.get({ ids: [id] }).subscribe(products => {
      this.product = products.find(product => product.id === id);

      this.imageSource = this.product?.images ? this.product.images[0].url : "";
    });
  }

  public addToCart = (): void => {
    if (this.product) {
      this.store.dispatch(new AddProduct(this.product));
    }
  };

  public selectImage = (image: string): void => {
    this.imageSource = image;
  };
}
