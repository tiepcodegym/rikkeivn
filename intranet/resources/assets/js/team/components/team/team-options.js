import React, {Component} from 'react';

export default class TeamOptions extends Component {

    renderNestedOptions(teams, selected, depth = -1) {
        let htmlOptions = '';
        if (depth == -1) {
            htmlOptions += "<option value=\"\">&nbsp;</option>";
        }
        depth++;
        for (let i = 0; i < teams.length; i++) {
            htmlOptions += "<option value=\""+ teams[i].id +"\" "+ (teams[i].id == selected ? "selected" : "") +">"
                    + "&nbsp;".repeat(depth * 4) + teams[i].name +"</option>";
            if (teams[i].childs.length > 0) {
                htmlOptions += this.renderNestedOptions(teams[i].childs, selected, depth);
            }
        }
        return htmlOptions;
    }

    render () {
        let that = this;
        let {teams, className, selected, fieldName, handleChangeTeamField} = this.props; 
        return (
            <select className={className} onChange={(e) => handleChangeTeamField(e, fieldName)}
                dangerouslySetInnerHTML={{__html: that.renderNestedOptions(teams, selected)}}
                data-field={fieldName}
                style={{width: '100%'}}>
            </select>
        )
    }
}

