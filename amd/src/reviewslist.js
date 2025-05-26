define([
  "jquery",
  "core/ajax",
  "core/templates",
  "core/notification",
], function ($, ajax, templates, notification) {
  return /** @alias module:block_programs/programs */ {
    /**
     * Load the user programs!
     *
     * @method programs
     */
    reviews: function () {
      // Add a click handler to the button.

      $(document).on("click", ".page-link", function (e) {
        e.preventDefault();
        var page_val = $(this).attr("href");
        //                        var activityname = $('.activity-search').val();
        var page = getURLParameter("page", page_val);
        var activityname = $(".activity-search").val();
        var programid = $("#batchsearch_id").find(":selected").val();
        var courseid = $("#coursesearch_id").find(":selected").val();
        var assigntype = $("#assigntype_id").find(":selected").val();
        var state = $(".down-arrow").attr("val");
        if (programid === "Batch") {
          programid = "";
        }
        if (courseid === "Course") {
          courseid = "";
        }
        if (assigntype === "Classification") {
          assigntype = "";
        }
        if (page) {
          var WAITICON = {
            pix: M.util.image_url("i/loading", "core"),
            component: "moodle",
          };
          var loader = $('<img style="display: block;margin: 100px auto" />')
            .attr("src", M.util.image_url(WAITICON.pix, WAITICON.component))
            .addClass("spinner");
          $(".sorted_data").html(
            '<tr> <td colspan="8">' + loader.get(0).outerHTML + "</td></tr>"
          );
          var promises = ajax.call([
            {
              methodname: "block_vlearn_reviews_get_reviews",
              args: {
                program: programid,
                cid: courseid,
                type: assigntype,
                page: page,
                duesorting: state,
              },
            },
          ]);
          promises[0]
            .done(function (data) {
              $(".sorted_data").html(data.displayhtml);
              $(".pagination-nav-filter").html(data.pagedata);
            })
            .fail(notification.exception);
        } else {
          return false;
        }
      });

      $(document).on(
        "keyup",
        ".activity-search",
        delay(function (e) {
          e.preventDefault();
          var page_val = window.location.href;
          var activityname = $(".activity-search").val();
          var page = getURLParameter("page", page_val);
          var WAITICON = {
            pix: M.util.image_url("i/loading", "core"),
            component: "moodle",
          };
          var loader = $("<img />")
            .attr("src", M.util.image_url(WAITICON.pix, WAITICON.component))
            .addClass("spinner");
          $(".allreviews-display").html(
            '<div class="text-center">' + loader.get(0).outerHTML + "</div>"
          );
          var promises = ajax.call([
            {
              methodname: "block_vlearn_reviews_get_reviews",
              args: {
                activityname: activityname,
                page: 0,
              },
            },
          ]);
          promises[0]
            .done(function (data) {
              $(".allreviews-display").html(data.displayhtml);
            })
            .fail(notification.exception);
        }, 1000)
      );

      $(document).on(
        "change",
        ".batchsearch, .coursesearch, .assigntype",
        delay(function (e) {
          e.preventDefault();
          var page_val = window.location.href;
          var activityname = $(".activity-search").val();
          var programid = $("#batchsearch_id").find(":selected").val();
          var courseid = $("#coursesearch_id").find(":selected").val();
          var assigntype = $("#assigntype_id").find(":selected").val();

          if (programid === "Batch") {
            programid = "";
          }
          if (courseid === "Course") {
            courseid = "";
          }
          if (assigntype === "Classification") {
            assigntype = "";
          }
          var page = getURLParameter("page", page_val);
          var WAITICON = {
            pix: M.util.image_url("i/loading", "core"),
            component: "moodle",
          };
          var loader = $("<img />")
            .attr("src", M.util.image_url(WAITICON.pix, WAITICON.component))
            .addClass("spinner");
          $(".allreviews-display").html(
            '<div class="text-center">' + loader.get(0).outerHTML + "</div>"
          );
          var promises = ajax.call([
            {
              methodname: "block_vlearn_reviews_get_reviews",
              args: {
                program: programid,
                cid: courseid,
                type: assigntype,
                page: 0,
                duesorting: "",
              },
            },
          ]);
          promises[0]
            .done(function (data) {
              $(".allreviews-display").html(data.displayhtml);
              //                             $('.pagination-nav-filter').html(data.pagedata);
              if (programid !== "") {
                $(".coursesearch").html(data.options);
              }
            })
            .fail(notification.exception);
        }, 1000)
      );

      $(document).on(
        "click",
        ".down-arrow",
        delay(function (e) {
          e.preventDefault();
          var page_val = window.location.href;
          var programid = $("#batchsearch_id").find(":selected").val();
          var courseid = $("#coursesearch_id").find(":selected").val();
          var assigntype = $("#assigntype_id").find(":selected").val();
          var state = $(this).attr("val");

          if (state === "asc") {
            state = "desc";
            $(".down-arrow").addClass("custom-up");
            $(this).attr("val", "desc");
          } else if (state === "desc") {
            state = "asc";
            $(".down-arrow").removeClass("custom-up");
            $(this).attr("val", "asc");
          }
          if (programid === "Batch") {
            programid = "";
          }
          if (courseid === "Course") {
            courseid = "";
          }
          if (assigntype === "Classification") {
            assigntype = "";
          }
          var page = getURLParameter("page", page_val);
          var WAITICON = {
            pix: M.util.image_url("i/loading", "core"),
            component: "moodle",
          };
          var loader = $('<img style="display: block;margin: 100px auto" />')
            .attr("src", M.util.image_url(WAITICON.pix, WAITICON.component))
            .addClass("spinner");
          $(".sorted_data").html(
            '<tr> <td colspan="8">' + loader.get(0).outerHTML + "</td></tr>"
          );
          var promises = ajax.call([
            {
              methodname: "block_vlearn_reviews_get_reviews",
              args: {
                program: programid,
                cid: courseid,
                type: assigntype,
                page: 0,
                duesorting: state,
              },
            },
          ]);
          promises[0]
            .done(function (data) {
              $(".sorted_data").html(data.displayhtml);
              $(".pagination-nav-filter").html(data.pagedata);
            })
            .fail(notification.exception);
        }, 1000)
      );

      $(document).on(
        "click",
        "#grade_popup",
        delay(function (e) {
            var programid = $(this).attr("data-programid");
            var id = $(this).attr("data-id");
            var cid = $(this).attr("cmid");
            var promises = ajax.call([
            {
              methodname: "block_vlearn_reviews_get_pending_submissions",
              args: {
                programid: programid,
                instanceid: id,
                cmid: cid,
              },
            },
          ]);
          promises[0]
            .done(function (data) {
              $(".custom-modal").html(data.displayhtml);
              $("#exampleModal").modal("show");
            })
        }, 1000)
      );
    },
  };

  function getURLParameter(name, page_val) {
    return (
      decodeURIComponent(
        (new RegExp("[?|&]" + name + "=" + "([^&;]+?)(&|#|;|$)").exec(
          page_val
        ) || [null, ""])[1].replace(/\+/g, "%20")
      ) || null
    );
  }
  //Function for delay the keyup event
  function delay(callback, ms) {
    var timer = 0;
    return function () {
      var context = this,
        args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        callback.apply(context, args);
      }, ms || 0);
    };
  }
});
