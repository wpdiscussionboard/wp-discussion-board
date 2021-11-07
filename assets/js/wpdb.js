(function($, config) {

    $(document).ready(function(){
        init();
    });

    function init(){
        // edit the post
        $('.wpdb-post-edit a').on('click', function(e){
            e.preventDefault();

            $('#topic_edit_form').remove();
            $('<form id="topic_edit_form" action="' + config.edit_post_url + '" method="post"><input type="hidden" name="topic" value="' + $(this).attr('data-topic-id') + '"></form>').appendTo('body');
            $('#topic_edit_form').trigger('submit');
        });

        // edit the content
        $('.ctdb-edit-link a').on('click', function(e){
            e.preventDefault();

            var $commentID = $(this).attr('data-comment-id');
            $('.comment-content-text[data-comment-id=' + $commentID + ']').hide();
            $('.comment-content-edit[data-comment-id=' + $commentID + ']').show();
            $('.comment-content-edit[data-comment-id=' + $commentID + '] button').on('click', function(e){
                var $_textarea = $('.comment-content-edit[data-comment-id=' + $commentID + '] textarea');
                var $commentText = $_textarea.length > 0 ? $_textarea.val() : '';
                $.ajax({
                    url: config.edit_comment_url + $commentID,
                    method: 'POST',
                    beforeSend: function ( xhr ) {
                        xhr.setRequestHeader( 'X-WP-Nonce', config.rest_nonce );
                    },
                    data: {
                        content: $commentText
                    },
                    success: function(data){
                        $('.comment-content-text[data-comment-id=' + $commentID + ']').html(data.content.rendered).show();
                        $('.comment-content-edit[data-comment-id=' + $commentID + ']').hide();
                    }
                });
            });
        });
    }

})(jQuery, wpdb_config);