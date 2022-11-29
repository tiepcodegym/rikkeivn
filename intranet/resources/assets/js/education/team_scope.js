
var parentId = 1;
var elCurrentParent = $('.multi-select-style input[type="checkbox"][value="' + parentId + '"]');
var elCurrentParentOption = $('.js-team[value="' + parentId + '"]');

elCurrentParent.attr('data-is-checked', elCurrentParentOption.data('is-checked'));


initScopeData(1);

function initScopeData(currentTeamId) {

  var objectCurrentTeam = teamPath[currentTeamId];
  if (objectCurrentTeam['child'].length) {
    for (var i = 0; i < objectCurrentTeam['child'].length; i++) {
      var currentIdChild = objectCurrentTeam['child'][i];
      var elCurrentChild = $('.multi-select-style input[type="checkbox"][value="' + currentIdChild + '"]');
      var elCurrentChildOption = $('.js-team[value="' + currentIdChild + '"]');
      elCurrentChild.attr('data-parent-id', currentTeamId);
      elCurrentChild.attr('data-is-checked', elCurrentChildOption.data('is-checked'));
      initScopeData(currentIdChild);
    }
  }
}

function fillCheckBoxChilds(currentTeamId) {
  var elCurrentTeam = teamPath[currentTeamId];

  for (var i = 0; i < elCurrentTeam['child'].length; i++) {
    var currentIdChild = elCurrentTeam['child'][i];
    var elCurrentChild = $('.multi-select-style input[type="checkbox"][value="' + currentIdChild + '"]');
    if (!elCurrentChild.data('is-checked')) {
      elCurrentChild.data('is-checked', true);
      elCurrentChild.trigger('click').attr('checked', true);
      fillCheckBoxChilds(currentIdChild);
    }
  }
}

function fillCheckBoxParent(currentTeamId) {
  if (!currentTeamId) {
    return;
  }
  var elCurrentTeam = teamPath[currentTeamId];
  var totalChilds = $('.multi-select-style input[type="checkbox"][data-parent-id=' + elCurrentTeam['parent'][0] + ']').length;
  var totalChildsChecked = $('.multi-select-style input[type="checkbox"][data-parent-id=' + elCurrentTeam['parent'][0] + ']:checked').length;
  if (totalChilds === totalChildsChecked) {
    var elParent = $('.multi-select-style input[type="checkbox"][value=' + elCurrentTeam['parent'][0] + ']');
    if (!elParent.data('is-checked')) {
      elParent.data('is-checked', true);
      elParent.trigger('click').attr('checked', true);
      fillCheckBoxParent(elCurrentTeam['parent'][0]);
    }
  }
}

function fillCheckBox(currentTeamId) {
  var elCurrentTeam = teamPath[currentTeamId];
  if (elCurrentTeam['child'].length) {
    fillCheckBoxChilds(currentTeamId);
  }
  if (elCurrentTeam['parent'] && elCurrentTeam['parent'][0]) {
    fillCheckBoxParent(currentTeamId);
  }
}

function unFillCheckBoxChilds(elCurrentTeam) {
  if (elCurrentTeam['child'].length) {
    for (var i = 0; i < elCurrentTeam['child'].length; i++) {
      var currentIdChild = elCurrentTeam['child'][i];
      var elCurrentChild = $('.multi-select-style input[type="checkbox"][value="' + currentIdChild + '"]');
      if (elCurrentChild.data('is-checked')) {
        elCurrentChild.data('is-checked', false);
        elCurrentChild.trigger('click').attr('checked', false);
      }
      unFillCheckBoxChilds(teamPath[currentIdChild]);
    }
  }
}

function unFillCheckBoxParent(elCurrentTeam) {
  var parentIds = elCurrentTeam['parent'];
  if (parentIds.length) {
    var parentId = parentIds[0];
    if (teamPath[parentId]['child']) {
      var elChildsOfParent = $('input[data-parent-id=' + parentId + ']:checked');
    }
    var elParent = $('.multi-select-style input[type="checkbox"][value="' + parentId + '"]');
    if (!elChildsOfParent.length && elParent.data('is-checked')) {
      elParent.data('is-checked', false);
      elParent.trigger('click').attr('checked', false);
      unFillCheckBoxParent(teamPath[parentId]);
    }
  }
}

function unFillCheckBox(elCurrentTeam) {
  if (elCurrentTeam['child'].length) {
    unFillCheckBoxChilds(elCurrentTeam);
  }
  if (elCurrentTeam['parent'] && elCurrentTeam['parent'].length) {
    unFillCheckBoxParent(elCurrentTeam);
  }
}

$('.js-team input').change(function (e) {
  if (this.checked) {
    if (!$(e.target).data('is-checked')) {
      var id = $(e.target).attr('value');
      $(e.target).data('is-checked', true);
      fillCheckBox(id);
    }
  }

  if (!this.checked) {
    if ($(e.target).data('is-checked')) {
      var id = $(e.target).attr('value');
      $(e.target).data('is-checked', false);
      unFillCheckBox(teamPath[id]);
    }
  }
});



