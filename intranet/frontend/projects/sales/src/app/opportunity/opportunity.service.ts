import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from  "@angular/common/http";
import { throwError } from "rxjs";
import { retry, catchError } from 'rxjs/operators';

@Injectable({providedIn: 'root'})
export class OpportunityService {
    opporParams: any;
    OpporEvents: any;

    constructor(private http: HttpClient) {
        this.http = http;
        // @ts-ignore
        this.opporParams = OpporParams;
        // @ts-ignore
        this.OpporEvents = OpporEvents;
    }

    getOppor(id) {
        return this.http.get(this.opporParams.getOpporUrl + '/' + id);
    }

    save(data = {}) {
        data['_token'] = this.opporParams.token;
        return this.http.post(this.opporParams.saveUrl, data);
    }

    exportData() {
        return this.http.post(this.opporParams.exportUrl, {_token: this.opporParams.token});
    }

    checkExistsCode(code: string, id: number) {
        let data = {
            _token: this.opporParams.token,
            field: 'code',
            value: code,
            id: id
        }
        return this.http.post(this.opporParams.checkingCodeUrl, data);
    }

    saveNoteCv(data = {}) {
        data['_token'] = this.opporParams.token;
        return this.http.post(this.opporParams.saveCvMemberUrl, data);
    }

    removeCvNote(id) {
        let options = {
            headers: new HttpHeaders({
                'Content-Type': 'application/json'
            }),
            body: {
                '_token': this.opporParams.token
            }
        };
        return this.http.delete(this.opporParams.deleteCvMemberUrl + '/' + id, options);
    }

    getListCvNotes(requestId, page = 1) {
        return this.http.get(this.opporParams.listCvNotesUrl + '/' + requestId + '?page=' + page);
    }

    handleError(error: HttpErrorResponse) {
        var message = '';
        if (error.error instanceof ErrorEvent) {
            message = error.error.message;
        }
        console.log(this);
        // @ts-ignore
        OpporEvents.errorHandle(message);
        return throwError('Error!');
    };
}
