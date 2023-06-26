(function ($) {






   function init() {

       (function ($) {
           // Retrieve the ACF field
           var field = acf.getField('field_612616dc483456frerf24rnjgnjk64');

           // Add a change event listener to the field
           field.on('change', function () {
               var value = field.val();
               toggleFieldGroup(value);
           });

           // Toggle the visibility of the field group based on the field value
           function toggleFieldGroup(value) {

               var fieldGroup = $('#acf-group_5afc7b8e22840 .acf-field:not(.dol-always-show),.acf-tab-wrap');

               if (value) {
                   fieldGroup.show();
               } else {
                   fieldGroup.hide();
               }
           }

           // Trigger the initial toggle based on the field value
           var initialValue = field.val();
           toggleFieldGroup(initialValue);
       })(jQuery);

    }

    jQuery(setTimeout.bind(window, init));


})(jQuery);
