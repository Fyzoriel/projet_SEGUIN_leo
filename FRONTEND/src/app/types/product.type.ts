import { TypeAPIType } from "./type.type";
import { ManufacturerAPIType } from "./manufacturer.type";
import { ImageAPIType } from "./image.type";
import { ModelAPIType } from "./model.type";

export interface GetProductAPIType {
  id: number;
  name: string;
  price: number;
  height: number;
  length: number;
  speed: number;
  capacity: number;
  enable: boolean;
  manufacturer: ManufacturerAPIType;
  type: TypeAPIType;
  images: ImageAPIType[];
  models: ModelAPIType[];
}
