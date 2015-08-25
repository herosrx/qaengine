/**
 * editor_plugin_src.js
 *
 * Copyright 2013, EngineTheme Team
 * Released under LGPL License.
 *
 * License: http://www.enginethemes.com/
 * Contributing: http://www.enginethemes.com/
 */

(function($) {
    tinymce.create('tinymce.plugins.QAImageUploadPlugin', {
        init : function(ed, url) {

            ed.addCommand('qaOpenModal', function() {
                if(typeof QAEngine.Views.UploadImagesModal !== "undefined"){
                    var uploadIMGModal = new QAEngine.Views.UploadImagesModal({ el:$("#upload_images") });
                    uploadIMGModal.openModal();
                }
            });

            ed.addButton('qaimage', {
                title : qa_front.texts.upload_images,
                image : url + '/img/upload-image.gif',
                cmd : 'qaOpenModal'
            });
        },
        getInfo : function() {
            return {
                longname : 'QA Images Upload',
                author : 'thaint',
                version : '1.0'
            };
        }
    });

    tinymce.PluginManager.add('qaimage', tinymce.plugins.QAImageUploadPlugin);
})(jQuery);
