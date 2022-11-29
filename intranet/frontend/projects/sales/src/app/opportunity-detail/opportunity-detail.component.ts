import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { NgModel, FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { AppComponent } from './../app.component';
import { OpportunityService } from './../opportunity/opportunity.service';
import { ValidationService } from './../opportunity/validation.service';
import { retry, catchError, finalize } from 'rxjs/operators';
declare var $: any;

@Component({
    interpolation: ['[[', ']]'],
    selector: 'app-opportunity-detail',
    templateUrl: './opportunity-detail.component.html',
    styleUrls: ['./../app.component.css']
})
export class OpportunityDetailComponent extends AppComponent implements OnInit {

    opporForm: FormGroup;
    item: any;
    members: any;
    opporParams: any;
    opporEvents: any;
    opporTrans: any;
    loading: boolean;
    oldStatus: any;

    cvMembers: any;
    cvsForm: FormGroup;
    cvsEditForm: FormGroup;
    cvNotes: any;
    cvEditNote: any;
    editLoading: boolean;
    commentLoading: boolean;
    cvLoading: boolean;
    opporSubmited: boolean;

    constructor(
        private opporService: OpportunityService,
        private validateService: ValidationService,
        private formBuilder: FormBuilder,
        private route: ActivatedRoute
    ) {
        super();
        // @ts-ignore
        this.opporParams = OpporParams;
        // @ts-ignore
        this.opporEvents = OpporEvents;
        // @ts-ignore
        this.opporTrans = OpporTrans;
        this.loading = false;
        this.opporSubmited = false;

        // @ts-ignore
        this.item = ITEM;

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
        this.item.send_mail = 1;
        this.item.permiss_edit = this.opporParams.permissEdit;
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
    }

    ngOnInit() {
        /*let id = this.route.snapshot.paramMap.get('id');*/

        var that = this;

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
            that.itemForm.controls[name].setValue(value);
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
            that.membersForm.controls[index]['controls'][name].setValue(value);
        });

        $('body').on('dp.change', '.date-picker', function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            that.itemForm.controls[name].setValue(value);
        });

        //validate
        let itemDisabled = !this.item.permiss_edit;
        this.opporForm = this.formBuilder.group({
            item: this.formBuilder.group({
                id: [this.item.id],
                name: [
                    {value: this.item.name, disabled: itemDisabled},
                    [Validators.required, Validators.maxLength(255)]
                ],
                code: [
                    {value: this.item.code, disabled: itemDisabled},
                    [Validators.required, Validators.maxLength(255)],
                    [this.validateService.opporCodeValidator.bind(this, that.item.id)]
                ],
                priority: [
                    {value: this.item.priority, disabled: itemDisabled},
                    [Validators.required]
                ],
                status: [
                    {value: this.item.status, disabled: itemDisabled},
                    [Validators.required]
                ],
                number_member: [{value: this.item.number_member, disabled: itemDisabled}, [
                    Validators.required,
                    Validators.min(0),
                    Validators.max(100),
                    this.validateService.greaterThan.bind(this, 'number_recieved')
                ]],
                number_recieved: [
                    {value: this.item.number_recieved, disabled: itemDisabled},
                    [Validators.min(0), this.validateService.lessThan.bind(this, 'number_member')]
                ],
                detail: [{value: this.item.detail, disabled: itemDisabled}],
                potential: [{value: this.item.potential, disabled: itemDisabled}],
                prog_ids: [{value: this.item.prog_ids, disabled: itemDisabled}],
                lang: [{value: this.item.lang, disabled: itemDisabled}],
                duedate: [
                    {value: this.item.duedate, disabled: itemDisabled},
                    [Validators.required]
                ],
                duration: [
                    {value: this.item.duration, disabled: itemDisabled},
                    [Validators.required]
                ],
                sale_id: [
                    {value: this.item.sale_id, disabled: itemDisabled},
                    [Validators.required]
                ],
                country_id: [{value: this.item.country_id, disabled: itemDisabled}],
                location: [
                    {value: this.item.location, disabled: itemDisabled},
                    [Validators.required]
                ],
                customer_name: [
                    {value: this.item.customer_name, disabled: itemDisabled},
                    [Validators.maxLength(255)]
                ],
                curator: [
                    {value: this.item.curator, disabled: itemDisabled},
                    [Validators.maxLength(255)]
                ],
                curator_email: [
                    {value: this.item.curator_email, disabled: itemDisabled},
                    [Validators.maxLength(255)]
                ],
                send_mail: [{value: this.item.send_mail, disabled: itemDisabled}]
            }),
            members: this.formBuilder.array(this.initFormArrayMembers())
        });

        this.itemForm.controls['number_member'].valueChanges.subscribe((value) => {
            this.checkCloseItem();
        });
        this.itemForm.controls['number_recieved'].valueChanges.subscribe((value) => {
            this.checkCloseItem();
        });

        this.loadCvNotes(1);

        this.cvsForm = this.formBuilder.group({
            note: [null, [Validators.required]]
        });

        this.cvsEditForm = this.formBuilder.group({
            id: [that.cvEditNote ? that.cvEditNote.id : null],
            note: [that.cvEditNote ? that.cvEditNote.note : null, [Validators.required]]
        });

        this.opporEvents.init();
    }

    get membersForm(): FormArray {
        return this.opporForm.get('members') as FormArray;
    }

    get itemForm(): FormGroup {
        return this.opporForm.get('item') as FormGroup;
    }

    initFormArrayMembers() {
        let aryMembers = [];
        let itemDisabled = !this.item.permiss_edit;
        for (let i = 0; i < this.members.length; i++) {
            let member = this.members [i];
            aryMembers.push(this.formBuilder.group({
                id: [{value: member.id, disabled: itemDisabled}],
                role: [{value: member.role, disabled: itemDisabled}],
                member_exp: [{value: member.member_exp, disabled: itemDisabled}],
                english_level: [{value: member.english_level, disabled: itemDisabled}],
                japanese_level: [{value: member.japanese_level, disabled: itemDisabled}],
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
        this.opporSubmited = true;
        if (that.opporForm.invalid) {
            return;
        }
        that.loading = true;
        var data = this.itemForm.value;
        data.members = this.membersForm.value;
        this.opporService.save(data)
            .pipe(finalize(() => {
                that.loading = false;
                that.opporSubmited = false;
            }))
            .subscribe(
                (result) => {
                    // @ts-ignore
                    that.opporEvents.alertSuccess(result.message);
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

    /*
     * request get cv notes
     */
    loadCvNotes(page = 1) {
        if (!this.item.id) {
            return;
        }
        let that = this;
        that.commentLoading = true;
        this.opporService.getListCvNotes(this.item.id, page).subscribe(
            (response) => {
                let oldData = that.cvNotes ? that.cvNotes.data : [];
                // @ts-ignore
                that.cvNotes = response.cvNotes;
                // @ts-ignore
                that.cvNotes.data = oldData.concat(response.cvNotes.data);
                that.commentLoading = false;
            },
            (error) => {
                that.commentLoading = false;
            }
        );
    }

    /*
     * request get cv notes
     */
    editCvNote(cvNote) {
        cvNote.editting = true;
        this.cvEditNote = cvNote;
        this.cvsEditForm.get('note').setValue(cvNote.note);
    }

    /*
     * request delete cv note
     */
    removeCvNote(cvNote, cvIdx) {
        if (cvNote.deleting) {
            return;
        }
        let that = this;
        // @ts-ignore
        bootbox.confirm({
            message: that.trans('Are you sure want to delete?'),
            className: 'modal-danger',
            callback: function (result) {
                if (result) {
                    cvNote.deleting = true;
                    that.opporService.removeCvNote(cvNote.id)
                    .pipe(finalize(() => {
                        cvNote.deleting = false;
                    }))
                    .subscribe(
                        (response) => {
                            that.cvNotes.data.splice(cvIdx, 1);
                            if (that.cvNotes.data.length < 1 && that.cvNotes.next_page_url) {
                                that.loadCvNotes();
                            }
                        }
                    );
                }
            }
        });
    }

    /*
     * request save cv note
     */
    saveNoteCv(cvNote: null): void {
        var that = this;
        let form;
        let data = {};
        if (cvNote) {
            form = that.cvsEditForm;
            data = this.cvsEditForm.value;
            // @ts-ignore
            data.id = cvNote.id;
            that.editLoading = true;
        } else {
            form = that.cvsForm;
            data = this.cvsForm.value
            that.cvLoading = true;
        }
        if (form.invalid) {
            that.editLoading = false;
            that.cvLoading = false
            return;
        }
        // @ts-ignore
        data.req_oppor_id = this.item.id;

        this.opporService.saveNoteCv(data)
            .pipe(finalize(() => {
                if (cvNote) {
                    that.editLoading = false;
                } else {
                    that.cvLoading = false;
                }
            }))
            .subscribe(
                (result) => {
                    if (!cvNote) {
                        // @ts-ignore
                        that.cvNotes.data.unshift(result.cvItem);
                        form.get('note').setValue('');
                        form.get('note').markAsPristine();
                        form.get('note').markAsUntouched();
                    } else {
                        // @ts-ignore
                        cvNote.note = result.cvItem.note;
                        // @ts-ignore
                        cvNote.editting = false;
                    }
                },
                that.opporService.handleError.bind(this)
            );
    }

}
