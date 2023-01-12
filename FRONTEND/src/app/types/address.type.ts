export interface AddressAPIType {
  id: number;
  state: string;
  zip: string;
  city: string;
  street: string;
  streetNumber: string;
  type: "DELIVERY" | "BILLING";
  name: string;
}

export interface RegisterAddressAPIType extends Omit<AddressAPIType, "id"> {}
