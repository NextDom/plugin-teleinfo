$( document ).on( "click", ".btn", function() {
  var temp = $(this).attr("btnid")
  $(".result"+temp+"").append('<i class="fa fa-spinner fa-spin"></i>');
  $.ajax({
  async:true,
  global : false,
  url: 'plugins/teleinfo/core/ajax/teleinfo.ajax.php',
  data: {
      action:'diagnostic_step' + temp
      },
  dataType: 'json',
  error: function (request, status, error) {
      console.log(request);
      handleAjaxError(request, status, error,$('#div_DiagnosticAlert'));
  },
  success: function (data) {
      console.log(data);
      if (data.result.result === '0'){
          $('.advise'+temp).show();
          $(".result"+temp+"").empty().append('<i class="fa fa-times" style="color:#FF0000"></i> ');
      }
      else if (data.result.result === '1'){
          $(".result"+temp+"").empty().append('<i class="fa fa-check" style="color:#00FF00"></i> ');
      }else {
          $(".result"+temp+"").empty();
      }
      $(".result"+temp+"").append(data.result.message);
  }
  });
});

function check_state(name,  datetime){
    return '<span  class="label label-danger" style="font-size : 1em;">NOK</span>';
}

//populate_table();



$(document).ready(function () {
    var navListItems = $('div.setup-panel div a'),
    allWells = $('.setup-content'),
    allNextBtn = $('.nextBtn'),
    allReturnBtn = $('.returnBtn');
    allWells.hide();
    navListItems.click(function (e) {
        e.preventDefault();
        var $target = $($(this).attr('href')),
        $item = $(this);
        if (!$item.hasClass('disabled')) {
            navListItems.removeClass('btn-primary').addClass('btn-default');
            $item.addClass('btn-primary');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        }
    });
    allNextBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
        curStepBtn = curStep.attr("id"),
        nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
        curInputs = curStep.find("input[type='text'],input[type='url']"),
        isValid = true;
        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }
        if (isValid)
        nextStepWizard.removeAttr('disabled').trigger('click');
    });
    allReturnBtn.click(function(){
        var curStep = $(this).closest(".setup-content"),
        curStepBtn = curStep.attr("id"),
        returnStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().prev().children("a"),
        curInputs = curStep.find("input[type='text'],input[type='url']"),
        isValid = true;
        $(".form-group").removeClass("has-error");
        for(var i=0; i<curInputs.length; i++){
            if (!curInputs[i].validity.valid){
                isValid = false;
                $(curInputs[i]).closest(".form-group").addClass("has-error");
            }
        }
    });
    $('div.setup-panel div a.btn-primary').trigger('click');
});
$("#toStep2").click(function(){

});
$("#toStep5").click(function(){

});
$("#closeConfigureModal").click(function(){
    $('#md_modal').dialog( "close" );
});
