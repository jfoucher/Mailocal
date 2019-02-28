require('../../vendor/kevinpapst/adminlte-bundle/Resources/assets/admin-lte');
import jQuery from 'jquery';
require('icheck/skins/flat/blue.css');
require('../scss/app.scss');

(function ($) {
  $(document).ready(() => {
    //Enable iCheck plugin for checkboxes
    //iCheck for checkbox and radio inputs
    $('.mailbox-messages input[type="checkbox"]').iCheck({
      checkboxClass: 'icheckbox_flat-blue',
      radioClass: 'iradio_flat-blue'
    });

    //Enable check and uncheck all functionality
    $('body').on('click', ".checkbox-toggle", function () {
      const clicks = $(this).data('clicks');
      if (clicks) {
        //Uncheck all checkboxes
        $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
        $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
      } else {
        //Check all checkboxes
        $(".mailbox-messages input[type='checkbox']").iCheck("check");
        $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
      }
      $(this).data("clicks", !clicks);
    });

    $('.trash').on('click', (ev) => {
      const btn = $(ev.currentTarget);
      $(".mailbox-messages input[type='checkbox']:checked").each((i, item) => {
        const $item = $(item);
        $.ajax({
          url: $item.data('link'),
          method: 'delete'
        }).then((res) => {
          $item.parents('tr').remove();
        })
      })
    });

    $('.mark-read').on('click', (ev) => {
      const btn = $(ev.currentTarget);
      $(".mailbox-messages input[type='checkbox']:checked").each((i, item) => {
        const $item = $(item);
        const curTr = $item.parents('tr');
        if (!curTr.hasClass('read')) {
          $.ajax({
            url: $item.parents('tr').data('markread'),
            method: 'put'
          }).then((res) => {
            $item.parents('tr').addClass('read');
          })
        }
      })
    });
    let to = null;
    $('body').on('click', '.mailbox-messages table tbody > tr', (ev) => {
      const tr = $(ev.currentTarget);
      const html = tr.find('script.html-email').html();
      const text = tr.find('script.text-email').html();
      const attach = tr.find('.mailbox-attachment > a');
      // Mark Read
      if (to) {
        clearTimeout(to);
      }
      if (!tr.hasClass('read')) {
        to = setTimeout(() => {
          $.ajax({
            url: tr.data('markread'),
            method: 'put'
          }).then((res) => {
            tr.addClass('read');
          });
        }, 2000);
      }


      $("body").addClass('sidebar-collapse').trigger('collapsed.pushMenu');
      $('.email-display').addClass('col-md-8').css('display', 'flex').find('.box-title').html(tr.data('title'));
      $('.email-list').removeClass('col-md-12').addClass('col-md-4');
      $('#html-content .no-content').remove();
      if (html.replace(/\s/g,'').length > 0) {
        const iframe = $('#email-display-iframe');
        iframe.contents().find('html').html(html);
        iframe.show();
      } else {
        $('#email-display-iframe').hide();
        $('#html-content').append('<div class="no-content"><p>Aucun contenu HTML pour cet email</p></div>');
      }
      if (text.replace(/\s/g,'').length > 0) {
        $('#text-content').html('<div class="email-text-content"><p>'+text.replace(/\n/gi, "<br>\n")+'</p></div>');
      } else {
        $('#text-content').html('<div class="no-content"><p>Aucun contenu texte pour cet email</p></div>');
      }
      $('#raw-content').html('<div class="email-text-content"><p>'+tr.find('script.raw-email').html().replace(/\n/gi, "<br>\n")+'</p></div>');

      const footer = $('.email-display .box-footer');
      footer.html('');
      footer.hide();
      if (attach.length > 0) {
        footer.show();
        attach.each((i, item) => {
          const $item = $(item);
          footer.append($item.clone());
        });
      }
    })
      .on('click', '.email-close', (ev) => {
        $('.email-display').removeClass('col-md-8').css('display', 'none');
        $('.email-list').addClass('col-md-12').removeClass('col-md-4');
        $('.mailbox-firstline').show();
        $("body").removeClass('sidebar-collapse').trigger('expanded.pushMenu');
        const footer = $('.email-display .box-footer');
        footer.html('');
        footer.hide();
    });

    setInterval(() => {
      const lastEmailId = $('.mailbox-messages table tbody > tr').first().data('id');
      $.get('emails/new/' + lastEmailId).then((res) => {
        console.log(res);
        $('.mailbox-messages table tbody').prepend(res);
        $('.mailbox-messages input[type="checkbox"]').iCheck({
          checkboxClass: 'icheckbox_flat-blue',
          radioClass: 'iradio_flat-blue'
        });

      })
    }, 10000)
  });

})(jQuery);
