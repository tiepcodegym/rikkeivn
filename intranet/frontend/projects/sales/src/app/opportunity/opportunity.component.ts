import { Component, OnInit } from '@angular/core';
import { NgModel, FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { AppComponent } from './../app.component';
import { OpportunityService } from './opportunity.service';
import { ValidationService } from './validation.service';
import { retry, catchError, finalize } from 'rxjs/operators';
declare var $: any;

@Component({
    interpolation: ['[[', ']]'],
    selector: 'app-opportunity',
    templateUrl: './opportunity.component.html',
    styleUrls: ['./../app.component.css']
})

export class OpportunityComponent extends AppComponent implements OnInit {

    opporForm: FormGroup;
    item: any;
    opporParams: any;
    members: any;
    OpporEvents: any;
    loading: boolean;
    exporting: boolean;
    oldStatus: any;

    constructor(
        private opporService: OpportunityService,
        private formBuilder: FormBuilder,
        private validateService: ValidationService
    ) {
        super();
        // @ts-ignore
        this.opporParams = OpporParams;
        // @ts-ignore
        this.opporTrans = OpporTrans;

        // @ts-ignore
        this.item = ITEM;
        if (!this.item) {
            this.item = {
                id: null,
                name: null,
                code: this.opporParams.itemCode,
                priority: this.opporParams.PRIORITY_NORMAL,
                status: this.opporParams.STT_OPEN,
                number_member: 0,
                number_recieved: null,
                detail: null,
                potential: null,
                prog_ids: [],
                lang: null,
                duedate: null,
                duration: null,
                sale_id: null,
                country_id: this.opporParams.COUNTRY_VN,
                location: null,
                customer_name: null,
                note: null,
            };
        }
        this.item.send_mail = 0;
        let removeStatuses = [this.opporParams.STT_FAIL, this.opporParams.STT_PASS, this.opporParams.STT_SUBMIT];
        if (removeStatuses.indexOf(this.item.status) > -1) {
            this.item.status = null;
        }
        this.oldStatus = this.item.status;

        this.members = this.opporParams.members.length > 0 ? [this.opporParams.members[0]] : [];
        if (this.members.length < 1) {
            this.members = [{
                id: null,
                role: null,
                member_exp: null,
                english_level: null,
                japanese_level: null
            }];
        }

        // @ts-ignore
        this.OpporEvents = OpporEvents;
        this.loading = false;
    }

    ngOnInit() {
        var _this = this;

        $('body').on('change', '.item-form select', function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (value instanceof Array) {
                let formatVal = [];
                for (let i = 0; i < value.length; i++) {
                    formatVal.push(parseInt(value[i].split(':')[1].trim()));
                }
                value = formatVal;
            }
            _this.itemForm.controls[name].setValue(value);
        });

        $('body').on('change', '#list_employees select', function () {
            var index = $(this).closest('.emp-item').attr('data-index');
            var name = $(this).attr('data-name');
            var value = $(this).val();
            if (value instanceof Array) {
                let formatVal = [];
                for (let i = 0; i < value.length; i++) {
                    formatVal.push(parseInt(value[i].split(':')[1].trim()));
                }
                value = formatVal;
            }
            _this.membersForm.controls[index]['controls'][name].setValue(value);
        });

        $('body').on('dp.change', '.date-picker', function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            _this.itemForm.controls[name].setValue(value);
        });

        //validate
        this.opporForm = this.formBuilder.group({
            item: this.formBuilder.group({
                id: [this.item.id],
                name: [this.item.name, [Validators.required, Validators.maxLength(255)]],
                code: [
                    this.item.code,
                    [Validators.required, Validators.maxLength(255)],
                    [this.validateService.opporCodeValidator.bind(this, _this.item.id)]
                ],
                priority: [this.item.priority],
                status: [this.item.status, [Validators.required]],
                number_member: [this.item.number_member, [
                    Validators.required,
                    Validators.min(0),
                    Validators.max(100),
                    this.validateService.greaterThan.bind(this, 'number_recieved')
                ]],
                number_recieved: [this.item.number_recieved, [Validators.min(0), this.validateService.lessThan.bind(this, 'number_member')]],
                detail: [this.item.detail],
                potential: [this.item.potential],
                prog_ids: [this.item.prog_ids],
                lang: [this.item.lang],
                duedate: [this.item.duedate, [Validators.required]],
                duration: [this.item.duration, [Validators.required]],
                sale_id: [this.item.sale_id, [Validators.required]],
                country_id: [this.item.country_id],
                location: [this.item.location],
                customer_name: [this.item.customer_name, [Validators.maxLength(255)]],
                note: [this.item.note],
                send_mail: [this.item.send_mail]
            }),
            members: this.formBuilder.array(this.initFormArrayMembers())
        });

        this.itemForm.controls['number_member'].valueChanges.subscribe((value) => {
            this.checkCloseItem();
        });
        this.itemForm.controls['number_recieved'].valueChanges.subscribe((value) => {
            this.checkCloseItem();
        });

        this.OpporEvents.init();
    }

    get membersForm(): FormArray {
        return this.opporForm.get('members') as FormArray;
    }

    get itemForm(): FormGroup {
        return this.opporForm.get('item') as FormGroup;
    }

    initFormArrayMembers() {
        let aryMembers = [];
        for (let i = 0; i < this.members.length; i++) {
            let member = this.members [i];
            aryMembers.push(this.formBuilder.group({
                id: [member.id],
                role: [member.role],
                member_exp: [member.member_exp],
                english_level: [member.english_level],
                japanese_level: [member.japanese_level],
            }));
        }
        return aryMembers;
    }

    checkCloseItem() {
        let numMember = this.itemForm.get('number_member').value;
        let numRecieved = this.itemForm.get('number_recieved').value;
        if (numMember == numRecieved) {
            this.itemForm.controls['status'].setValue(this.opporParams.STT_CLOSED);
        } else {
            this.itemForm.controls['status'].setValue(this.oldStatus);
        }
        // @ts-ignore
        $('#item_status').trigger('change');
    }

    /**
     * create or update opportunity
     */
    saveOppor(): void {
        var that = this;
        if (that.opporForm.invalid) {
            return;
        }
        that.loading = true;
        var data = this.itemForm.value;
        data.members = this.membersForm.value;
        this.opporService.save(data)
            .pipe(finalize(() => {
                that.loading = false;
            }))
            .subscribe(
                (result) => {
                    // @ts-ignore
                    that.OpporEvents.alertSuccess(result.message);
                    that.oldStatus = that.itemForm.get('status').value;
                    // @ts-ignore
                    if (typeof result.redirect != 'undefined') {
                        setTimeout(function () {
                            // @ts-ignore
                            window.location.href = result.redirect;
                        }, 2000);
                    }
                },
                that.opporService.handleError.bind(this)
            );
    }

    /**
     * export opportunity
     */
    exportOppor(event): void {
        var that = this;
        that.exporting = true;
        this.opporService.exportData()
            .pipe(finalize(() => {
                that.exporting = false;
            }))
            .subscribe(
                (response) => {
                    that.OpporEvents.exportData(response);
                },
                that.opporService.handleError.bind(this)
            );
    }

}
