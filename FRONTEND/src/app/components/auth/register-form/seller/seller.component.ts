import { Component, OnInit } from "@angular/core";
import { HttpErrorResponse } from "@angular/common/http";
import { FormBuilder, FormGroup, Validators } from "@angular/forms";
import { Router } from "@angular/router";

import { AuthService } from "../../../../services/auth.service";
import { ManufacturerService } from "../../../../services/manufacturer.service";
import { MustMatchValidator } from "../../../../validators/mustMatch.validator";
import { UserRegisterType } from "../../../../types/user.type";
import { Observable } from "rxjs";
import { ManufacturerAPIType } from "../../../../types/manufacturer.type";

interface ExistingManufacturerOption {
  id: number;
  name: string;
}
interface NewManufacturerOption {
  name: string;
  new: boolean;
}

type ManufacturerOption = ExistingManufacturerOption | NewManufacturerOption;

@Component({
  selector: "app-seller",
  templateUrl: "./seller.component.html",
  styleUrls: ["./seller.component.css"]
})
export class SellerComponent implements OnInit {
  public userDataForm!: FormGroup;
  public registerError: string | undefined;
  public manufacturers$!: Observable<ManufacturerAPIType[]>;

  constructor(
    private readonly router: Router,
    private readonly formBuilder: FormBuilder,
    private readonly authService: AuthService,
    private readonly manufacturerService: ManufacturerService
  ) {}

  ngOnInit(): void {
    this.manufacturers$ = this.manufacturerService.get();

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
      manufacturer: [
        "",
        {
          validators: Validators.required
        }
      ],
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

  addManufacturer = (name: string) => {
    return { name, new: true };
  };

  onSubmit = () => {
    if (!this.userDataForm.valid) {
      return;
    }

    const manufacturer: ManufacturerOption =
      this.userDataForm.get("manufacturer")?.value;

    let manufacturerToSend;

    if ("id" in manufacturer) {
      manufacturerToSend = {
        id: manufacturer.id
      };
    } else if ("new" in manufacturer) {
      manufacturerToSend = {
        name: manufacturer.name
      };
    }

    const user: UserRegisterType = {
      firstName: this.userDataForm.get("firstName")?.value,
      name: this.userDataForm.get("name")?.value,
      email: this.userDataForm.get("email")?.value,
      passphrase: this.userDataForm.get("passphrase")?.value,
      confirmPassphrase: this.userDataForm.get("confirmPassphrase")?.value,
      manufacturer: {
        ...manufacturerToSend
      },
      role: "SELLER"
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
