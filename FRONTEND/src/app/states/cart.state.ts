import { Injectable } from "@angular/core";
import { Action, Selector, State, StateContext } from "@ngxs/store";

import { CartStateModel } from "./cart.state.model";
import { GetProductAPIType } from "../types/product.type";
import { AddProduct, DeleteProduct } from "../actions/cart.action";

@State<CartStateModel>({
  name: "cart",
  defaults: {
    products: []
  }
})
@Injectable()
export class CartState {
  @Selector()
  public static products(state: CartStateModel): GetProductAPIType[] {
    return state.products;
  }

  @Selector()
  public static count(state: CartStateModel): number {
    return state.products.length;
  }

  @Selector()
  public static totalPrice(state: CartStateModel): number {
    return state.products.reduce((acc, product) => acc + product.price, 0);
  }

  @Action(AddProduct)
  public addProduct(
    { getState, patchState }: StateContext<CartStateModel>,
    { payload }: AddProduct
  ): void {
    const state = getState();
    patchState({
      products: [...state.products, payload]
    });
  }

  @Action(DeleteProduct)
  public deleteProduct(
    { getState, patchState }: StateContext<CartStateModel>,
    { payload }: DeleteProduct
  ): void {
    const state = getState();

    const productIndex = state.products.findIndex(
      product => product.id === payload.id
    );

    patchState({
      products: state.products.filter((_, index) => index !== productIndex)
    });
  }
}
