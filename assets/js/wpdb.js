(function($, config) {

    $(document).ready(function(){
        init();
    });

    function init(){
        $('.wpdb-post-edit a').on('click', function(e){
            e.preventDefault();

            $('#topic_edit_form').remove();
            $('<form id="topic_edit_form" action="' + config.edit_post_url + '" method="post"><input type="hidden" name="topic" value="' + $(this).attr('data-topic-id') + '"></form>').appendTo('body');
            $('#topic_edit_form').trigger('submit');
        });
    }

})(jQuery, wpdb_config);