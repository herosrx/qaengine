/**
 * editor_plugin_src.js
 *
 * Copyright 2013, EngineTheme Team
 * Released under LGPL License.
 *
 * License: http://www.enginethemes.com/
 * Contributing: http://www.enginethemes.com/
 */

(function() {
  tinymce.create('tinymce.plugins.FEIconCodePlugin', {
    init : function(ed, url) {

      // Register commands
      ed.addCommand('feInsertCode', function() {
        ed.focus();
        //if(ed.selection.getContent())
            ed.selection.setContent('[code]<br/>' + ed.selection.getContent() + '<br/>[/code]');
        // else
        //     ed.setContent('[code]<br/>[/code]');
      });

      // Register buttons
      ed.addButton('qacode', {
        title : qa_front.texts.insert_codes,
        //class: 'feimage-icon',
        icon: 'wp_code',
        //image : url + '/img/icon-code.png',
        cmd : 'feInsertCode'
      });
    },
    getInfo : function() {
      return {
        longname : 'FE Insert Code',
        author : 'thaint',
        version : '0.0.1'
      };
    }
  });

  // Register plugin
  tinymce.PluginManager.add('qacode', tinymce.plugins.FEIconCodePlugin);
})();
