import { Component } from '@angular/core';

@Component({
  selector: 'app-opportunity',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {

    opporParams: any;
    opporTrans: any;

    trans(text: string): string {
        if (typeof this.opporTrans[text] == 'undefined') {
            return text;
        }
        return this.opporTrans[text];
    }

    renderStatusHtml($status, $statuses, $class = 'callout'): string {
        let $html = '<div class="' + $class + ' text-center white-space-nowrap ' + $class;
        $status = parseInt($status);
        if (typeof $statuses[$status] == 'undefined') {
            return null;
        }
        switch ($status) {
            case this.opporParams.STT_OPEN:
                $html += '-info">' + $statuses[$status];
                break;
            case this.opporParams.STT_PROCESSING:
                $html += '-warning">' + $statuses[$status];
                break;
            case this.opporParams.STT_SUBMIT:
                $html += '-info">' + $statuses[$status];
                break;
            case this.opporParams.STT_PASS:
                $html += '-success">' + $statuses[$status];
                break;
            case this.opporParams.STT_CANCEL:
            case this.opporParams.STT_FAIL:
            case this.opporParams.STT_CLOSED:
                $html += '-danger">' + $statuses[$status];
                break;
            default:
                return null;
        }
        return $html += '</div>';
    }

    getValueLabel(value, labels): string {
        if (typeof labels[value] == 'undefined') {
            return null;
        }
        return labels[value];
    }

    /**
     * @param values array program id
     * @param labels array of object program {id: <id>, name: <name>}
     */
    getAryValueLabels(values, labels): string {
        let result = [];
        let aryLabels = [];
        for (let i = 0; i < labels.length; i++) {
            aryLabels[labels[i].id] = labels[i].name;
        }
        for (let key = 0; key < values.length; key++) {
            if (typeof aryLabels[values[key]] != 'undefined') {
                result.push('<span>'+ aryLabels[values[key]] +'</span>');
            }
        }
        return result.join(', ');
    }

}
