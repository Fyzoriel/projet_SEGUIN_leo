import { GetProductAPIType } from "../types/product.type";

export class AddProduct {
  static readonly type = "CART|Add";
  constructor(public payload: GetProductAPIType) {}
}

export class DeleteProduct {
  static readonly type = "CART|Delete";
  constructor(public payload: GetProductAPIType) {}
}
