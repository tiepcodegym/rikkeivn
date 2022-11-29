import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';

import { OpportunityService } from './opportunity/opportunity.service';
import { ValidationService } from './opportunity/validation.service';

import { AppComponent } from './app.component';
import { OpportunityComponent } from './opportunity/opportunity.component';
import { OpportunityDetailComponent } from './opportunity-detail/opportunity-detail.component';

import { AppRoutingModule } from './app-routing.module';

@NgModule({
    declarations: [
        AppComponent,
        OpportunityComponent,
        OpportunityDetailComponent
    ],
    imports: [
        BrowserModule,
        FormsModule,
        HttpClientModule,
        ReactiveFormsModule,
        AppRoutingModule
    ],
    providers: [OpportunityService, ValidationService],
    bootstrap: [AppComponent]
})

export class AppModule { }
