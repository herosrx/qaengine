<?php
define('ADMIN_PATH', TEMPLATEPATH . '/admin');

/**
 * Handle admin features
 * Adding admin menus
 */
class QA_Admin extends QA_Engine
{

    function __construct() {
        parent::__construct();
        $this->add_action('after_setup_theme', 'admin_setup');

        $ajax_classes = apply_filters('et_ajax_classes', array(
            'QA_Ajax',
            'ET_UserAjax',
        ));

        if (isset($_REQUEST['page'])) {
            $this->add_action('admin_print_footer_scripts', 'override_template_setting', 200);
        }

        foreach ((array)$ajax_classes as $class) {
            if (class_exists($class)) new $class();
        }

        $this->add_ajax('ae-reset-option', 'reset_option');

        /**
         * set default options
         */
        $options = AE_Options::get_instance();
        if (!$options->init) $options->reset($this->get_default_options());

        // default badges array
        $default_badges = array(
            'pos_badges' => array(
                'create_question' => 2,
                'post_answer'     => 2,
                'q_vote_up'       => 5,
                'a_vote_up'       => 10,
                'a_accepted'      => 15,
            ) ,
            'neg_badges' => array(
                'q_vote_down' => - 2,
                'a_vote_down' => - 2,
                'vote_down'   => - 2,
            ) ,
            'privileges' => array(
                'create_post'   => 1,
                'vote_up'       => 20,
                'add_comment'   => 200,
                'vote_down'     => 500,
                'edit_qa'       => 1200,
                'create_tag'    => 1500,
                'edit_question' => 2000,
                'edit_answer'   => 40000,
            ) ,
            'init' => 1
        );

        /**
         * set default badge options
         */
        $options = QA_BadgeOptions::get_instance();
        if (!$options->init) $options->reset($default_badges);
    }
    function override_template_setting() {
    ?>
        <!-- override underscore template settings -->
        <script type="text/javascript">
            _.templateSettings = {
                evaluate: /\<\#(.+?)\#\>/g,
                interpolate: /\{\{=(.+?)\}\}/g,
                escape: /\{\{-(.+?)\}\}/g
            };
        </script>
        <!-- localize validator -->
    <?php
    }
    /**
     * ajax function reset option
     */
    function reset_option() {

        $option_name = $_REQUEST['option_name'];
        $default_options = $this->get_default_options();

        if (isset($default_options[$option_name])) {
            $options = AE_Options::get_instance();
            $options->$option_name = $default_options[$option_name];
            wp_send_json(array(
                'msg' => $default_options[$option_name]
            ));
        }
    }

