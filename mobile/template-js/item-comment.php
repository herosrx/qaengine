<script type="text/template" id="mobile_comment_item">

    <div class="row">
        <div class="col-md-3 col-xs-3">
            <a href="javascript:void(0)" class="avatar-cmt-in-cmt">
                {{= avatar }}
            </a>
            <p class="cmt-author">
                <a href="{{= author_url }}"  title="{{= author }}">
                    {{= author }}
                </a>
            </p>
        </div>
        <div class="col-md-9 col-xs-9">
            <div class="content-cmt-in-cmt">
            {{= content_filter }}
            <span class="time-cmt-in-cmt">{{= human_date }}. <!-- <a href="javascript:void(0)">Edit&nbsp;&nbsp;<i class="fa fa-pencil"></i></a> --></span>
            </div>
        </div>
        <div class="col-md-12">
            <div class="clearfix"></div>
            <div class="line" style="width:90%;"></div>
        </div>
    </div>

</script>