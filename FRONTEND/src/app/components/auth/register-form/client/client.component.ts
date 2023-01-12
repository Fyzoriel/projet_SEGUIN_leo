import { Component, OnInit } from "@angular/core";
import { HttpErrorResponse } from "@angular/common/http";
import { Router } from "@angular/router";
import { FormArray, FormBuilder, FormGroup, Validators } from "@angular/forms";

import { AuthService } from "../../../../services/auth.service";
import { UserRegisterType } from "../../../../types/user.type";
import { MustMatchValidator } from "../../../../validators/mustMatch.validator";
import { MustBeOneOfValidator } from "../../../../validators/mustBeOneOf.validator";
import { RegisterAddressAPIType } from "../../../../types/address.type";

@Component({
  selector: "app-client",
  templateUrl: "./client.component.html",
  styleUrls: ["./client.component.css"]
})
export class ClientComponent implements OnInit {
  public userDataForm!: FormGroup;
  public addresses!: FormArray;
  public registerError: string | undefined;

  constructor(
    private readonly router: Router,
    private readonly formBuilder: FormBuilder,
    private readonly authService: AuthService
  ) {}

  ngOnInit(): void {
    const minPassphraseLength = 8;

    this.userDataForm = this.formBuilder.group({
      firstName: [
        "",
        {
          validators: Validators.required
        }
      ],
      name: [
        "",
        {
          validators: Validators.required
        }
      ],
      email: [
        "",
        {
          validators: [Validators.required, Validators.email]
        }
      ],
      phone: [
        "",
        {
          validators: [
            Validators.required,
            Validators.pattern(
              "(0|\\+33 ?)[1-9]([-. ]?[0-9]{2} ?){3}([-. ]?[0-9]{2})"
            )
          ]
        }
      ],
      addresses: this.formBuilder.array([this.createAddress()]),
      passphrase: [
        "",
        {
          validators: [
            Validators.required,
            Validators.minLength(minPassphraseLength),
            MustMatchValidator.mustMatch("confirmPassphrase", true)
          ]
        }
      ],
      confirmPassphrase: [
        "",
        {
          validators: [
            Validators.required,
            Validators.minLength(minPassphraseLength),
            MustMatchValidator.mustMatch("passphrase")
          ]
        }
      ]
    });
  }

  createAddress = (): FormGroup => {
    return this.formBuilder.group({
      name: [
        "",
        {
          validators: Validators.required
        }
      ],
      type: [
        "BILLING",
        {
          validators: [
            Validators.required,
            MustBeOneOfValidator.valueInTypeValidator([
              "BILLING",
              "DELIVERY"
            ] as const)
          ]
        }
      ],
      street: [
        "",
        {
          validators: Validators.required
        }
      ],
      streetNumber: [
        "",
        {
          validators: Validators.required
        }
      ],
      zip: [
        "",
        {
          validators: Validators.required
        }
      ],
      city: [
        "",
        {
          validators: Validators.required
        }
      ],
      state: [
        "",
        {
          validators: Validators.required
        }
      ]
    });
  };

  addAddress = () => {
    const addresses = this.userDataForm.get("addresses") as FormArray;
    addresses.push(this.createAddress());
  };

  deleteAddress = (index: number) => {
    const addresses = this.userDataForm.get("addresses") as FormArray;
    addresses.removeAt(index);
  };

  getAddressesControls = () => {
    const addresses = this.userDataForm.get("addresses") as FormArray;
    return addresses.controls;
  };

  getCountAddresses = () => {
    const addresses = this.userDataForm.get("addresses") as FormArray;
    return addresses.length;
  };

  onSubmit = () => {
    if (!this.userDataForm.valid) {
      return;
    }

    const addresses: RegisterAddressAPIType[] = [];
    for (const address of this.getAddressesControls()) {
      addresses.push({
        name: address.get("name")?.value,
        type: address.get("type")?.value,
        street: address.get("street")?.value,
        streetNumber: address.get("streetNumber")?.value,
        zip: address.get("zip")?.value,
        city: address.get("city")?.value,
        state: address.get("state")?.value
      });
    }

    const user: UserRegisterType = {
      firstName: this.userDataForm.get("firstName")?.value,
      name: this.userDataForm.get("name")?.value,
      email: this.userDataForm.get("email")?.value,
      phone: this.userDataForm.get("phone")?.value,
      addresses,
      passphrase: this.userDataForm.get("passphrase")?.value,
      confirmPassphrase: this.userDataForm.get("confirmPassphrase")?.value,

      role: "CLIENT"
    };

    this.authService.register(user).subscribe({
      next: () => {
        this.registerError = undefined;

        void this.router.navigate(["/login"]);
      },
      error: err => {
        this.userDataForm.patchValue({
          passphrase: "",
          confirmPassphrase: ""
        });
        this.userDataForm.markAsUntouched();
        this.userDataForm.markAsPristine();

        if (err instanceof HttpErrorResponse) {
          this.registerError = err.error.details;
        } else {
          this.registerError = "Unknown error";
        }
      }
    });
  };
}
