window.QAEngine 		= window.QAEngine || {};
(function (QAEngine, $, Backbone) {
	QAEngine.Views  = QAEngine.Views || {};
	QAEngine.Models  = QAEngine.Models || {};
	QAEngine.Events  = QAEngine.Events || {};
	QAEngine.Events  = _.extend(QAEngine.Events, Backbone.Events);

	QAEngine.Models.User = Backbone.Model.extend({
		action: "et_user_sync",
		initialize : function () {
			if ( typeof this.get('id') == 'undefined' && typeof this.get('ID') != 'undefined' ){
				this.set('id', this.get('ID'));
			}
		},
		login: function(username, password, remember, options){

			this.set('content',{
				username: username,
				password: password,
				remember: remember,
			});

			this.fetch(options);
		},
		register: function(data, options){

			this.set('content', data);
			this.save('','',options);
		},
		forgot: function(username_or_email, options){
			this.set('content', {
				action: 'forgot',
				user_login: username_or_email
			});
			this.fetch(options);
		},
		resetpass: function(username, new_pass, user_key, options){
			this.set('content', {
				action		: 'reset',
				user_login	: username,
				new_pass	: new_pass,
				user_key	: user_key
			});
			this.fetch(options);
		}
	});

	QAEngine.Models.Post = Backbone.Model.extend({
		action: "et_post_sync",
		initialize : function () {
			if ( typeof this.get('id') == 'undefined' && typeof this.get('ID') != 'undefined' ){
				this.set('id', this.get('ID'));
			}
		}
	});

})(window.QAEngine, jQuery, Backbone);
/* ==== reload page ==== */
function redirectTimeout(url, time){
	setTimeout(function(){
		window.location.href = url;
	}, time);
}

jQuery.fn.serializeObject = function(){

	var self = this,
	    json = {},
	    push_counters = {},
	    patterns = {
	        "validate": /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
	        "key":      /[a-zA-Z0-9_]+|(?=\[\])/g,
	        "push":     /^$/,
	        "fixed":    /^\d+$/,
	        "named":    /^[a-zA-Z0-9_]+$/
	    };


	this.build = function(base, key, value){
	    base[key] = value;
	    return base;
	};

	this.push_counter = function(key){
	    if(push_counters[key] === undefined){
	        push_counters[key] = 0;
	    }
	    return push_counters[key]++;
	};

	jQuery.each(jQuery(this).serializeArray(), function(){

	    // skip invalid keys
	    if(!patterns.validate.test(this.name)){
	        return;
	    }

	    var k,
	        keys = this.name.match(patterns.key),
	        merge = this.value,
	        reverse_key = this.name;

	    while((k = keys.pop()) !== undefined){

	        // adjust reverse_key
	        reverse_key = reverse_key.replace(new RegExp("\\[" + k + "\\]$"), '');

	        // push
	        if(k.match(patterns.push)){
	            merge = self.build([], self.push_counter(reverse_key), merge);
	        }

	        // fixed
	        else if(k.match(patterns.fixed)){
	            merge = self.build([], k, merge);
	        }

	        // named
	        else if(k.match(patterns.named)){
	            merge = self.build({}, k, merge);
	        }
	    }

	    json = jQuery.extend(true, json, merge);
	});

	return json;
};