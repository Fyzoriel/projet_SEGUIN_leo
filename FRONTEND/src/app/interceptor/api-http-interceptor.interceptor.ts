import { Injectable } from "@angular/core";
import {
  HttpRequest,
  HttpHandler,
  HttpEvent,
  HttpInterceptor,
  HttpErrorResponse
} from "@angular/common/http";
import { Router } from "@angular/router";

import { Observable, tap } from "rxjs";

import { StorageService } from "../services/storage.service";
import { HTTP_STATUS } from "../types/httpStatus.type";

@Injectable()
export class ApiHttpInterceptorInterceptor implements HttpInterceptor {
  constructor(
    private readonly route: Router,
    private readonly storageService: StorageService
  ) {}

  intercept(
    request: HttpRequest<unknown>,
    next: HttpHandler
  ): Observable<HttpEvent<unknown>> {
    const token = this.storageService.getUserToken();

    if (token != null) {
      request = request.clone({
        setHeaders: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`
        }
      });
    }

    return next.handle(request).pipe(
      tap({
        error: err => {
          if (err instanceof HttpErrorResponse) {
            if (err.status === HTTP_STATUS.UNAUTHORIZED) {
              void this.route.navigate(["/auth/login"]);
            }
          }
        }
      })
    );
  }
}
