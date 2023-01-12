import { Directive, ElementRef, HostListener, Input } from "@angular/core";

@Directive({
  selector: "[appDataControl]"
})

// This directive is used to control the input of data in the client input form.
// Example: <input type="text" appDataControl="^[-a-A-Z]*$" />
export class DataControlDirective {
  @Input() appDataControl: string = "";

  constructor(private readonly element: ElementRef) {}

  @HostListener("blur") onBlur() {
    const regex = new RegExp(this.appDataControl);
    if (regex.test(this.element.nativeElement.value)) {
      this.element.nativeElement.style.backgroundColor = "green";
    } else {
      this.element.nativeElement.style.backgroundColor = "red";
    }
  }
}
