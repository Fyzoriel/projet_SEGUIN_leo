import { Pipe, PipeTransform } from "@angular/core";

@Pipe({
  name: "phone"
})
export class PhonePipe implements PipeTransform {
  transform(value: string | undefined): string {
    if (value === undefined) {
      return "";
    }

    value = value.replace(" ", "");

    if (!value.startsWith("+33")) {
      value = "+33" + value.slice(1);
    }
    const parts = value.match(/(\+33)(\d)(\d{2})(\d{2})(\d{2})(\d{2})/);

    if (parts === null) {
      return value;
    }

    parts.shift();
    return parts.join(" ");
  }
}
