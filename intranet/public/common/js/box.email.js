function showList(e, a) {
  void 0 !== ajaxLoadFlagDelay[e] && (ajaxLoadFlagDelay[e] = !1);
  var r = $.trim($("#token").val()),
    t = $(e).data("name"),
    i = $("body").find('.rikker-result[data-for="' + t + '"]');
  "" !== a
    ? (clearTimeout(ajaxClearTimeout),
      (ajaxClearTimeout = setTimeout(function () {
        ajax_request = $.ajax({
          url: baseUrl + "/css/get_rikker",
          type: "post",
          dataType: "JSON",
          data: { _token: r, value: a },
        })
          .done(function (a) {
            if (a.length > 0) {
              var r = "",
                l = a.length;
              i.css("display", "block");
              for (var o = 0; o < l; o++)
                (r +=
                  '<div class="rikker-item" data-name="' +
                  t +
                  '" onclick="chooseRikker(this);">'),
                  (r += '   <div class="col-xs-12 ">'),
                  (r += '       <div class="row">'),
                  (r += '           <div class="pull-left">'),
                  (r +=
                    '               <img style="border-radius: 50px; width: 50px; height: 50px;" src="' +
                    a[o].avatar_url +
                    '" />'),
                  (r += "           </div>"),
                  (r +=
                    '           <div class="pull-left" style="padding: 8px;">'),
                  (r +=
                    '               <p class="rikker-name" data-name="' +
                    a[o].name +
                    '">' +
                    a[o].name +
                    "</p>"),
                  (r +=
                    '               <p class="rikker-email" data-email="' +
                    a[o].email +
                    '">' +
                    a[o].email +
                    "</p>"),
                  (r +=
                    '               <input class="rikker-name-jp" type="hidden" data-name-jp="' +
                    a[o].japanese_name +
                    '" />'),
                  (r += "           </div>"),
                  (r += "       </div>"),
                  (r += "   </div>"),
                  (r += "</div>");
              i.html(r),
                $(
                  '.rikker-result[data-for="' +
                    t +
                    '"] .rikker-item:first-child'
                ).addClass("hovered");
            } else i.html(""), i.css("display", "none");
            void 0 !== ajaxLoadingEmail[e] && (ajaxLoadingEmail[e] = !1);
          })
          .fail(function () {
            console.log("Ajax failed to fetch data"),
              void 0 !== ajaxLoadingEmail[e] && (ajaxLoadingEmail[e] = !1);
          });
      }, ajaxLoadDelay)))
    : (i.css("display", "none"),
      void 0 !== ajaxLoadingEmail[e] && (ajaxLoadingEmail[e] = !1));
}
function selectUpDown(e, a) {
  $('.rikker-result[data-for="' + e + '"] .rikker-item').length > 0 &&
    (40 === a
      ? $(".rikker-result[data-for='" + e + "'] .rikker-item.hovered").length <=
        0
        ? $(
            '.rikker-result[data-for="' + e + '"] .rikker-item:first-child'
          ).addClass("hovered")
        : $(".rikker-result[data-for='" + e + "'] .rikker-item.hovered")
            .removeClass("hovered")
            .next()
            .addClass("hovered")
      : 38 == a &&
        ($(".rikker-result[data-for='" + e + "'] .rikker-item.hovered")
          .length <= 0
          ? $(
              '.rikker-result[data-for="' + e + '"] .rikker-item:last-child'
            ).addClass("hovered")
          : $(".rikker-result[data-for='" + e + "'] .rikker-item.hovered")
              .removeClass("hovered")
              .prev()
              .addClass("hovered")));
}
function backSpace(e) {
  $(".rikker-set[data-for='" + e + "'] .vN").length > 0 &&
    $(".rikker-set[data-for='" + e + "'] .vN:last-child .vM").trigger("click");
}
function tabEvent(e, a, r) {
  void 0 == r && (r = {});
  var t = $(e).data("name");
  if (
    $('.rikker-result[data-for="' + t + '"]').find(".rikker-item").length > 0 &&
    (void 0 == r.setText || !r.setText)
  )
    $('.rikker-result[data-for="' + t + '"] .rikker-item.hovered').trigger(
      "click"
    );
  else {
    $("#pm_email_name").removeAttr("readonly");
    var i = !1;
    if (
      ($('input[type="hidden"][data-for="' + t + '"]').each(function () {
        if ($(this).val() === a) return (i = !0), !1;
      }),
      !i)
    ) {
      var l = "",
        o = $("body").find('.rikker-set[data-for="' + t + '"]');
      l = validateEmailOutSide(a.toLowerCase())
        ? '<span class="vN bfK a3q" email="' +
          a +
          '"><div class="vT">' +
          a +
          '</div><div class="vM" data-remove="' +
          a +
          '" data-for="' +
          t +
          '" onclick="removeRikker(this);"></div></span>'
        : '<span class="vN bfK a3q error" email="' +
          a +
          '" style="background-clo"><div class="vT">' +
          a +
          '</div><div class="vM" data-remove="' +
          a +
          '" data-for="' +
          t +
          '" onclick="removeRikker(this);"></div></span>';
      var d = o.html();
      o.css("display", "inline"),
        validateEmailOutSide(a.toLowerCase()) &&
          $(".rikker-relate-container").append(
            '<input type="hidden" data-for="' +
              t +
              '" name="' +
              t +
              '[]" value="' +
              a +
              '" />'
          ),
        "pm_email" === t
          ? ($("#" + t)
              .attr("readonly", "true")
              .css("background-color", "#fff"),
            o.html(l))
          : o.html(d + l),
        checkSet(t);
    }
  }
  $("#" + t).val(""),
    $('.rikker-result[data-for="' + t + '"]').css("display", "none"),
    $('.rikker-result[data-for="' + t + '"]').html("");
}
function chooseRikker(e) {
  var a = $(e).find(".rikker-name").data("name"),
    r = $(e).find(".rikker-email").data("email"),
    t = $(e).find(".rikker-name-jp").data("name-jp"),
    i = !1,
    l = $(e).data("name");
  if (
    ($('input[type="hidden"][data-for="' + l + '"]').each(function () {
      if ($(this).val() === r) return (i = !0), !1;
    }),
    !i)
  ) {
    a
      ? ((nameShowNull = a), (nameValueNull = a))
      : ((nameShowNull = r), (nameValueNull = r.replace(/@.*$/, "")));
    var o = $("body").find('.rikker-set[data-for="' + l + '"]'),
      d = o.html(),
      n =
        '<span class="vN bfK a3q" email="' +
        r +
        '"><div class="vT">' +
        nameShowNull +
        '</div><div class="vM" data-remove="' +
        r +
        '" data-for="' +
        l +
        '" onclick="removeRikker(this);"></div></span>';
    "1" == $("#" + l).data("length")
      ? (o.html(n),
        $('input[type="hidden"][data-for="' + l + '"]').remove(),
        $(".rikker-relate-container").append(
          '<input type="hidden" data-for="' +
            l +
            '" name="' +
            l +
            '[]" value="' +
            r +
            '" />'
        ),
        $("body")
          .find("#" + l + "_name")
          .val(nameValueNull),
        $("body")
          .find("#" + l + "_jp")
          .val(t),
        $("#" + l)
          .attr("readonly", "true")
          .css("background-color", "#fff"),
        $("#" + l + "_name").attr("readonly", "true"))
      : (o.html(d + n),
        $(".rikker-relate-container").append(
          '<input type="hidden" data-for="' +
            l +
            '" name="' +
            l +
            '[]" value="' +
            r +
            '" />'
        )),
      checkSet(l);
  }
  $("#" + l).val(""),
    $("#" + l).focus(),
    $('.rikker-result[data-for="' + l + '"]').css("display", "none"),
    $('.rikker-result[data-for="' + l + '"]').html("");
}
function removeRikker(e) {
  var a = $(e).data("remove"),
    r = $(e).data("for");
  $('input[type=hidden][data-for="' + r + '"][value="' + a + '"]').remove(),
    $(e).parent().remove(),
    $("#" + r + "_name")
      .val("")
      .removeAttr("readonly"),
    $("#" + r + "_jp").val(""),
    checkSet(r),
    $("#" + r).removeAttr("readonly"),
    $("#" + r).focus();
}
function checkSet(e) {
  $("#" + e).removeClass("pm-email-update"),
    $("#" + e).removeClass("rikker-relate-update"),
    $('.rikker-set[data-for="' + e + '"] .vN').length > 0
      ? ($('.rikker-set[data-for="' + e + '"]').css("display", "inline"),
        $("#" + e)
          .css("top", "-3px ")
          .css("height", "26px "),
        $("#" + e).position().left < 15 && $("#" + e).css("top", "0 "))
      : ($('.rikker-set[data-for="' + e + '"]').css("display", "none"),
        $("#" + e)
          .css("top", "0 ")
          .css("height", "32px")),
    checkEmail(e);
}
function checkEmail(e) {
  var a = !1;
  var x = !1;
  $(".rikker-relate-container").parent().find("label.error").remove(),
    $('input[type=hidden][data-for="' + e + '"]').length > 0 &&
      ($("#" + e + "_check").val("1"), $("#" + e + "_check-error").remove()),
    $('.rikker-set[data-for="' + e + '"] .vN').length > 0
      ? ($("#" + e + "_check").val("1"),
        $("#" + e + "_check-error").remove(),
        $('.rikker-set[data-for="' + e + '"] .vN').each(function () {
          var e = $(this).attr("email");
          var flag =$(this).parent().attr('flag');
          if(flag == "unValidate") {
            if (!validateEmailOutSide(e)) return (x = !0), !1;
          } else {
            if (!validateEmail(e)) return (a = !0), !1;
          }
        }),
        a
          ? ($("#" + e + "_validate").val(""),
            $(".rikker-relate-container").after(
              '<label class="error" style="display: block;">' +
                emailInvalid +
                "</label>" 
            ))
          : ($("#" + e + "_validate").val("1"),
            $("#" + e + "_validate-error").remove()),
        x  ? ($("#" + e + "_validate").val(""),
          $(".rikker-relate-container").after(
            '<label class="error" style="display: block;">' +
              emailFormat +
              "</label>" 
          )) : ($("#" + e + "_validate").val("1"),
          $("#" + e + "_validate-error").remove()))
      : ($("#" + e + "_check").val(""),
        $("#" + e + "_validate").val("1"),
        $("#" + e + "_validate-error").remove());
}
function validateEmail(e) {
  var a =
    /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
  return (
    !!a.test(e) &&
    e.indexOf("@rikkeisoft.com", e.length - "@rikkeisoft.com".length) !== -1
  );
}
function validateEmailOutSide(e) {
  var a =
    /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/;
  return (
    !!a.test(e)
  );
}
function getLeft() {
  var e = $(".rikker-set").width();
  return e + 19;
}
var ajax_request,
  ajaxLoadingEmail = {},
  ajaxLoadDelay = 400,
  ajaxLoadFlagDelay = {},
  ajaxClearTimeout;
$(document).mouseup(function (e) {
  var a = $(".rikker-result");
  a.is(e.target) || 0 !== a.has(e.target).length || (a.hide(), a.html(""));
}),
  $(".rikker-item").hover(
    function () {
      $(this).css("background-color", "#ececec");
    },
    function () {
      $(this).css("background-color", "#fff");
    }
  );
