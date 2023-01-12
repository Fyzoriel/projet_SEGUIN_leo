import { NgModule } from "@angular/core";
import { registerLocaleData } from "@angular/common";
import { HTTP_INTERCEPTORS, HttpClientModule } from "@angular/common/http";
import { BrowserModule } from "@angular/platform-browser";
import { Router, RouterOutlet } from "@angular/router";

import { NgxsModule } from "@ngxs/store";

import localeFr from "@angular/common/locales/fr";

import { AppRoutingModule } from "./app-routing.module";

import { ProductService } from "./services/product.service";
import { StorageService } from "./services/storage.service";

import { AppComponent } from "./app.component";
import { HeaderComponent } from "./components/shared/header/header.component";
import { FooterComponent } from "./components/shared/footer/footer.component";

import { DataControlDirective } from "./directives/data-control.directive";

import { CartState } from "./states/cart.state";

import { ApiHttpInterceptorInterceptor } from "./interceptor/api-http-interceptor.interceptor";

registerLocaleData(localeFr);

@NgModule({
  declarations: [
    AppComponent,
    HeaderComponent,
    FooterComponent,
    DataControlDirective
  ],
  imports: [
    AppRoutingModule,
    BrowserModule,
    HttpClientModule,
    RouterOutlet,
    NgxsModule.forRoot([CartState])
  ],
  providers: [
    ProductService,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: ApiHttpInterceptorInterceptor,
      multi: true,
      deps: [Router, StorageService]
    }
  ],
  bootstrap: [AppComponent]
})
export class AppModule {}
