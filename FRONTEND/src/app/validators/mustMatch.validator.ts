import { AbstractControl, ValidationErrors, ValidatorFn } from "@angular/forms";

export class MustMatchValidator {
  static mustMatch = (matchTo: string, reverse?: boolean): ValidatorFn => {
    return (control: AbstractControl): ValidationErrors | null => {
      if (control.parent && reverse) {
        const controlExist = (control.parent.controls as any)[matchTo];
        if (controlExist) {
          const c = controlExist as AbstractControl;
          c.updateValueAndValidity();
        }
        return null;
      }
      return !!control.parent &&
        !!control.parent.value &&
        control.value === (control.parent.controls as any)[matchTo].value
        ? null
        : { matching: true };
    };
  };
}
