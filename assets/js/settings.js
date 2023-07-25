(function($, config){
    $(document).ready(function(){
        init();
    });

    function init(){
        $('select[name="ctdb_user_settings[discussion_board_minimum_role][]"], select[name="ctdb_user_settings[minimum_user_roles][]"]').select2({
            width: '300px'
        });
    }
})(jQuery, config);