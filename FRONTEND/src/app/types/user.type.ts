import { AddressAPIType, RegisterAddressAPIType } from "./address.type";

export interface UserAPIType {
  id: number;
  firstName: string;
  name: string;
  email: string;
  phone: string;
  validated: boolean;
  addresses: AddressAPIType[];
}

export interface UserRegisterType {
  firstName: string;
  name: string;
  email: string;
  phone?: string;
  passphrase: string;
  confirmPassphrase: string;
  role: "CLIENT" | "SELLER";
  addresses?: RegisterAddressAPIType[];
  manufacturer?: {
    name?: string;
    id?: number;
  };
}
