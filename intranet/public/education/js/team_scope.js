function initScopeData(e){var t=teamPath[e];if(t.child.length)for(var a=0;a<t.child.length;a++){var c=t.child[a],i=$('.multi-select-style input[type="checkbox"][value="'+c+'"]'),l=$('.js-team[value="'+c+'"]');i.attr("data-parent-id",e),i.attr("data-is-checked",l.data("is-checked")),initScopeData(c)}}function fillCheckBoxChilds(e){for(var t=teamPath[e],a=0;a<t.child.length;a++){var c=t.child[a],i=$('.multi-select-style input[type="checkbox"][value="'+c+'"]');i.data("is-checked")||(i.data("is-checked",!0),i.trigger("click").attr("checked",!0),fillCheckBoxChilds(c))}}function fillCheckBoxParent(e){if(e){var t=teamPath[e],a=$('.multi-select-style input[type="checkbox"][data-parent-id='+t.parent[0]+"]").length,c=$('.multi-select-style input[type="checkbox"][data-parent-id='+t.parent[0]+"]:checked").length;if(a===c){var i=$('.multi-select-style input[type="checkbox"][value='+t.parent[0]+"]");i.data("is-checked")||(i.data("is-checked",!0),i.trigger("click").attr("checked",!0),fillCheckBoxParent(t.parent[0]))}}}function fillCheckBox(e){var t=teamPath[e];t.child.length&&fillCheckBoxChilds(e),t.parent&&t.parent[0]&&fillCheckBoxParent(e)}function unFillCheckBoxChilds(e){if(e.child.length)for(var t=0;t<e.child.length;t++){var a=e.child[t],c=$('.multi-select-style input[type="checkbox"][value="'+a+'"]');c.data("is-checked")&&(c.data("is-checked",!1),c.trigger("click").attr("checked",!1)),unFillCheckBoxChilds(teamPath[a])}}function unFillCheckBoxParent(e){var t=e.parent;if(t.length){var a=t[0];if(teamPath[a].child)var c=$("input[data-parent-id="+a+"]:checked");var i=$('.multi-select-style input[type="checkbox"][value="'+a+'"]');!c.length&&i.data("is-checked")&&(i.data("is-checked",!1),i.trigger("click").attr("checked",!1),unFillCheckBoxParent(teamPath[a]))}}function unFillCheckBox(e){e.child.length&&unFillCheckBoxChilds(e),e.parent&&e.parent.length&&unFillCheckBoxParent(e)}function initScopeDatasss(e){var t=teamPath[e];if(t.child.length)for(var a=0;a<t.child.length;a++){var c=t.child[a],i=$('.multi-select-style-search input[type="checkbox"][value="'+c+'"]'),l=$('.js-team-search[value="'+c+'"]');i.attr("data-parent-ids",e),i.attr("data-is-checkeds",l.data("is-checkeds")),initScopeDatasss(c)}}function fillCheckBoxChildsss(e){for(var t=teamPath[e],a=0;a<t.child.length;a++){var c=t.child[a],i=$('.multi-select-style-search input[type="checkbox"][value="'+c+'"]');i.data("is-checkeds")||(i.data("is-checkeds",!0),i.trigger("click").attr("checked",!0),fillCheckBoxChildsss(c))}}function fillCheckBoxParentsss(e){if(e){var t=teamPath[e],a=$('.multi-select-style-search input[type="checkbox"][data-parent-ids='+t.parent[0]+"]").length,c=$('.multi-select-style-search input[type="checkbox"][data-parent-ids='+t.parent[0]+"]:checked").length;if(a===c){var i=$('.multi-select-style-search input[type="checkbox"][value='+t.parent[0]+"]");i.data("is-checkeds")||(i.data("is-checkeds",!0),i.trigger("click").attr("checked",!0),fillCheckBoxParentsss(t.parent[0]))}}}function fillCheckBoxsss(e){var t=teamPath[e];t.child.length&&fillCheckBoxChildsss(e),t.parent&&t.parent[0]&&fillCheckBoxParentsss(e)}function unFillCheckBoxChildsss(e){if(e.child.length)for(var t=0;t<e.child.length;t++){var a=e.child[t],c=$('.multi-select-style-search input[type="checkbox"][value="'+a+'"]');c.data("is-checkeds")&&(c.data("is-checkeds",!1),c.trigger("click").attr("checked",!1)),unFillCheckBoxChildsss(teamPath[a])}}function unFillCheckBoxParentsss(e){var t=e.parent;if(t.length){var a=t[0];if(teamPath[a].child)var c=$("input[data-parent-ids="+a+"]:checked");var i=$('.multi-select-style-search input[type="checkbox"][value="'+a+'"]');!c.length&&i.data("is-checkeds")&&(i.data("is-checkeds",!1),i.trigger("click").attr("checked",!1),unFillCheckBoxParentsss(teamPath[a]))}}function unFillCheckBoxsss(e){e.child.length&&unFillCheckBoxChildsss(e),e.parent&&e.parent.length&&unFillCheckBoxParentsss(e)}var parentId=1,elCurrentParent=$('.multi-select-style input[type="checkbox"][value="'+parentId+'"]'),elCurrentParentOption=$('.js-team[value="'+parentId+'"]');elCurrentParent.attr("data-is-checked",elCurrentParentOption.data("is-checked")),initScopeData(1),$(".js-team input").change(function(e){if(this.checked&&!$(e.target).data("is-checked")){var t=$(e.target).attr("value");$(e.target).data("is-checked",!0),fillCheckBox(t)}if(!this.checked&&$(e.target).data("is-checked")){var t=$(e.target).attr("value");$(e.target).data("is-checked",!1),unFillCheckBox(teamPath[t])}});var parentIdsss=1,elCurrentParentsss=$('.multi-select-style-search input[type="checkbox"][value="'+parentIdsss+'"]'),elCurrentParentOptionsss=$('.js-team-search[value="'+parentIdsss+'"]');elCurrentParentsss.attr("data-is-checkeds",elCurrentParentOptionsss.data("is-checkeds")),initScopeDatasss(1),$(".js-team-search input").change(function(e){if(this.checked&&!$(e.target).data("is-checkeds")){var t=$(e.target).attr("value");$(e.target).data("is-checkeds",!0),fillCheckBoxsss(t)}if(!this.checked&&$(e.target).data("is-checkeds")){var t=$(e.target).attr("value");$(e.target).data("is-checkeds",!1),unFillCheckBoxsss(teamPath[t])}});