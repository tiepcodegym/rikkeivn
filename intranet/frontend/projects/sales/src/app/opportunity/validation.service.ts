import { Injectable } from '@angular/core';
import { AbstractControl } from '@angular/forms';
import { throwError } from "rxjs";
import { retry, catchError, map } from 'rxjs/operators';
import { OpportunityService } from './opportunity.service';

@Injectable({providedIn: 'root'})
export class ValidationService {

    OpporParams: any;

    constructor(private opporService: OpportunityService) {
        // @ts-ignore
        this.OpporParams = OpporParams;
    }

    opporCodeValidator(id: number, control: AbstractControl) {
        var that = this;
        return this.opporService.checkExistsCode(control.value, id).pipe(
            map(result => {
                return result ? null : {codeTaken: true};
            }),
            catchError(that.opporService.handleError.bind(this))
        );
    }

    greaterThan(fieldCompare: string, control: AbstractControl) {
        var opporControl = control.parent;
        var compareValue = null;
        if (typeof opporControl != 'undefined') {
            compareValue = opporControl.get(fieldCompare).value;
        }
        if (compareValue === null || compareValue <= control.value) {
            return null;
        }
        return {greaterThan: true};
    }

    lessThan(fieldCompare: string, control: AbstractControl) {
        var opporControl = control.parent;
        var compareValue = null;
        if (typeof opporControl != 'undefined') {
            compareValue = opporControl.get(fieldCompare).value;
        }
        if (compareValue === null || compareValue >= control.value) {
            return null;
        }
        return {lessThan: true};
    }

}
