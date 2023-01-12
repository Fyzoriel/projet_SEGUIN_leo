import { Component, HostBinding, OnInit } from "@angular/core";
import { HttpErrorResponse, HttpResponse } from "@angular/common/http";
import { FormBuilder, FormGroup, Validators } from "@angular/forms";
import { Router } from "@angular/router";

import { AuthService } from "../../../services/auth.service";
import { StorageService } from "../../../services/storage.service";

@Component({
  selector: "app-login-form",
  templateUrl: "./login-form.component.html",
  styleUrls: ["./login-form.component.css"]
})
export class LoginFormComponent implements OnInit {
  @HostBinding("class.app_content_centered")
  public userDataForm!: FormGroup;

  public loginError: string | undefined;

  public constructor(
    private readonly router: Router,
    private readonly formBuilder: FormBuilder,
    private readonly authService: AuthService,
    private readonly storageService: StorageService
  ) {}

  public ngOnInit(): void {
    const minPassphraseLength = 8;

    this.userDataForm = this.formBuilder.group({
      email: [
        "",
        {
          validators: [Validators.required, Validators.email]
        }
      ],
      passphrase: [
        "",
        {
          validators: [
            Validators.required,
            Validators.minLength(minPassphraseLength)
          ]
        }
      ]
    });
  }

  public onSubmit = (): void => {
    if (!this.userDataForm.valid) {
      return;
    }

    const { email, passphrase } = this.userDataForm.value;

    this.authService.login(email, passphrase).subscribe({
      next: response => {
        if (response instanceof HttpResponse) {
          const token = response.headers.get("authorization");

          if (token != null) {
            const tokenWithoutBearer = token.replace("Bearer ", "");
            this.storageService.saveUserToken(tokenWithoutBearer);
            void this.router.navigate(["/"]);
          }
        }

        this.loginError = undefined;
        void this.router.navigate(["/"]);
      },
      error: err => {
        this.userDataForm.patchValue({
          passphrase: ""
        });
        this.userDataForm.markAsUntouched();
        this.userDataForm.markAsPristine();

        if (err instanceof HttpErrorResponse) {
          this.loginError = err.error.details;
        } else {
          this.loginError = "Unknown error";
        }
      }
    });
  };
}