var parentIdsss = 1;
var elCurrentParentsss = $('.multi-select-style-search input[type="checkbox"][value="' + parentIdsss + '"]');
var elCurrentParentOptionsss = $('.js-team-search[value="' + parentIdsss + '"]');

elCurrentParentsss.attr('data-is-checkeds', elCurrentParentOptionsss.data('is-checkeds'));


initScopeDatasss(1);

function initScopeDatasss(currentTeamId) {

  var objectCurrentTeamsss = teamPath[currentTeamId];
  if (objectCurrentTeamsss['child'].length) {
    for (var i = 0; i < objectCurrentTeamsss['child'].length; i++) {
      var currentIdChild = objectCurrentTeamsss['child'][i];
      var elCurrentChild = $('.multi-select-style-search input[type="checkbox"][value="' + currentIdChild + '"]');
      var elCurrentChildOption = $('.js-team-search[value="' + currentIdChild + '"]');
      elCurrentChild.attr('data-parent-ids', currentTeamId);
      elCurrentChild.attr('data-is-checkeds', elCurrentChildOption.data('is-checkeds'));
      initScopeDatasss(currentIdChild);
    }
  }
}

function fillCheckBoxChildsss(currentTeamId) {
  var elCurrentTeam = teamPath[currentTeamId];

  for (var i = 0; i < elCurrentTeam['child'].length; i++) {
    var currentIdChild = elCurrentTeam['child'][i];
    var elCurrentChild = $('.multi-select-style-search input[type="checkbox"][value="' + currentIdChild + '"]');
    if (!elCurrentChild.data('is-checkeds')) {
      elCurrentChild.data('is-checkeds', true);
      elCurrentChild.trigger('click').attr('checked', true);
      fillCheckBoxChildsss(currentIdChild);
    }
  }
}

function fillCheckBoxParentsss(currentTeamId) {
  if (!currentTeamId) {
    return;
  }
  var elCurrentTeam = teamPath[currentTeamId];
  var totalChilds = $('.multi-select-style-search input[type="checkbox"][data-parent-ids=' + elCurrentTeam['parent'][0] + ']').length;
  var totalChildsChecked = $('.multi-select-style-search input[type="checkbox"][data-parent-ids=' + elCurrentTeam['parent'][0] + ']:checked').length;
  if (totalChilds === totalChildsChecked) {
    var elParent = $('.multi-select-style-search input[type="checkbox"][value=' + elCurrentTeam['parent'][0] + ']');
    if (!elParent.data('is-checkeds')) {
      elParent.data('is-checkeds', true);
      elParent.trigger('click').attr('checked', true);
      fillCheckBoxParentsss(elCurrentTeam['parent'][0]);
    }
  }
}

function fillCheckBoxsss(currentTeamId) {
  var elCurrentTeam = teamPath[currentTeamId];
  if (elCurrentTeam['child'].length) {
    fillCheckBoxChildsss(currentTeamId);
  }
  if (elCurrentTeam['parent'] && elCurrentTeam['parent'][0]) {
    fillCheckBoxParentsss(currentTeamId);
  }
}

function unFillCheckBoxChildsss(elCurrentTeam) {
  if (elCurrentTeam['child'].length) {
    for (var i = 0; i < elCurrentTeam['child'].length; i++) {
      var currentIdChild = elCurrentTeam['child'][i];
      var elCurrentChild = $('.multi-select-style-search input[type="checkbox"][value="' + currentIdChild + '"]');
      if (elCurrentChild.data('is-checkeds')) {
        elCurrentChild.data('is-checkeds', false);
        elCurrentChild.trigger('click').attr('checked', false);
      }
      unFillCheckBoxChildsss(teamPath[currentIdChild]);
    }
  }
}

function unFillCheckBoxParentsss(elCurrentTeam) {
  var parentIds = elCurrentTeam['parent'];
  if (parentIds.length) {
    var parentIdsss = parentIds[0];
    if (teamPath[parentIdsss]['child']) {
      var elChildsOfParent = $('input[data-parent-ids=' + parentIdsss + ']:checked');
    }
    var elParent = $('.multi-select-style-search input[type="checkbox"][value="' + parentIdsss + '"]');
    if (!elChildsOfParent.length && elParent.data('is-checkeds')) {
      elParent.data('is-checkeds', false);
      elParent.trigger('click').attr('checked', false);
      unFillCheckBoxParentsss(teamPath[parentIdsss]);
    }
  }
}

function unFillCheckBoxsss(elCurrentTeam) {
  if (elCurrentTeam['child'].length) {
    unFillCheckBoxChildsss(elCurrentTeam);
  }
  if (elCurrentTeam['parent'] && elCurrentTeam['parent'].length) {
    unFillCheckBoxParentsss(elCurrentTeam);
  }
}

$('.js-team-search input').change(function (e) {
  if (this.checked) {
    if (!$(e.target).data('is-checkeds')) {
      var id = $(e.target).attr('value');
      $(e.target).data('is-checkeds', true);
      fillCheckBoxsss(id);
    }
  }

  if (!this.checked) {
    if ($(e.target).data('is-checkeds')) {
      var id = $(e.target).attr('value');
      $(e.target).data('is-checkeds', false);
      unFillCheckBoxsss(teamPath[id]);
    }
  }
});