	tinyMCE.init({
		include_jquery:false,
		tinymce_jquery:false,
		language:"fr",
		theme:{simple:{mode:"textareas",theme:"advanced",theme_advanced_buttons1:"mylistbox,mysplitbutton,bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,undo,redo,link,unlink",theme_advanced_toolbar_location:"top",theme_advanced_toolbar_align:"left",theme_advanced_statusbar_location:"bottom",paste_auto_cleanup_on_paste:true,plugins:"fullscreen",content_css:"\/web\/css\/tinymce_content.css",theme_advanced_buttons2:"",theme_advanced_buttons3:"",language:"fr"}},
		tinymce_buttons:[],
		external_plugins:[],
		jquery_script_url:"\/web\/bundles\/stfalcontinymce\/vendor\/tiny_mce\/tiny_mce.jquery.js",
		//mode: "textareas",
		//theme: "simple",
		//content_css : "{{ asset('css/tinymce_content.css') }}",
		// update validation status on change
		onchange_callback: function(editor) {
			tinyMCE.triggerSave();
			$("#" + editor.id).valid();
		},
		setup : function(ed) {
			ed.onChange.add(function(ed, l) {
				$('.tinymce').val(l.content);
			});
		}
	});
	$(function() {
		//~ var validator = $('form[name="{{ form.vars.id }}"]').submit(function() {
		var validator = JqueryValidateFormName.submit(function() {
			// update underlying textarea before submit validation
			tinyMCE.triggerSave();
		}).validate({
			ignore: "",
			rules: {
				title: "required",
				content: "required"
			},
			errorPlacement: function(error, element) {
				// Append error within linked label
				var msgPosition = $( element )
					.closest( "form" )
						.find( "label[for='" + element.attr( "id" ) + "']" );
				if( msgPosition.length > 0 ){
							msgPosition.append( error );
				}
				else if( !element.is("input:hidden") ) {
					if (element.is("textarea")) {
						error.insertAfter(element.next());
					} else {
						error.insertAfter(element)
					}
				}
			},
			errorElement: "span",
			/*errorPlacement: function(label, element) {
				// position error label after generated textarea
				if (element.is("textarea")) {
					label.insertAfter(element.next());
				} else {
					label.insertAfter(element)
				}
			}*/
		});
		validator.focusInvalid = function() {
			// put focus on tinymce on submit validation
			if (this.settings.focusInvalid) {
				try {
					var toFocus = $(this.findLastActive() || this.errorList.length && this.errorList[0].element || []);
					if (toFocus.is("textarea")) {
						tinyMCE.get(toFocus.attr("id")).focus();
					} else {
						toFocus.filter(":visible").focus();
					}
				} catch (e) {
					// ignore IE throwing errors when focusing hidden elements
				}
			}
		}
	})
