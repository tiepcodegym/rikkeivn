import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { OpportunityComponent } from './opportunity/opportunity.component';
import { OpportunityDetailComponent } from './opportunity-detail/opportunity-detail.component';

const routes: Routes = [
    { path: 'edit', component: OpportunityComponent },
    { path: 'edit/:id', component: OpportunityComponent },
    { path: 'view', component: OpportunityDetailComponent },
    { path: 'view/:id', component: OpportunityDetailComponent }
];

@NgModule({
    declarations: [],
    imports: [ RouterModule.forRoot(routes) ],
    exports: [ RouterModule ]
})

export class AppRoutingModule { }
