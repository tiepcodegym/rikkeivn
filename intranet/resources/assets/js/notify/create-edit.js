$(function () {
    $('#datepicker_start_at').datetimepicker(
        {
            allowInputToggle: true,
            format: 'DD-MM-YYYY HH:mm',
            sideBySide: true,
        }
    );
    $('#date_type1').on('click', function () {
        $('#datepicker_start_at').hide();
        $('#available_at').prop('disabled', true);
    });
    $('#date_type2').on('click', function () {
        $('#available_at').prop('disabled', false);
        $('#datepicker_start_at').show();
    });

    window.selectAll = function() {
        var checkboxes = document.getElementsByTagName('input');
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].type == 'checkbox') {
                checkboxes[i].checked = true;
            }
        }
        var x = document.getElementById('error-select-team');
        x.style.display = 'none';
    };

    window.deSelect = function() {
        var checkboxes = document.getElementsByTagName('input');
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].type == 'checkbox') {
                checkboxes[i].checked = false;
            }
        }
    };

    window.getTeam = function() {
        var team_id = [];
        var team_name = [];
        $.each($("input[name='team']:checked"), function () {
            team_id.push($(this).val());
            team_name.push($(this).next('span').text());
        });
        document.getElementById('team_list').value = team_id.join(", ");
        document.getElementById('team_name').value = team_name.join(", ");

        $('#getTeamModal').modal('hide');
    };

    window.closeSelectTeam = function() {
        var team_id = [];
        str_team_id = document.getElementById('team_list').value;
        team_id = str_team_id.split(",");

        if (team_id.length == 0) {
            var checkboxes = document.getElementsByTagName('input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = false;
                }
            }
        } else {
            var index_checkbox = [];
            var checkboxes = document.getElementsByTagName('input');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = false;
                    for (var k = 0; k < team_id.length; k++) {
                        if (team_id[k].trim() == checkboxes[i].value.trim()) {
                            checkboxes[i].checked = true;
                        }
                    }
                }
            }
        }
        $('#getTeamModal').modal('hide');
    }
});