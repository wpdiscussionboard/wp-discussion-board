(function($, config){
    $(document).ready(function(){
        init();
    });

    function init(){
        $('select[name="ctdb_user_settings[discussion_board_minimum_role][]"]').select2();
        $('select[name="ctdb_user_settings[minimum_user_roles][]"]').select2();
    }
})(jQuery, config);