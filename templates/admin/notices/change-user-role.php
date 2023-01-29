<script type="text/javascript">
    (function($) {
        var customer_role = $('[data-name="wpd_client_site_permission"]');
        if (customer_role.length) {
            var key = customer_role.data('key');

            $('[name="acf[' + key + ']"]').on('change', function() {
                alert('IMPORTANT! Changing the clients permission will change the permission for ALL the websites of ALL your clients. Changing to Editor will cause all your clients to have only editor role accounts on their websites. Please note that doesn\'t affect the websites launched by administrators.');
            })
        }
    })(jQuery);
</script>