    /**
     * retrieve site default options
     */
    function get_default_options() {

        return array(
            'blogname'                       => get_option('blogname') ,

            'blogdescription'                => get_option('blogdescription') ,

            // default forgot passmail
            'forgotpass_mail_template'       => '<p>Hello [display_name],</p>
                                                <p>You have just sent a request to recover the password associated with your account in [blogname]. If you did not make this request, please ignore this email; otherwise, click the link below to create your new password:</p>
                                                <p>[activate_url]</p>
                                                <p>Regards,<br />[blogname]</p>',

            // default register mail template
            'register_mail_template'         => '<p>Hello [display_name],</p>
                                                <p>You have successfully registered an account with &nbsp;&nbsp;[blogname].&nbsp;Here is your account information:</p>
                                                <ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol>
                                                <p>Thank you and welcome to [blogname].</p>',

            // default confirm mail template
            'confirm_mail_template'          => '<p>Hello [display_name],</p>
                                                <p>You have successfully registered an account with &nbsp;&nbsp;[blogname].&nbsp;Here is your account information:</p>
                                                <ol><li>Username: [user_login]</li><li>Email: [user_email]</li></ol>
                                                <p>Please click the link below to confirm your email address.</p>
                                                <p>[confirm_link]</p><p>Thank you and welcome to [blogname].</p>',

            // default confirmed mail template
            'confirmed_mail_template'        => "<p>Hi [display_name],</p>
                                                <p>Your email address has been successfully confirmed.</p>
                                                <p>Thank you and welcome to [blogname].</p>",

            // default accepted answer mail template
            'accept_answer_mail_template'    => "<p>Hi [display_name],</p>
                                                <p>Your answer has been [action] as the best answer.</p>
                                                <p>You can view your answer by visit this link:[question_link],</p>
                                                <p>Sincerely,<br />[blogname]</p>",

            //  default reset pass mail template
            'resetpass_mail_template'        => "<p>Hello [display_name],</p>
                                                <p>You have successfully changed your password. Click this link &nbsp;[site_url] to login to your [blogname]'s account.</p>
                                                <p>Sincerely,<br />[blogname]</p>",

            'init'                           => 1,

            //  default alert new answer mail template
            'new_answer_mail_template'       => '<p>Hello [display_name],</p>
                                                <p>The question <strong>[question_title]</strong> you are following has a new answer. </p>
                                                <p>Click the link below to view the question.</p>
                                                <p>[question_link]</p>
                                                <p>Sincerely,<br />[blogname]</p>',

            //  default alert report mail template
            'report_mail_template'           => '<p>Hello [display_name],</p>
                                                <p>You have a new  report from:  [blogname]</p>
                                                <p>Question content :</p>
                                                <p> [thread_content]</p>
                                                <p>Report message:</p>
                                                <p> [report_message]</p>
                                                <p>Question link:</p>
                                                <p> [thread_link]</p>
                                                <p>Sincerely,<br />[blogname]</p>',

            //  default pending question mail template
            'pending_question_mail_template' => '<p>Hello Admin,</p>
                                                <p>The new pending question <strong>[question_title]</strong> is waiting for your approval. </p>
                                                <p>Without your acceptance, it won’t be displayed in the site. Please check out the following link to approve the question.</p>
                                                <p>[pending_question_link]</p>
                                                <p>Sincerely,<br />[blogname]</p>',
            //  default pending question mail template
            'best_answer_mail_template'      =>   '<p>Hi [display_name],</p><p>Your answer has been [action] as the best answer.</p><p>You can view your answer by visit this link:[question_link],</p><p>Sincerely,<br />[blogname]</p>',
            // default ban email template
            'ban_mail_template'              => '<p>Hello [display_name],</p><p>You have been banned from [blogname] for reason:</p><p>[reason]</p><p>Your ban will be expired on [ban_expired]</p><p>Please contact our staff for more information</p><p>Sincerely,<br />[blogname]</p>',
            // default inbox email template
            'inbox_mail_template'              => '<p>Hi [display_name],</p><p>You have just received the following message from user:[sender]</p><p>|==============================================================|</p>[message]<p>|==============================================================|</p><p>Sincerely,<br />[blogname]</p>',
        );
    }

    /**
     * render admin setup
     */
    function admin_setup() {

        $sections = array();

        /**
         * general settings section
         */
        $sections[] = array(
            'args' => array(
                'title' => __("General", ET_DOMAIN) ,
                'id' => 'general-settings',
                'icon' => 'y',
                'class' => ''
            ) ,
            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("Website Title", ET_DOMAIN) ,
                        'id' => 'site-name',
                        'class' => '',
                        'desc' => __("Enter your website title.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'blogname',
                            'type' => 'text',
                            'title' => __("Website Title", ET_DOMAIN) ,
                            'name' => 'blogname',
                            'class' => 'option-item bg-grey-input '
                        )
                    )
                ) ,

