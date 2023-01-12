import { Component, HostBinding } from "@angular/core";
import { Observable } from "rxjs";

import { GetProductAPIType } from "../../types/product.type";
import { Select, Store } from "@ngxs/store";
import { CartState } from "../../states/cart.state";
import { DeleteProduct } from "../../actions/cart.action";

@Component({
  selector: "app-cart",
  templateUrl: "./cart.component.html",
  styleUrls: ["./cart.component.css"]
})
export class CartComponent {
  @HostBinding("class.app_content_centered")
  @Select(CartState.products)
  products$!: Observable<GetProductAPIType[]>;

  @Select(CartState.count) cartCount$!: Observable<number>;
  @Select(CartState.totalPrice) totalPrice$!: Observable<number>;

  constructor(private readonly store: Store) {}

  public removeFromCart = (product: GetProductAPIType): void => {
    this.store.dispatch(new DeleteProduct(product));
  };
}
