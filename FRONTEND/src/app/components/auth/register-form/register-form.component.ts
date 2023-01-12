import { Component, HostBinding } from "@angular/core";

@Component({
  selector: "app-register-form",
  templateUrl: "./register-form.component.html",
  styleUrls: ["./register-form.component.css"]
})
export class RegisterFormComponent {
  @HostBinding("class.app_content_centered")
  private readonly bindHack = true;

  public isClientRegister = true;

  onClick() {
    this.isClientRegister = !this.isClientRegister;
  }
}
