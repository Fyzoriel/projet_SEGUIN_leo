import { Component, HostBinding, OnInit } from "@angular/core";
import { ActivatedRoute, Router } from "@angular/router";

import { Observable, of } from "rxjs";

import { UserService } from "../../../services/user.service";
import { UserAPIType } from "../../../types/user.type";
import { HTTP_STATUS } from "../../../types/httpStatus.type";

@Component({
  selector: "app-display-client-data",
  templateUrl: "./profile.component.html",
  styleUrls: ["./profile.component.css"]
})
export class ProfileComponent implements OnInit {
  @HostBinding("class.app_content_centered")
  public userInformation$!: Observable<UserAPIType | null>;

  public constructor(
    private readonly router: Router,
    private readonly route: ActivatedRoute,
    private readonly userService: UserService
  ) {}

  public ngOnInit(): void {
    const id: number = Number(this.route.snapshot.paramMap.get("id"));
    this.userService.get(id).subscribe({
      next: response => {
        if (response.status === HTTP_STATUS.OK && response.data !== null) {
          this.userInformation$ = of(response.data);
        }
      },
      error: () => {
        void this.router.navigate(["/"]);
      }
    });
  }
}
