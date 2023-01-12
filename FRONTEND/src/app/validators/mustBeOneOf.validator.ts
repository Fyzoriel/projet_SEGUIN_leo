import { AbstractControl, ValidatorFn } from "@angular/forms";

export class MustBeOneOfValidator {
  static valueInTypeValidator<T>(type: T): ValidatorFn {
    return (control: AbstractControl) => {
      if (!Object.values(type).includes(control.value)) {
        return { valueInType: true };
      }
      return null;
    };
  }
}
