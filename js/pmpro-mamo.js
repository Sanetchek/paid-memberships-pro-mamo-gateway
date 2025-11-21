var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
var eventer = window[eventMethod];
var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
var creditCardCollected = false;
var pmproMamoAjaxMode = !!(window.pmproMamoVars && pmproMamoVars.data && pmproMamoVars.data.ajax === true);

// Listen for a message from the iframe (only in Popup/AJAX mode).
eventer(messageEvent, function (e) {
    if (!pmproMamoAjaxMode) { return; }
    var iframeEl = document.getElementById('wc_mamo_iframe');
    if (iframeEl) {
        iframeEl.style.height = e.data + 'px';
    }
}, false);

// Monitor iframe URL changes to detect successful payment redirect
if (pmproMamoAjaxMode) {
	var redirectDetected = false;
	var monitorInterval = setInterval(function() {
		if (redirectDetected) {
			clearInterval(monitorInterval);
			return;
		}

		var iframe = document.getElementById('wc_mamo_iframe');
		if (iframe) {
			try {
				var iframeUrl = iframe.contentWindow.location.href;

				// Ignore login redirects - these happen when confirmation page requires auth
				if (iframeUrl.indexOf('/login/') > -1 || iframeUrl.indexOf('wp-login.php') > -1) {
					redirectDetected = true;
					clearInterval(monitorInterval);
					console.log('MAMO: Payment processed, extracting confirmation URL...');

					var confirmationUrl = null;
					var match = iframeUrl.match(/redirect_to=([^&]+)/);
					if (match && match[1]) {
						confirmationUrl = decodeURIComponent(match[1]);
						console.log('MAMO: Found confirmation URL:', confirmationUrl);
					}

					// Replace iframe content with success message
					var successHtml = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>'
						+ 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; '
						+ 'margin: 0; padding: 40px; text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); '
						+ 'color: white; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; }'
						+ '.checkmark { width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); '
						+ 'display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; font-size: 48px; }'
						+ 'h1 { margin: 0 0 15px; font-size: 28px; font-weight: 600; }'
						+ 'p { margin: 0; font-size: 16px; opacity: 0.9; }'
						+ '.spinner { border: 3px solid rgba(255,255,255,0.3); border-top: 3px solid white; '
						+ 'border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 25px auto 0; }'
						+ '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }'
						+ '</style></head><body>'
						+ '<div class="checkmark">✓</div>'
						+ '<h1>Payment Successful!</h1>'
						+ '<p>Please wait, redirecting to confirmation page...</p>'
						+ '<div class="spinner"></div>'
						+ '</body></html>';

					iframe.contentWindow.document.open();
					iframe.contentWindow.document.write(successHtml);
					iframe.contentWindow.document.close();

					// Redirect main window after showing the message
					setTimeout(function() {
						if (confirmationUrl) {
							console.log('MAMO: Redirecting to confirmation page...');
							window.top.location.href = confirmationUrl;
						} else {
							console.log('MAMO: Redirecting to default confirmation...');
							window.top.location.href = window.location.origin + '/confirmation/';
						}
					}, 1500);
					return;
				}

				// Check if iframe redirected to confirmation page (MAMO redirects with status=captured)
				if (iframeUrl.indexOf('/confirmation/') > -1 ||
				    iframeUrl.indexOf('status=captured') > -1 ||
				    iframeUrl.indexOf('status=success') > -1) {
					redirectDetected = true;
					clearInterval(monitorInterval);
					console.log('MAMO: Payment successful, redirecting to confirmation...');
					jQuery('#mamo_payment_popup').modal('hide');
					window.top.location.href = iframeUrl;
				}
				// Check for error redirect
				else if (iframeUrl.indexOf('/checkout/') > -1 ||
				         iframeUrl.indexOf('status=failed') > -1) {
					redirectDetected = true;
					clearInterval(monitorInterval);
					console.log('MAMO: Payment error, redirecting to checkout...');
					jQuery('#mamo_payment_popup').modal('hide');
					window.top.location.href = iframeUrl;
				}
			} catch(e) {
				// Cross-origin - ignore (this is expected for external MAMO pages)
			}
		}
	}, 500);
}

jQuery(document).ready(function ($) {
	$('#mamo_payment_popup').on('show.bs.modal', function () {
		var $msg = $('#pmpro_processing_message');
		if ($msg.length) { $msg.css('visibility', 'hidden'); }
	});

	$('#mamo_payment_popup').on('hidden.bs.modal', function () {
		$('.pmpro_btn-submit-checkout,.pmpro_btn-submit').removeAttr('disabled');
		var $msg = $('#pmpro_processing_message');
		if ($msg.length) { $msg.css('visibility', 'visible'); }
	});

	var readyToprocess = $('#readyToProcessByMamo').val();
	var orderCode = $('#orderCode').val();
	if (readyToprocess && orderCode) {
		$('#pmpro_message').text('Processing......').removeClass('pmpro_error').removeClass('pmpro_alert').addClass('pmpro_success');
		$('.pmpro_btn-submit-checkout,.pmpro_btn-submit').attr('disabled', 'disabled');
		if (redirectAddress) {
			$('#wc_mamo_iframe').attr('src', redirectAddress)
			$('#mamo_payment_popup').modal('show');
		} else {
			$('#pmpro_message').text("ERROR").addClass('pmpro_error').removeClass('pmpro_alert').removeClass('pmpro_success').show();
			$('.pmpro_btn-submit-checkout,.pmpro_btn-submit').removeAttr('disabled');
		}
	}

    $('.pmpro_form').submit(function (event) {
        if (pmproMamoAjaxMode && creditCardCollected == false) {
            processMamo();
            event.preventDefault();
        }
    });

	function processMamo() {
		var name = $('#bfirstname').val();
		if ($('#bfirstname').length && $('#blastname').length) {
			name = jQuery.trim($('#bfirstname').val() + ' ' + $('#blastname').val());
		}

    const formData = $(".pmpro_form").serialize();

		jQuery.ajax({
			url: pmproMamoVars.data.url,
			type: "post",
			data: {
				action: pmproMamoVars.data.action,
				nonce: pmproMamoVars.data.nonce,
				form_data: formData,
			},
            success: function (apiresponse) {
                if (!apiresponse || apiresponse.success !== true) {
                    var msg = (apiresponse && apiresponse.data && apiresponse.data.message) ? apiresponse.data.message : 'MAMO error';
                    $('#pmpro_message').text(msg)
                        .addClass('pmpro_error')
                        .removeClass('pmpro_alert pmpro_success')
                        .show();
                    $('.pmpro_btn-submit-checkout,.pmpro_btn-submit').removeAttr('disabled');
                    return;
                }
                var redirectUrl = apiresponse.data && apiresponse.data.redirectUrl ? apiresponse.data.redirectUrl : '';
                if (!redirectUrl) {
                    $('#pmpro_message').text('Missing redirect URL').addClass('pmpro_error').removeClass('pmpro_alert').removeClass('pmpro_success').show();
                    $('.pmpro_btn-submit-checkout,.pmpro_btn-submit').removeAttr('disabled');
                    return;
                }
                $('#wc_mamo_iframe').attr('src', redirectUrl);
                $('#mamo_payment_popup').modal('show');
            },
			error: function (request, status, error) {
				$('#pmpro_message').text(request.responseText).addClass('pmpro_error').removeClass('pmpro_alert').removeClass('pmpro_success').show();
				$('.pmpro_btn-submit-checkout,.pmpro_btn-submit').removeAttr('disabled');
			}
		})
	}
});