                array(
                    'args' => array(
                        'title' => __("Website Description", ET_DOMAIN) ,
                        'id' => 'site-description',
                        'class' => '',
                        'desc' => __("Enter your website description", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'blogdescription',
                            'type' => 'text',
                            'title' => __("Website Title", ET_DOMAIN) ,
                            'name' => 'blogdescription',
                            'class' => 'option-item bg-grey-input '
                        )
                    )
                ) ,
                array(
                    'args' => array(
                        'title' => __("Copyright", ET_DOMAIN) ,
                        'id' => 'site-copyright',
                        'class' => '',
                        'desc' => __("This copyright information will appear in the footer.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'copyright',
                            'type' => 'text',
                            'title' => __("Copyright", ET_DOMAIN) ,
                            'name' => 'copyright',
                            'class' => 'option-item bg-grey-input '
                        )
                    )
                ) ,
                array(
                    'args' => array(
                        'title' => __("Google Analytics Script", ET_DOMAIN) ,
                        'id' => 'site-analytics',
                        'class' => '',
                        'desc' => __("Google analytics is a service offered by Google that generates detailed statistics about the visits to a website.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'opt-ace-editor-js',
                            'type' => 'textarea',
                            'title' => __("Google Analytics Script", ET_DOMAIN) ,
                            'name' => 'google_analytics',
                            'class' => 'option-item bg-grey-input '
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Email Confirmation", ET_DOMAIN) ,
                        'id' => 'user-confirm',
                        'class' => '',
                        'desc' => __("Enabling this will require users to confirm their email addresses after registration.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'user_confirm',
                            'type' => 'switch',
                            'title' => __("User Confirm", ET_DOMAIN) ,
                            'name' => 'user_confirm',
                            'class' => ''
                        )
                    )
                ),
            )
        );
        /**
         * social settings section
         */
        $sections[] = array(
            'args' => array(
                'title' => __("Social", ET_DOMAIN) ,
                'id' => 'social-settings',
                'icon' => 'B',
                'class' => ''
            ) ,
            'groups' => array(
                 array(
                    'args' => array(
                        'title' => __("Twitter API", ET_DOMAIN) ,
                        'id' => 'twitter-api',
                        'class' => '',
                        'desc' => __("Enabling this will allow users to login via Twitter.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'twitter_login',
                            'type' => 'switch',
                            'title' => __("Twitter API ", ET_DOMAIN) ,
                            'name' => 'twitter_login',
                            'class' => ''
                        ),
                        array(
                            'id' => 'et_twitter_key',
                            'type' => 'text',
                            'title' => __("Twitter key ", ET_DOMAIN) ,
                            'name' => 'et_twitter_key',
                            'placeholder' => __("Twitter Consumer Key", ET_DOMAIN) ,
                            'class' => '',
                        ),
                         array(
                            'id' => 'et_twitter_secret',
                            'type' => 'text',
                            'title' => __("Twitter secret ", ET_DOMAIN) ,
                            'name' => 'et_twitter_secret',
                            'placeholder' => __("Twitter Consumer Secret", ET_DOMAIN) ,
                            'class' => '',
                        )
                    ),
                ),
                array(
                    'args' => array(
                        'title' => __("Facebook API", ET_DOMAIN) ,
                        'id' => 'facebook-api',
                        'class' => '',
                        'desc' => __("Enabling this will allow users to login via Facebook.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'facebook_login',
                            'type' => 'switch',
                            'title' => __("Facebook API ", ET_DOMAIN) ,
                            'name' => 'facebook_login',
                            'class' => ''
                        ),
                        array(
                            'id' => 'et_facebook_key',
                            'type' => 'text',
                            'title' => __("Facebook key ", ET_DOMAIN) ,
                            'name' => 'et_facebook_key',
                            'placeholder' => __("Facebook Application ID", ET_DOMAIN) ,
                            'class' => ''
                        ),
                    ),
                ),
                 array(
                    'args' => array(
                        'title' => __("Google API", ET_DOMAIN) ,
                        'id' => 'google-api',
                        'class' => '',
                        'desc' => __("Enabling this will allow users to login via Google.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'gplus_login',
                            'type' => 'switch',
                            'title' => __("Google API ", ET_DOMAIN) ,
                            'name' => 'gplus_login',
                            'class' => ''
                        ),
                        array(
                            'id' => 'gplus_client_id',
                            'type' => 'text',
                            'title' => __("Google key ", ET_DOMAIN) ,
                            'name' => 'gplus_client_id',
                            'placeholder' => __("Client ID", ET_DOMAIN) ,
                            'class' => ''
                        ),
                    ),
                ),

            )
        );
        /**
         * content settings section
         */
        $sections[] = array(
            'args' => array(
                'title' => __("Content", ET_DOMAIN) ,
                'id'    => 'content-settings',
                'icon'  => 'l',
                'class' => ''
            ) ,
            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("Filter Bad Words ( Questions / Answers )", ET_DOMAIN) ,
                        'id'    => 'filter_keywords',
                        'class' => '',
                        'desc'  => __("Each word seperate by comma (,)", ET_DOMAIN),
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'filter_keywords',
                            'type'  => 'textarea',
                            'title' => __("Enter Keywords Here", ET_DOMAIN) ,
                            'name'  => 'filter_keywords',
                            'class' => 'option-item bg-grey-input',
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Intro Text", ET_DOMAIN) ,
                        'id'    => 'intro-text',
                        'class' => '',
                        'desc'  => __("This text will appear in the intro page.", ET_DOMAIN),
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'intro-heading',
                            'type'  => 'text',
                            'title' => __("Intro Slide Texts", ET_DOMAIN) ,
                            'name'  => 'intro_slide_text',
                            'class' => 'option-item bg-grey-input ',
                        ),
                        array(
                            'id'    => 'text-bottom',
                            'type'  => 'text',
                            'title' => __("Intro Bottom Texts", ET_DOMAIN) ,
                            'name'  => 'intro_bottom_text',
                            'class' => 'option-item bg-grey-input ',
                        )
                    )
                ),

                array(
                    'args'  => array(
                        'title' => __("Custom Slugs", ET_DOMAIN) ,
                        'id'    => 'intro-text',
                        'class' => '',
                        'desc'  => __("Add custom slugs for question & category here. (You need to save permalink structure to apply this change.)", ET_DOMAIN),
                    ) ,

                    'fields' => array(
                        array(
                            'id'          => 'question-slug',
                            'type'        => 'text',
                            'title'       => __("Question Slug", ET_DOMAIN) ,
                            'placeholder' => __("Question Slug", ET_DOMAIN) ,
                            'name'        => 'question_slug',
                            'class'       => 'option-item bg-grey-input ',
                        ),
                        array(
                            'id'          => 'category-slug',
                            'type'        => 'text',
                            'title'       => __("Category Slug", ET_DOMAIN) ,
                            'placeholder' => __("Category Slug", ET_DOMAIN) ,
                            'name'        => 'category_slug',
                            'class'       => 'option-item bg-grey-input ',
                        ),
                        array(
                            'id'          => 'tag-slug',
                            'type'        => 'text',
                            'title'       => __("Tag Slug", ET_DOMAIN) ,
                            'placeholder' => __("Tag Slug", ET_DOMAIN) ,
                            'name'        => 'tag_slug',
                            'class'       => 'option-item bg-grey-input ',
                        )

                    )
                ),
                array(
                    'args'  => array(
                        'title' => __("Editor Upload Images", ET_DOMAIN) ,
                        'id'    => 'upload-images',
                        'class' => '',
                        'desc'  => __("Turn on / off Feature Images Upload in Editor.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'upload-images',
                            'type'  => 'switch',
                            'title' => __("Upload Images", ET_DOMAIN) ,
                            'name'  => 'ae_upload_images',
                            'class' => ''
                        )
                    )
                ),
                array(
                    'args'  => array(
                        'title' => __("Login To View Content", ET_DOMAIN) ,
                        'id'    => 'login-view',
                        'class' => '',
                        'desc'  => __("If you enable this option, users have to login to see content.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'login-view-content',
                            'type'  => 'switch',
                            'title' => __("Login", ET_DOMAIN) ,
                            'name'  => 'login_view_content',
                            'class' => ''
                        )
                    )
                ),

                array(
                    'args'  => array(
                        'title' => __("Pending Questions", ET_DOMAIN) ,
                        'id'    => 'pending-questions',
                        'class' => '',
                        'desc'  => __("If you enable this option, the new posted questions have to be approved to be displayed.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'pending-questions-field',
                            'type'  => 'switch',
                            'title' => __("Pending Questions", ET_DOMAIN) ,
                            'name'  => 'pending_questions',
                            'class' => ''
                        )
                    )
                ),

                array(
                    'args'  => array(
                        'title' => __("Pending Answers", ET_DOMAIN) ,
                        'id'    => 'pending-answers',
                        'class' => '',
                        'desc'  => __("If you enable this option, the new posted answers have to be approved to be displayed.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'pending-answers-field',
                            'type'  => 'switch',
                            'title' => __("Pending Answers", ET_DOMAIN) ,
                            'name'  => 'pending_answers',
                            'class' => ''
                        )
                    )
                ),

                array(
                    'args'  => array(
                        'title' => __("Email Notification For Followed Questions", ET_DOMAIN) ,
                        'id'    => 'following-questions',
                        'class' => '',
                        'desc'  => __("If you enable this option, whenever there’s a new answer for the followed questions, the system will automatically send emails to the following users.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'following-quesitons-field',
                            'type'  => 'switch',
                            'title' => __("Send Mail Following", ET_DOMAIN) ,
                            'name'  => 'qa_send_following_mail',
                            'class' => ''
                        )
                    )
                ),

                array(
                    'args'  => array(
                        'title' => __("Live Notifications", ET_DOMAIN) ,
                        'id'    => 'live-notifications',
                        'class' => '',
                        'desc'  => __("Turn on / off live notifications feature.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'live-notifications',
                            'type'  => 'switch',
                            'title' => __("Live Notifications", ET_DOMAIN) ,
                            'name'  => 'qa_live_notifications',
                            'class' => ''
                        )
                    )
                ),
            )
        );
        /**
         * branding section
         */
        $sections[] = array(

            'args' => array(
                'title' => __("Branding", ET_DOMAIN) ,
                'id'    => 'branding-settings',
                'icon'  => 'b',
                'class' => ''
            ) ,

            'groups' => array(
                array(
                    'args'  => array(
                        'title' => __("Site logo", ET_DOMAIN) ,
                        'id'    => 'site-logo',
                        'class' => '',
                        'name'  => '',
                        'desc'  => __("Your logo should be in PNG, GIF or JPG format, within 150x50px and less than 1500Kb.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id'    => 'opt-ace-editor-js',
                            'type'  => 'image',
                            'title' => __("Site Logo", ET_DOMAIN) ,
                            'name'  => 'site_logo',
                            'class' => '',
                            'size'  => array(
                                '150',
                                '50'
                            )
                        )
                    )
                ) ,

                array(
                    'args'  => array(
                        'title' => __("Mobile Icon", ET_DOMAIN) ,
                        'id'    => 'mobile-icon',
                        'class' => '',
                        'name'  => '',
                        'desc'  => __("This icon will be used as a launcher icon for iPhone and Android smartphones and also as the website favicon. The image dimensions should be 57x57px.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'opt-ace-editor-js',
                            'type' => 'image',
                            'title' => __("Mobile Icon", ET_DOMAIN) ,
                            'name' => 'mobile_icon',
                            'class' => '',
                            'size' => array(
                                '57',
                                '57'
                            )
                        )
                    )
                ),

                array(
                    'args' => array(
                        'title' => __("Intro Page Background", ET_DOMAIN) ,
                        'id' => 'intro-background',
                        'class' => '',
                        'name' => '',
                        'desc' => __("This image will be used as a background image in Intro Page. The image dimensions should be 1400x700px or more and size less than 1000KB.", ET_DOMAIN)
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'opt-ace-editor-js',
                            'type' => 'image',
                            'title' => __("Intro Page Background", ET_DOMAIN) ,
                            'name' => 'intro_background',
                            'class' => '',
                            'size' => array(
                                '140',
                                '70'
                            )
                        )
                    )
                ),
            )
        );

        /**
         * mail template settings section
         */
        $sections[] = array(
            'args' => array(
                'title' => __("Mailing", ET_DOMAIN) ,
                'id' => 'mail-settings',
                'icon' => 'M',
                'class' => ''
            ) ,

            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("Authentication Mail Template", ET_DOMAIN) ,
                        'id'    => 'mail-description-group',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'mail-description',
                            'type'  => 'desc',
                            'title' => __("Mail description here", ET_DOMAIN) ,
                            'text'  => __("Email templates for authentication process. You can use placeholders to include some specific content.", ET_DOMAIN) . '<a class="icon btn-template-help payment" data-icon="?" href="javascript:void(0)" title="View more details"></a>' . '<div class="cont-template-help payment-setting">
                            [user_login],[display_name],[user_email] : ' . __("user's details you want to send mail", ET_DOMAIN) . '<br />
                            [dashboard] : ' . __("member dashboard url ", ET_DOMAIN) . '<br />
                            [title], [link], [excerpt],[desc] : ' . __("question title, link and details", ET_DOMAIN) . ' <br />
                            [activate_url] : ' . __("activate link is require for user to renew their pass", ET_DOMAIN) . ' <br />
                            [site_url],[blogname],[admin_email] : ' . __(" site info, admin email", ET_DOMAIN) . '
                            </div>',
                            
                            'class' => '',
                            'name'  => 'mail_description'
                        )
                    )
                ) ,
                array(
                    'args' => array(
                        'title' => __("Register Mail Template", ET_DOMAIN) ,
                        'id'    => 'register-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'register_mail_template',
                            'type'  => 'editor',
                            'title' => __("Register Mail", ET_DOMAIN) ,
                            'name'  => 'register_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ) ,

                array(
                    'args' => array(
                        'title' => __("Confirmation Mail Template", ET_DOMAIN) ,
                        'id'    => 'confirm-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'confirm_mail_template',
                            'type'  => 'editor',
                            'title' => __("Confirmation Mail", ET_DOMAIN) ,
                            'name'  => 'confirm_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ) ,

                array(
                    'args' => array(
                        'title' => __("Confirmed Mail Template", ET_DOMAIN) ,
                        'id'    => 'confirmed-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'confirmed_mail_template',
                            'type'  => 'editor',
                            'title' => __("Confirmation Mail", ET_DOMAIN) ,
                            'name'  => 'confirmed_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ) ,

                array(
                    'args' => array(
                        'title' => __("Forgotpass Mail Template", ET_DOMAIN) ,
                        'id'    => 'forgotpass-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'forgotpass_mail_template',
                            'type'  => 'editor',
                            'title' => __("Register Mail", ET_DOMAIN) ,
                            'name'  => 'forgotpass_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ) ,
                array(
                    'args' => array(
                        'title' => __("Resetpass Mail Template", ET_DOMAIN) ,
                        'id'    => 'resetpass-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'resetpass_mail_template',
                            'type'  => 'editor',
                            'title' => __("Resetpassword Mail", ET_DOMAIN) ,
                            'name'  => 'resetpass_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Pending Questions Mail Template", ET_DOMAIN) ,
                        'id'    => 'pending-questions-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'pending_question_mail_template',
                            'type'  => 'editor',
                            'title' => __("Pending Questions Mail", ET_DOMAIN) ,
                            'name'  => 'pending_question_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("New Answer Mail Template", ET_DOMAIN) ,
                        'id'    => 'new-answer-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'new_answer_mail_template',
                            'type'  => 'editor',
                            'title' => __("New Answer Mail", ET_DOMAIN) ,
                            'name'  => 'new_answer_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Report Mail Template", ET_DOMAIN) ,
                        'id'    => 'report-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'report_mail_template',
                            'type'  => 'editor',
                            'title' => __("Report Mail", ET_DOMAIN) ,
                            'name'  => 'report_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Best Answer Mail Template", ET_DOMAIN) ,
                        'id'    => 'best-answer-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'best_answer_mail_template',
                            'type'  => 'editor',
                            'title' => __("Best Answer Mail", ET_DOMAIN) ,
                            'name'  => 'best_answer_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Ban User Mail Template", ET_DOMAIN) ,
                        'id'    => 'ban-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'ban_mail_template',
                            'type'  => 'editor',
                            'title' => __("Ban User Mail", ET_DOMAIN) ,
                            'name'  => 'ban_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ),
                array(
                    'args' => array(
                        'title' => __("Inbox Mail Template", ET_DOMAIN) ,
                        'id'    => 'inbox-mail',
                        'class' => '',
                        'name'  => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id'    => 'inbox_mail_template',
                            'type'  => 'editor',
                            'title' => __("Inbox User Mail", ET_DOMAIN) ,
                            'name'  => 'inbox_mail_template',
                            'class' => '',
                            'reset' => 1
                        )
                    )
                ),
            )
        );

        /**
         * language settings
         */
        $sections[] = array(
            'args' => array(
                'title' => __("Language", ET_DOMAIN) ,
                'id' => 'language-settings',
                'icon' => 'G',
                'class' => ''
            ) ,

            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("Website Language", ET_DOMAIN) ,
                        'id' => 'website-language',
                        'class' => '',
                        'name' => '',
                        'desc' => __("Select the language you want to use for your website.", ET_DOMAIN)
                    ) ,
                    'fields' => array(
                        array(
                            'id' => 'forgotpass_mail_template',
                            'type' => 'language_list',
                            'title' => __("Register Mail", ET_DOMAIN) ,
                            'name' => 'website_language',
                            'class' => ''
                        )
                    )
                ) ,
                array(
                    'args' => array(
                        'title' => __("Translator", ET_DOMAIN) ,
                        'id' => 'translator',
                        'class' => '',
                        'name' => 'translator',
                        'desc' => __("Translate a language", ET_DOMAIN)
                    ) ,
                    'fields' => array(
                        array(
                            'id' => 'translator-field',
                            'type' => 'translator',
                            'title' => __("Register Mail", ET_DOMAIN) ,
                            'name' => 'translate',
                            'class' => ''
                        )
                    )
                )
            )
        );

        /**
         * language settings
         */
        $sections[] = array(
            'args' => array(
                'title' => __("Update", ET_DOMAIN) ,
                'id' => 'update-settings',
                'icon' => '~',
                'class' => ''
            ) ,

            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("License Key", ET_DOMAIN) ,
                        'id' => 'license-key',
                        'class' => '',
                        'desc' => ''
                    ) ,
                    'fields' => array(
                        array(
                            'id' => 'et_license_key',
                            'type' => 'text',
                            'title' => __("License Key", ET_DOMAIN) ,
                            'name' => 'et_license_key',
                            'class' => ''
                        )
                    )
                )
            )
        );

        $temp = array();
        $options = AE_Options::get_instance();
        foreach ($sections as $key => $section) {
            $temp[] = new AE_section($section['args'], $section['groups'], $options);
        }

        $pages = array();

        /**
         * overview container
         */
        $container = new AE_Overview( array('question' , 'answer') );

        //$statics		=	array();
        // $header		=	new AE_Head( array(	'page_title' 	=> __('Overview', ET_DOMAIN),
        // 									'menu_title' 	=> __('OVERVIEW', ET_DOMAIN),
        // 									'desc'			=> __("Overview", ET_DOMAIN) ) );
        $pages[] = array(
            'args' => array(
                'parent_slug' => 'et-overview',
                'page_title'  => __('Overview', ET_DOMAIN) ,
                'menu_title'  => __('OVERVIEW', ET_DOMAIN) ,
                'cap'         => 'administrator',
                'slug'        => 'et-overview',
                'icon'        => 'menu-overview',
                'desc'        => sprintf(__("%s overview", ET_DOMAIN) , $options->blogname)
            ) ,
            'container' => $container,

            // 'header'	=> $header

        );

        /**
         * setting view
         */
        $container = new AE_Container(array(
            'class' => '',
            'id'    => 'settings'
        ) , $temp, '');

        /**
         * page overview
        */
        $pages[] = array(
            'args' => array(
                'parent_slug' => 'et-overview',
                'page_title'  => __('Settings', ET_DOMAIN) ,
                'menu_title'  => __('SETTINGS', ET_DOMAIN) ,
                'cap'         => 'administrator',
                'slug'        => 'et-settings',
                'icon'        => 'gear',
                'desc'        => __("Manage how your Q&A Engine looks and feels", ET_DOMAIN)
            ) ,
            'container' => $container
        );

        /**
         * user list view
         */

        $container = new AE_UsersContainer(array(
            'filter' => array(
                'moderate'
            ),
            'id' => 'users_container'
        ));
        /**
         * page user list
        */
        $pages[] = array(
            'args' => array(
                'parent_slug' => 'et-overview',
                'page_title' => __('Members', ET_DOMAIN) ,
                'menu_title' => __('MEMBERS', ET_DOMAIN) ,
                'cap' => 'administrator',
                'slug' => 'et-users',
                'icon' => 'users',
                'desc' => __("Overview of registered members", ET_DOMAIN)
            ) ,
            'container' => $container
        );
        $badge = array();

        /**
         * user badges view
         */

        $temp = array();
        $sections = array();
        $sections[] = array(
            'args' => array(
                'title' => __("Points", ET_DOMAIN) ,
                'id' => 'badge-active',
                'icon' => 'W',
                'class' => ''
            ) ,

            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("Positive Point", ET_DOMAIN) ,
                        'id' => 'active-point',
                        'class' => '',
                        'desc' => __("Set up points that users can gain", ET_DOMAIN) ,
                        'name' => 'pos_badges'
                    ) ,

                    'fields' => array(
                        array(
                            'id' => 'q-vote-up',
                            'type' => 'text',
                            'title' => __("create a question", ET_DOMAIN) ,
                            'name' => 'create_question',
                            'class' => '',
                            'label' => __("create a question", ET_DOMAIN)
                        ) ,
                        array(
                            'id' => 'q-vote-up',
                            'type' => 'text',
                            'title' => __("answer a question", ET_DOMAIN) ,
                            'name' => 'post_answer',
                            'class' => '',
                            'label' => __("answer a question", ET_DOMAIN)
                        ) ,
                        array(
                            'id' => 'q-vote-up',
                            'type' => 'text',
                            'title' => __("question is voted up", ET_DOMAIN) ,
                            'name' => 'q_vote_up',
                            'class' => '',
                            'label' => __("question is voted up", ET_DOMAIN)
                        ) ,
                        array(
                            'id' => 'a-vote-up',
                            'type' => 'text',
                            'title' => __("answer is voted up", ET_DOMAIN) ,
                            'name' => 'a_vote_up',
                            'class' => '',
                            'label' => __("answer is voted up", ET_DOMAIN)
                        ) ,
                        array(
                            'id' => 'a-accepted',
                            'type' => 'text',
                            'title' => __("answer is marked 'accepted' ", ET_DOMAIN) ,
                            'name' => 'a_accepted',
                            'class' => '',
                            'label' => __("answer is marked 'accepted' ", ET_DOMAIN)
                        )
                         /*,
                        array(
                        'id'        => 'e-accepted',
                        'type'      => 'text',
                        'title'     => __("suggested edit is accepted", ET_DOMAIN),
                        'name'		=> 'e_accepted',
                        'class'		=> '',
                        'label'		=> __("suggested edit is accepted", ET_DOMAIN)
                        )*/
                    )
                ) ,

                array(
                    'args' => array(
                        'title' => __("Negative Point", ET_DOMAIN) ,
                        'id' => 'active-point',
                        'class' => '',
                        'desc' => __("Set up points that user can lose", ET_DOMAIN) ,
                        'name' => 'neg_badges'
                    ) ,
                    'fields' => array(
                        array(
                            'id' => 'q_vote_down',
                            'type' => 'text',
                            'title' => __("your question is voted down", ET_DOMAIN) ,
                            'name' => 'q_vote_down',
                            'class' => '',
                            'label' => __("your question is voted down", ET_DOMAIN)
                        ) ,
                        array(
                            'id' => 'a_vote_down',
                            'type' => 'text',
                            'title' => __("your answer is voted down", ET_DOMAIN) ,
                            'name' => 'a_vote_down',
                            'class' => '',
                            'label' => __("your answer is voted down", ET_DOMAIN)
                        ) ,

                        array(
                            'id' => 'vote_down',
                            'type' => 'text',
                            'title' => __("you vote down an answer or question", ET_DOMAIN) ,
                            'name' => 'vote_down',
                            'class' => '',
                            'label' => __("you vote down an answer or question", ET_DOMAIN)
                        )
                    )
                )
            ) ,
        );

        // privileges
        $privi = qa_privileges();
        $fields = array();
        foreach ($privi as $key => $value) {
            $fields[] = array(
                'id' => $key,
                'type' => 'text',
                'title' => '',
                'name' => $key,
                'class' => '',
                'label' => $value
            );
        }

        $sections[] = array(
            'args' => array(
                'title' => __("Privileges", ET_DOMAIN) ,
                'id' => 'badge-privileges',
                'icon' => 'K',
                'class' => ''
            ) ,

            'groups' => array(
                array(
                    'args' => array(
                        'title' => __("Privileges", ET_DOMAIN) ,
                        'id' => 'active-point',
                        'class' => '',
                        'desc' => __("Set up point users have to achieve to have specific privileges", ET_DOMAIN) ,
                        'name' => 'privileges'
                    ) ,

                    'fields' => $fields
                )
            )
        );

        $temp = array(
            new QA_SectionBadge(array(
                'title' => __("Level", ET_DOMAIN) ,
                'id' => 'badge-level',
                'icon' => 'S',
                'class' => ''
            ) , array() , '')
        );

        $options = QA_BadgeOptions::get_instance();
        foreach ($sections as $key => $section) {
            $temp[] = new AE_section($section['args'], $section['groups'], $options);
        }

        $container = new AE_Container(array(
            'class' => '',
            'id' => 'badge'
        ) , $temp, $options);

        /**
         * page badges
        */
        $pages[] = array(
            'args' => array(
                'parent_slug' => 'et-overview',
                'page_title' => __('User Badges', ET_DOMAIN) ,
                'menu_title' => __('USER BADGES', ET_DOMAIN) ,
                'cap' => 'administrator',
                'slug' => 'et-badge',
                'icon' => 'badge',
                'desc' => __("Manage what your members can do.", ET_DOMAIN)
            ) ,
            'container' => $container
        );

        /**
         *	filter pages config params so user can hook to here
         */
        $pages = apply_filters('ae_admin_menu_pages', $pages);

        /**
         * add menu page
         */
        $this->admin_menu = new AE_Menu($pages);

        /**
         * add sub menu page
         */
        foreach ($pages as $key => $page) {
            new AE_Submenu($page, $pages);
        }
    }
}

