import React, {Component} from 'react';
import TeamItem from './team-item';
import Helper from './../../react-helper';

export default class TeamList extends Component {

    render () {
        let {
            teams, 
            selectTeam,
            currentTeam,
        } = this.props;
        return (
            <ul className="treeview team-tree">
                {teams.map((team, index) => (
                    <TeamItem
                        key={index}
                        team={team}
                        selectTeam={selectTeam}
                        currentTeam={currentTeam}
                    />
                ))}
            </ul>
        )
    }
}


