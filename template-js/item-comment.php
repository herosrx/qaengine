<script type="text/template" id="comment_item">

    <div class="col-md-2 comment-avatar">
        {{= avatar }}
        <p class="cmt-author">
            <a href="{{= author_url }}" title="{{= author }}">
                {{= author }}
            </a>
        </p>
    </div>
    <div class="col-md-10 comment-content">
        <div class="cm-content-wrap">
            <div class="cm-wrap">{{= content_filter }}</div>
            <span class="comment-time">{{= human_date }}<?php //_e( ' ago.', ET_DOMAIN ) ?></span> <span class="comment-edit"><a class="edit-comment" href="javascript:void(0)"><?php _e("Edit",ET_DOMAIN) ?> <i class="fa fa-pencil"></i></a><a class="delete-comment" href="javascript:void(0)">
                    <?php _e("Delete",ET_DOMAIN) ?> <i class="fa fa-times"></i>
                </a></span>
        </div><!-- END COMMENT CONTENT -->
        <div class="cm-content-edit collapse">
            <form class="edit-comment">
                <input type="hidden" name="qa_nonce" value="<?php echo wp_create_nonce( 'edit_comment' );?>" />
                <div class="wp-editor-container">
                    <textarea name="comment_content" id="edit_comment_{{= comment_ID }}"></textarea>
                </div>
                <div class="row submit-wrapper">
                    <div class="col-md-3">
                        <button id="submit_reply" class="btn-submit"><?php _e("Update",ET_DOMAIN) ?></button>
                    </div>
                    <div class="col-md-3">
                        <a href="javascript:void(0)" class="cancel-comment"><?php _e("Cancel",ET_DOMAIN) ?></a>
                    </div>
                </div>
            </form>
        </div><!-- END EDIT COMMENT FORM -->
    </div>

</script>