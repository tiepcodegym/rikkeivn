import React, {Component} from 'react';

export default class TeamItem extends Component {
    render() {
        let {team, selectTeam, currentTeam} = this.props;
        return (
            <li>
                <label className="team-item">
                    <a href="#" id={"team_item_" + team.id} data-id={team.id} level={team.level}
                        onClick={(e) => selectTeam(e, team)}
                        className={currentTeam.id == team.id ? 'active' : ''}>{team.name}</a>
                </label>
                {team.childs.length > 0 ? (
                    <ul>
                        {team.childs.map((child, index) => (
                            <TeamItem
                                key={index}
                                team={child}
                                selectTeam={selectTeam}
                                currentTeam={currentTeam}
                            />
                        ))}
                    </ul>
                ) : null}
            </li>
        )
    }
}


