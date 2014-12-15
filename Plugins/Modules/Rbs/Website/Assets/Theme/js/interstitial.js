(function() {
	"use strict";
	var app = angular.module('RbsChangeApp');

	function rbsWebsiteInterstitial(AjaxAPI, $http, $compile, $timeout, $cookies) {
		return {
			restrict: 'A',
			templateUrl: '/rbsWebsiteInterstitial.tpl',
			scope: {
				modalId: '@',
				cacheKey: '@',
				contentUrl: '@'
			},

			link: function (scope) {
				scope.parameters = {};
				if (scope.cacheKey) {
					scope.parameters = AjaxAPI.getBlockParameters(scope.cacheKey);
				}

				// If there is no modalId or no contentUrl, do nothing.
				if (!scope.modalId || !scope.contentUrl) {
					return;
				}

				console.log('parameters', scope.parameters);
				var allowClosing = scope.parameters['allowClosing'];
				var frequency = scope.parameters['displayFrequency'];
				var modalNode = jQuery('#' + scope.modalId);
				var mainContentElement = jQuery('#' + scope.modalId + ' .modal-main-content');

				var closeDialog = function closeDialog() {
					modalNode.modal('hide');
				};

				var loadContentSuccess = function loadContentSuccess (resultData) {
					jQuery('#' + scope.modalId + ' .modal-loading').hide();
					mainContentElement.html(resultData);
					$compile(mainContentElement.contents())(scope);
					mainContentElement.show();

					var autoCloseDelay = parseInt(scope.parameters['autoCloseDelay']);
					if (autoCloseDelay > 1) {
						$timeout(closeDialog, autoCloseDelay * 1000);
					}
				};

				var loadContentError = function loadContentEror (data, status, headers) {
					console.log('error', data, status, headers);
				};

				var showDialog = function showDialog() {
					mainContentElement.html('<div class="text-center"><img alt="" src="data:image/gif;base64,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" /></div>');
					$http.get(scope.contentUrl).success(loadContentSuccess).error(loadContentError);
					modalNode.modal({keyboard: allowClosing, backdrop: allowClosing ? true : 'static'});
				};

				if (frequency === 'always') {
					showDialog();
				}
				else {
					var cookieName = 'rbsWebsiteInterstitial-' + scope.parameters['displayedPage'];
					if ($cookies[cookieName]) {
						return;
					}

					var getCookieExpireDate = function getCookieExpireDate(cookieTimeout) {
						var date = new Date();
						date.setTime(date.getTime() + cookieTimeout);
						return "; expires=" + date.toGMTString();
					};

					switch (frequency) {
						case 'session':
							$cookies[cookieName] = true;
							showDialog();
							break;

						case 'reprieve':
							var expire = getCookieExpireDate(scope.parameters['displayReprieve'] * 24 * 3600 * 1000);
							document.cookie = cookieName + '=true;' + expire + ' ; path=/';
							showDialog();
							break;

						case 'once':
							expire = getCookieExpireDate(10 * 365 * 24 * 3600 * 1000); // 10 years...
							document.cookie = cookieName + '=true;' + expire + ' ; path=/';
							showDialog();
							break;

						default:
							break;
					}
				}
			}
		}
	}

	rbsWebsiteInterstitial.$inject = ['RbsChange.AjaxAPI', '$http', '$compile', '$timeout', '$cookies'];
	app.directive('rbsWebsiteInterstitial', rbsWebsiteInterstitial);
})();