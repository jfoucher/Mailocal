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
    $(".checkbox-toggle").click(function () {
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
    })

    $('body').on('click', '.mailbox-messages table tbody > tr', (ev) => {
      const tr = $(ev.currentTarget);
      const html = tr.find('script.html-email');
      $('.email-display').addClass('col-md-8').css('display', 'flex').find('.box-title').text(tr.find('.mailbox-subject > b').text());
      $('.email-list').removeClass('col-md-12').addClass('col-md-4').find('td > a').addClass('max-80');
      $('#email-display-iframe').contents().find('html').html(html.html())
      $('.mailbox-firstline').hide();
    })
      .on('click', '.email-close', (ev) => {
      $('.email-display').removeClass('col-md-8').css('display', 'none');
      $('.email-list').addClass('col-md-12').removeClass('col-md-4').find('td > a').removeClass('max-80');
      $('.mailbox-firstline').show();
    })
  });

})(jQuery);
